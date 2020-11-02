<?php
declare( strict_types = 1 );
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
 * Version:     1.1.1
 * Author:      Per Soderlind
 * Author URI:  https://soderlind.no
 * Text Domain: wxr2pdf
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace Soderlind\WXR2PDF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'WXR2PDF_PATH', __DIR__ );
define( 'WXR2PDF_CACHE', WXR2PDF_PATH . '/var/pdf' );
define( 'WXR2PDF_VERSION', '1.0.0' );
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
	 * [<file>]
	 * : Path to WXR file for parsing, --stdin overrids this
	 *
	 * [--stdin]
	 * : Read export data from STDIN eg xyz
	 *
	 * [--posttype=<posttype>]
	 * : Select post type. If not set, "post" is used. Separate post types using colon, eg --posttype=post:page
	 *
	 * [--language=<country_CODE>]
	 * : Loads languages/country_CODE.mo
	 *
	 * [--paper-format=<format>]
	 * : Default is A4, alternative is Letter
	 *
	 * [--paper-orientation=<oriantation>]
	 * : Default is P (portarit), alternative is L (landscape)
	 *
	 * [--watermark=<text>]
	 * : Add watermark to the PDF
	 *
	 * [--noimg]
	 * : Dont include images
	 *
	 * [--nocomments]
	 * : Dont include comments
	 *
	 * ## EXAMPLES
	 *
	 *   wp wxr2pdf wxr-file.xml
	 *   wp wxr2pdf wxr-file.xml --noimg
	 *   wp wxr2pdf wxr-file.xml --nocomments
	 *   wp wxr2pdf wxr-file.xml --posttype=page
	 *   wp wxr2pdf wxr-file.xml --language=nb_NO
	 *   wp export --stdout | wp wxr2pdf --stdin
	 *
	 * @synopsis [<file>] [--stdin] [--noimg] [--nocomments] [--posttype=<posttype>] [--language=<country_CODE>] [--paper-format=<format>] [--paper-orientation=<oriantation>] [--watermark=<text>]
	 */
	function __invoke( array $args, array $assoc_args ) : void {
		$wxr_file = '';
		if ( isset( $args[0] ) ) {
			$wxr_file = $args[0];
		}

		// Handle input from STDIN.
		if ( isset( $assoc_args['stdin'] ) ) {
			$wxr_file = tempnam( sys_get_temp_dir(), 'WXR2PDF_' );
			file_put_contents( $wxr_file, file_get_contents( 'php://stdin' ) );
		}
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
		\WP_CLI::success( 'Done!' );
	}
}

if ( class_exists( 'WP_CLI' ) ) {
	\WP_CLI::add_command( 'wxr2pdf', __NAMESPACE__ . '\Command' );
}
