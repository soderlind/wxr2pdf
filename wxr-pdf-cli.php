<?php
/*
Plugin Name: WXR PDF CLI
Version: 0.0.3
Description: Convert a WXR to a PDF document
Author: Per Soderlind
Author URI: https://soderlind.no
Plugin URI: https://github.com/soderlind/wxr2pdf
License: GPL
Text Domain: wxr2pdf
Domain Path: /languages
*/

namespace Soderlind\WXR2PDF;
! defined( 'WP_CLI' ) and exit;



define( 'WXR2PDF_PATH', __DIR__ );
//define( 'WXR2PDF_URL',   plugin_dir_url( __FILE__ ));
define( 'WXR2PDF_CACHE', WXR2PDF_PATH . '/var/pdf' );
define( 'WXR2PDF_VERSION', '0.0.2' );
define( 'WXR2PDF_DEBUG', false );

require_once WXR2PDF_PATH . '/vendor/autoload.php';

/**
 * Dump a site to PDF using the WXR file
 */
class Command {

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
	 *   wp wxr2pdf convert file.wxr
	 *   wp wxr2pdf convert file.wxr --language=nb_NO
	 *   wp wxr2pdf convert file.wxr --noimg
	 *   wp wxr2pdf convert file.wxr --posttype=page
	 *   wp wxr2pdf convert file.wxr --nocomments
	 *
	 * @synopsis <file> [--language=<country_CODE>] [--noimg] [--posttype=<posttype>]
	 */
	function convert( $args, $assoc_args ) {
		if ( $args ) {
			list( $wxr_file ) = $args;

			if ( ! file_exists( $wxr_file ) ) {
				\WP_CLI::line();
				\WP_CLI::error(
					\WP_CLI::colorize( sprintf( 'File  "%%R%s%%n" not found.', $wxr_file ) )
				);
			}
			//wp_async_task_add( 'wxr2pdf_worker', array( 'file' => $wxr_file, 'assoc_args' => $assoc_args ) );
			Worker::callback(
				[
					'file'       => $wxr_file,
					'assoc_args' => $assoc_args,
				]
			);
		}

		\WP_CLI::success( 'Done!' );
	}
}
\WP_CLI::add_command( 'wxr2pdf', __NAMESPACE__ . '\Command' );




// add_action( 'wxr2pdf_worker', [ 'Worker', 'wxr2pdf_callback' ] );
