<?php
/*
Plugin Name: WXR PDF CLI
Version: 0.0.1
Description: Convert a WXR to a PDF document
Author: Per Soderlind
Author URI: https://soderlind.no
Plugin URI: https://github.com/soderlind/wxr-pdf-cli
License: GPL
Text Domain: wxr2pdf
Domain Path: /languages
*/

define( 'WXR2PDF_PATH',   __DIR__);
//define( 'WXR2PDF_URL',   plugin_dir_url( __FILE__ ));
define( 'WXR2PDF_CACHE', WXR2PDF_PATH . '/var/pdf');
define( 'WXR2PDF_VERSION', '0.0.1' );

function wxr_pdf_cli_init() {
	if ( defined('WP_CLI') && WP_CLI ) {
		/**
		 * Dump a site to PDF using the WXR file
		 */
		class WXR_PDF_Command extends WP_CLI_Command {

		    /**
		     * Convert a WordPress Export to PDF
		     *
		     * ## OPTIONS
		     *
		     * <file>
		     * : Path to WXR file for parsing
		     *
		     * [--posttype=<posttype>]
		     * : Select post type. If not set, "post" is used. Separate post types using colon, eg --posttype=post:page
		     *
		     * [--language=<country_CODE>]
		     * : Loads languages/wxr2pdf_country_CODE.mo
		     *
		     * [--noimg]
		     * : Dont include images
		     *
		     * ## EXAMPLES
		     *
		     *     wp wxr-pdf convert file.wxr
		     *     wp wxr-pdf convert file.wxr --language=nb_NO
		     *     wp wxr-pdf convert file.wxr --noimg
		     *     wp wxr-pdf convert file.wxr --posttype=page
		     *     wp wxr-pdf convert file.wxr --nocomments
		     *
		     * @synopsis <file> [--language=<country_CODE>] [--noimg] [--posttype=<posttype>]
		     */
		    function convert( $args, $assoc_args ) {
		        if ( $args ) {
		          list( $wxr_file ) = $args;


		            if (! file_exists($wxr_file)) {
		                WP_CLI::line();
		                WP_CLI::error(
		                    WP_CLI::colorize( sprintf('File  "%%R%s%%n" not found.', $wxr_file) )
		                );
		            }
		          	wp_async_task_add( 'wxr2pdf_worker', array( 'file' => $wxr_file, 'assoc_args' => $assoc_args ) );
		        }

		        WP_CLI::success( 'Done!' );
		    }
		}
		WP_CLI::add_command( 'wxr-pdf', 'WXR_PDF_Command' );
	}
}

if ( defined( 'WP_CLI' ) ) {
	add_action( 'plugins_loaded', 'wxr_pdf_cli_init' );
}

/**
* WXR2PDF Worker
*/
class WXR_PDF_Worker {

   static function wxr2pdf_callback($args) {

        $wxr_file = $args['file'];
        $assoc_args = $args['assoc_args'];
        $post_type  = (isset($assoc_args['posttype']) && '' != $assoc_args['posttype'] ) ? $assoc_args['posttype'] : 'post';
        $post_types = array_flip(explode(':', $post_type));


        if (isset($assoc_args['language'])) {
            $mofile = WXR2PDF_PATH . '/languages/' . $assoc_args['language'] . '.mo';
            load_textdomain( 'wxr2pdf', $mofile );
        }
        //add twig template engine
        require_once WXR2PDF_PATH . '/lib/Twig/Autoloader.php';
        Twig_Autoloader::register();

        $loader = new Twig_Loader_Filesystem(dirname( __FILE__ ) . '/templates/twig');
        $twig = new Twig_Environment($loader, array(
            //'cache' => dirname( __FILE__ ) . '/var/twig_cache',
        ));
        // include WXR file parsers
        require dirname( __FILE__ ) . '/parsers.php';

        //
        require_once WXR2PDF_PATH . '/inc/class-wxr-pdf-create.php';

        $create = WXR2PDF_Create::get_instance();

        $parser = new WXR_Parser();
            $data = $parser->parse($wxr_file);

            $posts = $sortArray = array();
            foreach ($data['posts'] as $key => $post) {
                if (isset($post['post_type']) && isset($post_types[$post['post_type']]) && 'publish' == $post['status']) {
                    unset($post['postmeta']);
                    $post['author_email']        = $data['authors'][$post['post_author']]['author_email'];
                    $post['author_display_name'] = $data['authors'][$post['post_author']]['author_display_name'];
                    $post['author_first_name']   = $data['authors'][$post['post_author']]['author_first_name'];
                    $post['author_last_name']    = $data['authors'][$post['post_author']]['author_last_name'];

                    $category = $tag = array();
                    if ( isset($post['terms']) && is_array($post['terms']) )  {
		                    foreach ($post['terms'] as $term) {
		                        switch ($term['domain']) {
		                            case 'category':
		                                $category[] = $term['name'];
		                                break;
		                            case 'post_tag':
		                                $tag[] = $term['name'];
		                                break;
		                        }
		                    }
	                    unset($post['terms']);
	                    $post['category'] = implode(' ,',$category);
	                    $post['tag']      = implode(' ,',$tag);
	                }
                    //sortarray - from http://php.net/manual/en/function.ksort.php#98465
                    foreach($post as $key=>$value){
                        if(!isset($sortArray[$key])){
                            $sortArray[$key] = array();
                        }
                        $sortArray[$key][] = $value;
                    }

                    //add to posts array
                    $posts[]                     = $post;
                }
            }

            // from from http://php.net/manual/en/function.ksort.php#98465
            $orderby = "post_date"; //change this to whatever key you want from the array
            array_multisort($sortArray[$orderby],SORT_DESC,$posts);

            //WP_CLI::print_value($posts);
            $create->init($posts[0]);

            $html = $twig->render('document.twig', array(
                'posts' => $posts,
                'l10n' => array(
                    'by'          => __('By','wxr2pdf'),
                    'comments'    => __('Comments','wxr2pdf'),
                    'name'        => __('Name','wxr2pdf'),
                    'email'       => __('Email','wxr2pdf'),
                    'url'         => __('URL','wxr2pdf'),
                    'date'        => __('Date','wxr2pdf'),
                    'date_format' => __('m/d/Y','wxr2pdf')
                ),
                'doc' => array(
                    'title'       => basename($wxr_file, '.xml'),
                    'madeby'      => __('This PDF is created using WXR2PDF.com', 'wxr2pdf'),
                    'titleprefix' => __('File:', 'wxr2pdf')
                )
            ));

            $filename =  dirname($wxr_file) . '/' . basename($wxr_file, '.xml') . ".pdf";

            if (isset($assoc_args['noimg'])) {
                //remove images
                $dom = new DOMDocument();
                @$dom->loadHTML( mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8') );
                $images = $dom->getElementsByTagName('img');

                // from http://us2.php.net/manual/en/domnode.removechild.php#90292
                $images_to_remove = array();
                foreach ( $images as $img ) {
                  $images_to_remove[] = $img;
                }
                foreach( $images_to_remove as $img ){
                  $img->parentNode->removeChild($img);
                }
                $html = $dom->saveHTML();
            }
            $create->pdf($filename, $html);
    }

}

add_action( 'wxr2pdf_worker', array('WXR_PDF_Worker', 'wxr2pdf_callback') );