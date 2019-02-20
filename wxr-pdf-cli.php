<?php
/*
Plugin Name: WXR PDF CLI
Version: 0.0.2
Description: Convert a WXR to a PDF document
Author: Per Soderlind
Author URI: https://soderlind.no
Plugin URI: https://github.com/soderlind/wxr-pdf-cli
License: GPL
Text Domain: wxr2pdf
Domain Path: /languages
*/

define( 'WXR2PDF_PATH', __DIR__ );
//define( 'WXR2PDF_URL',   plugin_dir_url( __FILE__ ));
define( 'WXR2PDF_CACHE', WXR2PDF_PATH . '/var/pdf' );
define( 'WXR2PDF_VERSION', '0.0.2' );
define( 'WXR2PDF_DEBUG', true );

function wxr_pdf_cli_init() {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
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
			 *   wp wxr-pdf convert file.wxr
			 *   wp wxr-pdf convert file.wxr --language=nb_NO
			 *   wp wxr-pdf convert file.wxr --noimg
			 *   wp wxr-pdf convert file.wxr --posttype=page
			 *   wp wxr-pdf convert file.wxr --nocomments
			 *
			 * @synopsis <file> [--language=<country_CODE>] [--noimg] [--posttype=<posttype>]
			 */
			function convert( $args, $assoc_args ) {
				if ( $args ) {
					list( $wxr_file ) = $args;

					if ( ! file_exists( $wxr_file ) ) {
						WP_CLI::line();
						WP_CLI::error(
							WP_CLI::colorize( sprintf( 'File  "%%R%s%%n" not found.', $wxr_file ) )
						);
					}
					//wp_async_task_add( 'wxr2pdf_worker', array( 'file' => $wxr_file, 'assoc_args' => $assoc_args ) );
					WXR_PDF_Worker::wxr2pdf_callback(
						array(
							'file'       => $wxr_file,
							'assoc_args' => $assoc_args,
						)
					);
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

	static $urls = array();

	static function wxr2pdf_callback( $args ) {

		$wxr_file      = $args['file'];
		$assoc_args    = $args['assoc_args'];
		$str_post_type = ( isset( $assoc_args['posttype'] ) && '' != $assoc_args['posttype'] ) ? $assoc_args['posttype'] : 'post';
		//		$post_types = array_flip(explode(':', $str_post_type));
		$post_types = explode( ':', $str_post_type );

		if ( isset( $assoc_args['language'] ) ) {
			$mofile = WXR2PDF_PATH . '/languages/' . $assoc_args['language'] . '.mo';
			load_textdomain( 'wxr2pdf', $mofile );
		}
		//add twig template engine
		require_once WXR2PDF_PATH . '/lib/Twig/Autoloader.php';
		Twig_Autoloader::register();

		$loader = new Twig_Loader_Filesystem( dirname( __FILE__ ) . '/templates/twig' );
		$twig   = new Twig_Environment(
			$loader,
			array(
				//'cache' => dirname( __FILE__ ) . '/var/twig_cache',
			)
		);
		// include WXR file parsers
		require_once dirname( __FILE__ ) . '/inc/class-wxr-parser.php';

		//
		require_once WXR2PDF_PATH . '/inc/class-wxr-pdf-create.php';

		$pdf = WXR2PDF_Create::get_instance();

		$parser = new WXR2PDF_WXR_Parser();

		$attachments = $parser->parse( $wxr_file, 'attachment' );
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			// WP_CLI::print_value( $attachments['posts'] );
		}

		foreach ( $post_types as $post_type ) {
			$data = array();
			$data = $parser->parse( $wxr_file, $post_type );
			// if (! $data) {
			//  WP_CLI::line( WP_CLI::colorize('%RERROR!%n'));
			//  WP_CLI::log( $parser->error_message );
			//  exit();
			// }
			//if ( defined( 'WP_CLI' ) && WP_CLI ) WP_CLI::print_value( $data['posts'] );
			$posts = $sort_array = array();
			foreach ( $data['posts'] as $key => $post ) {
				if ( isset( $post['post_type'] ) && /*isset($post_types[$post['post_type']]) && */'publish' == $post['status'] ) {
					//unset( $post['postmeta'] );

					/*
					 * test if variables are set
					 */
					$post['author_email']        = $data['authors'][ $post['post_author'] ]['author_email'];
					$post['author_display_name'] = $data['authors'][ $post['post_author'] ]['author_display_name'];
					$post['author_first_name']   = $data['authors'][ $post['post_author'] ]['author_first_name'];
					$post['author_last_name']    = $data['authors'][ $post['post_author'] ]['author_last_name'];

					$category = $tag = array();
					if ( isset( $post['terms'] ) && is_array( $post['terms'] ) ) {
						foreach ( $post['terms'] as $term ) {
							switch ( $term['domain'] ) {
								case 'category':
									$category[] = $term['name'];
									break;
								case 'post_tag':
									$tag[] = $term['name'];
									break;
							}
						}
						unset( $post['terms'] );
						$post['category'] = implode( ' ,', $category );
						$post['tag']      = implode( ' ,', $tag );
					}

					// foreach ($post['postmeta'] as $element) {
					// 	$thumb_id = '0';
					// 	if (isset($element['key']) && '_thumbnail_id' == $element['key']) {
					// 		$thumb_id = $element['value'];
					// 		break;
					// 	}
					// }
					$attachment_url = '';
					if ( false != ( $featured = self::_find_post( $post['postmeta'], array( 'key' => '_thumbnail_id' ) ) ) ) {
						$thumb_id       = $featured['value'];
						$attachment     = self::_find_post( $attachments['posts'], array( 'post_id' => $thumb_id ) );
						$attachment_url = ! empty( $attachment['attachment_url'] ) ? $attachment['attachment_url'] : $attachment['guid'];
					}
					$post['featured_image'] = $attachment_url;

					//sortarray - from http://php.net/manual/en/function.ksort.php#98465
					foreach ( $post as $key => $value ) {
						if ( ! isset( $sort_array[ $key ] ) ) {
							$sort_array[ $key ] = array();
						}
						$sort_array[ $key ][] = $value;
					}

					//add to posts array
					$posts[] = $post;
				}
			}

			// from from http://php.net/manual/en/function.ksort.php#98465
			$orderby = 'post_date'; //change this to whatever key you want from the array
			if ( ! $posts ) {
				if ( defined( 'WP_CLI' ) && WP_CLI ) {
					WP_CLI::line( WP_CLI::colorize( 'Post type "%C' . $post_type . '%n" not found in file.' ) );
				}
				continue;
			}
			array_multisort( $sort_array[ $orderby ], SORT_DESC, $posts );

			$all_post_slugs = array();
			$post_ids       = $sort_array['post_id'];
			foreach ( $sort_array['post_name'] as $key => $post_name ) {
				$all_post_slugs[ $post_ids[ $key ] ] = $post_name;
			}

			//$all_post_slugs = array_merge($sort_array['post_name'], $sort_array['post_name']);

			self::clidebug( $all_post_slugs );
			//if ( defined('WP_CLI') && WP_CLI ) WP_CLI::print_value($posts);
			//printf("<pre>%s</pre>",print_r($sort_array['post_name'],true)); exit();

			self::clidebug( array_filter( $data, 'is_scalar' ) ); // http://stackoverflow.com/a/13088138/1434155
			// if ( defined('WP_CLI') && WP_CLI ) WP_CLI::print_value($posts);
			// exit();

			$pdf->init( $posts[0], $data['site_title'], $data['site_decription'] );

			$html = $twig->render(
				'page.twig',
				array(
					'posts'    => $posts,
					'site'     => array_filter( $data, 'is_scalar' ), // http://stackoverflow.com/a/13088138/1434155
					'posttype' => $post_type,
					'l10n'     => array(
						'pretitle'    => __( 'Post Type', 'wxr2pdf' ),
						'by'          => __( 'By', 'wxr2pdf' ),
						'comments'    => __( 'Comments', 'wxr2pdf' ),
						'name'        => __( 'Name', 'wxr2pdf' ),
						'email'       => __( 'Email', 'wxr2pdf' ),
						'url'         => __( 'URL', 'wxr2pdf' ),
						'date'        => __( 'Date', 'wxr2pdf' ),
						'date_format' => __( 'm/d/Y', 'wxr2pdf' ),
					),
					'doc'      => array(
						'title'       => basename( $wxr_file, '.xml' ),
						//'madeby'	  => __('This PDF is created using WXR2PDF.com', 'wxr2pdf'),
						'madeby'      => 'x',
						'titleprefix' => __( 'File:', 'wxr2pdf' ),
					),
				)
			);
			$html = apply_filters( 'the_content', $html );

			$filename = dirname( $wxr_file ) . '/' . basename( $wxr_file, '.xml' ) . '-' . $post_type . '.pdf';

			$download_dir = dirname( $wxr_file ) . '/' . basename( $wxr_file, '.xml' );

			if ( isset( $assoc_args['noimg'] ) ) {
				$html = self::_remove_img_tag( $html );
			} else {
				$html = self::_remove_img_link( $html );
			}

			$html = self::_get_linked_elements( $html, $download_dir, $data['base_url'], $all_post_slugs );

			// return body->innerhtml
			$html = preg_replace( '~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $html );

			$html = $twig->render(
				'document.twig',
				array(
					'content'  => $html,
					'urls'     => static::$urls,
					'site'     => array_filter( $data, 'is_scalar' ), // http://stackoverflow.com/a/13088138/1434155
					'posttype' => $post_type,
					'l10n'     => array(
						'pretitle'       => __( 'Post Type', 'wxr2pdf' ),
						'by'             => __( 'By', 'wxr2pdf' ),
						'comments'       => __( 'Comments', 'wxr2pdf' ),
						'name'           => __( 'Name', 'wxr2pdf' ),
						'email'          => __( 'Email', 'wxr2pdf' ),
						'url'            => __( 'URL', 'wxr2pdf' ),
						'date'           => __( 'Date', 'wxr2pdf' ),
						'date_format'    => __( 'm/d/Y', 'wxr2pdf' ),
						//	 'external_files' => __('External files','wxr2pdf')
						'external_files' => __( 'Eksterne filer', 'wxr2pdf' ),
					),
					'doc'      => array(
						'title'       => basename( $wxr_file, '.xml' ),
						//				 'madeby'	  => __('This PDF is created using WXR2PDF.com', 'wxr2pdf'),
						'madeby'      => 'n',
						'titleprefix' => __( 'File:', 'wxr2pdf' ),
					),
				)
			);

			//print $html;

			$pdf->create( $html );

			// if (is_array(static::$urls) && count(static::$urls)) {
			//  $pdf->attach(static::$urls);
			// }

			$pdf->save( $filename );
		}
	}


	static function _remove_img_tag( $html ) {
		//remove images
		$dom = new DOMDocument();
		@$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) /*,LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD*/ );
		$images = $dom->getElementsByTagName( 'img' );

		// from http://us2.php.net/manual/en/domnode.removechild.php#90292
		$images_to_remove = array();
		foreach ( $images as $img ) {
			$images_to_remove[] = $img;
		}
		foreach ( $images_to_remove as $img ) {
			if ( 'a' == $img->parentNode->nodeName ) {
				$img->parentNode->parentNode->removeChild( $img->parentNode );
			} else {
				$img->parentNode->removeChild( $img );
			}
		}
		return $dom->saveHTML();
	}


	static function _remove_img_link( $html ) {
		//remove images
		$dom = new DOMDocument();
		@$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) /*,LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD*/ );
		$images = $dom->getElementsByTagName( 'img' );
		foreach ( $images as $img ) {
			if ( 'a' == $img->parentNode->nodeName ) {
				$img->parentNode->parentNode->replaceChild( $img, $img->parentNode );
			}
			$img->removeAttribute( 'title' );
		}
		return $dom->saveHTML();
	}



	static function _get_linked_elements( $html, $download_dir, $base_url, $all_post_slugs = array() ) {

		// if ( defined( 'WP_CLI' ) && WP_CLI && WXR2PDF_DEBUG ) {
		// 	WP_CLI::print_value( array_filter( func_get_args(), 'is_scalar' ) ); // http://stackoverflow.com/a/13088138/1434155
		// }

		$dom = new DOMDocument();
		@$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) /*, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD*/ );
		$dom->formatOutput       = true;
		$dom->preserveWhitespace = false;

		$tags = $dom->getElementsByTagName( 'a' );
		$i    = 1;
		for ( $k = $tags->length - 1; $k >= 0; $k-- ) {
			$tag  = $tags->item( $k );
			$url  = $tag->getAttribute( 'href' );
			$link = ( in_array( substr( $url, strrpos( $url, '.' ) + 1 ), array( 'pdf' ) ) ) ? $url : '';

			if ( false !== strpos( str_replace( [ 'http://','https://' ],'',$url), str_replace( [ 'http://','https://' ],'',$base_url) ) && '' !== $link ) {
				if ( ! file_exists( $download_dir ) ) {
					if ( true !== wp_mkdir_p( $download_dir ) ) {
						continue;
					}
				}
				if ( ! file_exists( $download_dir . '/' . basename( $url ) ) ) {
					self::copyfile_chunked( $url, $download_dir . '/' . basename( $url ) );
				}
				$tag->setAttribute( 'href', basename( $download_dir ) . '/' . basename( $url ) );
				self::$urls[] = array(
					'url' => basename( $download_dir ) . '/' . basename( $url ),
					'txt' => $tag->textContent,
				);
				//	//create reference link
				//	$e = $dom->createElement('a', ' [' . $i . ']');
				//	$a = $dom->appendChild($e);
				//	$a->setAttribute('href', '#wxr2pdf-urls');
				//	$sup = $dom->createElement('span');
				//	$sup->appendChild($a);
				//	$tag->parentNode->insertBefore($sup, $tag->nextSibling);

				// append font awsome
				$icon_pdf = $dom->createDocumentFragment(); // create fragment
				$icon_pdf->appendXML( '<span style="font-family: fontawesome; vertical-align: bottom;"> &#xf1c1;</span>' ); // insert arbitary html into the fragment
				$tag->appendChild( $icon_pdf );
			} elseif ( false !== strpos( $url, $base_url ) && 0 != ( $slug_id = self::_in_slugs( $url, $all_post_slugs ) ) ) { // internal ref
				echo '#', $slug_id, "\n";
				$tag->removeAttribute( 'href' );
				$tag->setAttribute( 'href', '#' . $slug_id );
			} elseif ( false !== strpos( $url, $base_url ) ) { // points to another post type i.e. outside current document so remove link
				$lnkText    = $tag->textContent;
				$newTxtNode = $dom->createTextNode( $lnkText );
				$tag->parentNode->replaceChild( $newTxtNode, $tag );
			} elseif ( '' == $tag->getAttribute( 'name' ) ) {
				// $lnkText = $tag->textContent;
				// $t = sprintf("%s (%s)",$lnkText, $url);
				// $newTxtNode = $dom->createTextNode( $t );
				// $tag->parentNode->replaceChild( $newTxtNode, $tag );
				$icon_external_link = $dom->createDocumentFragment(); // create fragment
				//              $icon_external_link->appendXML('<span style="font-family: fontawesome; vertical-align: bottom;"> &#xf08e;</span>'); // insert arbitary html into the fragment
				$icon_external_link->appendXML( '<span style="vertical-align: bottom;"> (' . $url . ')</span>' ); // insert arbitary html into the fragment
				if ( ! empty( $icon_external_link ) ) {
					$tag->appendChild( $icon_external_link );
				}
			}
		}
		return $dom->saveHTML();
	}

	static function _in_slugs( $url, $slugs ) {
		$slug_id = 0;
		$query   = parse_url( $url, PHP_URL_QUERY );
		if ( '' != $query ) {
			$args = wp_parse_args(
				$query,
				array()
			);
			if ( isset( $args['p'] ) && array_key_exists( $args['p'], $slugs ) ) {
				$slug_id = $args['p'];
			}
		} elseif ( false !== in_array( basename( $url ), array_flip( $slugs ) ) ) {
			$slug_id = basename( $url );
		} elseif ( false !== in_array( basename( $url ), $slugs ) ) {
			$tmp_arr = array_flip( $slugs );
			$slug_id = $tmp_arr[ basename( $url ) ];
		}
		return $slug_id;
	}


	/**
	 * Copy remote file over HTTP one small chunk at a time. From: http://stackoverflow.com/a/4000569/1434155
	 *
	 * @param unknown $infile  The full URL to the remote file
	 * @param unknown $outfile The path where to save the file
	 */
	static function copyfile_chunked( $infile, $outfile ) {
		$chunksize = 10 * ( 1024 * 1024 ); // 10 Megs

		/**
		 * parse_url breaks a part a URL into it's parts, i.e. host, path,
		 * query string, etc.
		 */
		$parts    = parse_url( $infile );
		$i_handle = fsockopen( $parts['host'], 80, $errstr, $errcode, 5 );
		$o_handle = fopen( $outfile, 'wb' );

		if ( $i_handle == false || $o_handle == false ) {
			return false;
		}

		if ( ! empty( $parts['query'] ) ) {
			$parts['path'] .= '?' . $parts['query'];
		}

		/**
		 * Send the request to the server for the file
		 */
		$request  = "GET {$parts['path']} HTTP/1.1\r\n";
		$request .= "Host: {$parts['host']}\r\n";
		$request .= "User-Agent: Mozilla/5.0\r\n";
		$request .= "Keep-Alive: 115\r\n";
		$request .= "Connection: keep-alive\r\n\r\n";
		fwrite( $i_handle, $request );

		/**
		 * Now read the headers from the remote server. We'll need
		 * to get the content length.
		 */
		$headers = array();
		while ( ! feof( $i_handle ) ) {
			$line = fgets( $i_handle );
			if ( $line == "\r\n" ) {
				break;
			}
			$headers[] = $line;
		}

		/**
		 * Look for the Content-Length header, and get the size
		 * of the remote file.
		 */
		$length = 0;
		foreach ( $headers as $header ) {
			if ( stripos( $header, 'Content-Length:' ) === 0 ) {
				$length = (int) str_replace( 'Content-Length: ', '', $header );
				break;
			}
		}

		/**
		 * Start reading in the remote file, and writing it to the
		 * local file one chunk at a time.
		 */
		$cnt = 0;
		while ( ! feof( $i_handle ) ) {
			$buf   = '';
			$buf   = fread( $i_handle, $chunksize );
			$bytes = fwrite( $o_handle, $buf );
			if ( $bytes == false ) {
				return false;
			}
			$cnt += $bytes;

			/**
			 * We're done reading when we've reached the conent length
			 */
			if ( $cnt >= $length ) {
				break;
			}
		}

		fclose( $i_handle );
		fclose( $o_handle );
		return $cnt;
	}

	// from http://stackoverflow.com/a/19995603/1434155
	static function _find_post( $array, $matching ) {
		foreach ( $array as $item ) {
			$is_match = true;
			foreach ( $matching as $key => $value ) {

				if ( is_object( $item ) ) {
					if ( ! isset( $item->$key ) ) {
						$is_match = false;
						break;
					}
				} else {
					if ( ! isset( $item[ $key ] ) ) {
						$is_match = false;
						break;
					}
				}

				if ( is_object( $item ) ) {
					if ( $item->$key != $value ) {
						$is_match = false;
						break;
					}
				} else {
					if ( $item[ $key ] != $value ) {
						$is_match = false;
						break;
					}
				}
			}

			if ( $is_match ) {
				return $item;
			}
		}

		return false;
	}

	static function _array_find_element_by_key( $key, $form ) {
		if ( array_key_exists( $key, $form ) ) {
			$ret = $form[ $key ];
			return $ret;
		}
		foreach ( $form as $k => $v ) {
			if ( is_array( $v ) ) {
				$ret = self::_array_find_element_by_key( $key, $form[ $k ] );
				if ( $ret ) {
					return $ret;
				}
			}
		}
		return false;
	}

	private static function clidebug( $val ) {
		if ( defined( 'WP_CLI' ) && WP_CLI && WXR2PDF_DEBUG ) {
			WP_CLI::print_value( $val );
		}
	}


}

add_action( 'wxr2pdf_worker', array( 'WXR_PDF_Worker', 'wxr2pdf_callback' ) );
