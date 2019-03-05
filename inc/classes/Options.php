<?php
namespace Soderlind\WXR2PDF;
! defined( 'WP_CLI' ) and exit;
/**
*
*/
class Options {

	public static $options;
	private static $instance;

	public static function get_instance() {

		if ( self::$instance ) {
			return self::$instance;
		}

		self::$instance = new self();
		return self::$instance;
	}


	private function __construct() {
		self::$options = [
			'copyright'              => [
				'message'               => 'wp-cli wxr2pdf',
			],
			'pdf_cover'              => [
				'art'                   => 'none',
				'custom_image'          => '',
			],
			'pdf_css'                => [
				'css'                   => '',
				'custom_css'            => '',
			],
			'pdf_header'             => [
				'header'                => 'default_header',
				'custom_header'         => '',
				'default_header'        => ['', '', ''],
			],
			'pdf_footer'             => [
				'footer'                => 'default_header',
				'custom_footer'         => '',
				'default_footer'        => ['','','']
			],
			'pdf_layout'             => [
				'paper_format'          => 'A4',
				'paper_orientation'     => 'P',
				'pdfa'                  => '0',
				'add_toc'               => '1',
				'toc'                   => [ '1', '3' ],
			],
			'pdf_protection'         => [
				'protection'            => '',
				'password_owner'        => '',
				'password_user'         => '',
				'user_can_do'           => '',
			],
			'pdf_watermark'          => [
				'watermark'             => '', // watermark_text
				'watermark_image'       => '',
				'watermark_text'        => 'WXR2PDF',
				'watermark_tranparency' => '0.1',
			]
		];
	}
}
