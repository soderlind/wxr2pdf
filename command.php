<?php
/**
 * WXR2PDF
 *
 * @package     Soderlind\WXR2PDF
 * @author      Per Soderlind
 * @copyright   2019 Per Soderlind
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: WXR2PDF
 * Plugin URI: https://github.com/soderlind/wxr2pdf
 * GitHub Plugin URI: https://github.com/soderlind/wxr2pdf
 * Description: WP-CLI add-on: wxr2pdf, convert an WordPress Export to PDF
 * Version:     0.0.5
 * Author:      Per Soderlind
 * Author URI:  https://soderlind.no
 * Text Domain: wxr2pdf
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace Soderlind\WXR2PDF;
! defined( 'ABSPATH' ) and exit;
define( 'WXR2PDF_PATH', __DIR__ );
define( 'WXR2PDF_CACHE', WXR2PDF_PATH . '/var/pdf' );
define( 'WXR2PDF_VERSION', '0.0.5' );
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
	 *   wp wxr2pdf wxr-file.xml
	 *   wp wxr2pdf wxr-file.xml --language=nb_NO
	 *   wp wxr2pdf wxr-file.xml --noimg
	 *   wp wxr2pdf wxr-file.xml --posttype=page
	 *   wp wxr2pdf wxr-file.xml --nocomments
	 *
	 * @synopsis <file> [--language=<country_CODE>] [--noimg] [--posttype=<posttype>]
	 */
	function __invoke( $args, $assoc_args ) {
		if ( $args ) {
			list( $wxr_file ) = $args;

			if ( ! file_exists( $wxr_file ) ) {
				\WP_CLI::line();
				\WP_CLI::error(
					\WP_CLI::colorize( sprintf( 'File  "%%R%s%%n" not found.', $wxr_file ) )
				);
			}
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

if ( class_exists('WP_CLI')) {
	\WP_CLI::add_command( 'wxr2pdf', __NAMESPACE__ . '\Command' );
}