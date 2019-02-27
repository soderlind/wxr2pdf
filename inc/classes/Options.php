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
		self::$options                              = array();
		self::$options['copyright']['message']      = 'copyright NN 2015';
		self::$options['pdf_cover']['art']          = 'none';
		self::$options['pdf_cover']['custom_image'] = '';
		self::$options['pdf_css']['css']            = '';
		self::$options['pdf_css']['custom_css']     = '';

		self::$options['pdf_header']['header']            = 'default_header';
		self::$options['pdf_header']['custom_header']     = '';
		self::$options['pdf_header']['default_header'][0] = '';
		self::$options['pdf_header']['default_header'][1] = '';
		self::$options['pdf_header']['default_header'][2] = '';

		self::$options['pdf_footer']['footer']            = '';
		self::$options['pdf_footer']['custom_footer']     = '';
		self::$options['pdf_footer']['default_footer'][0] = '';
		self::$options['pdf_footer']['default_footer'][1] = '';
		self::$options['pdf_footer']['default_footer'][2] = '';

		self::$options['pdf_layout']['paper_format']      = 'A4';
		self::$options['pdf_layout']['paper_orientation'] = 'P';
		self::$options['pdf_layout']['pdfa']              = '0';

		self::$options['pdf_layout']['add_toc'] = '1';
		self::$options['pdf_layout']['toc'][0]  = '1';
		self::$options['pdf_layout']['toc'][1]  = '3';

		self::$options['pdf_protection']['protection']     = '';  //'has_protection' == true
		self::$options['pdf_protection']['password_owner'] = '';
		self::$options['pdf_protection']['password_user']  = '';
		self::$options['pdf_protection']['user_can_do']    = '';

		//      self::$options['pdf_watermark']['watermark'] = 'watermark_text';
		self::$options['pdf_watermark']['watermark']             = '';
		self::$options['pdf_watermark']['watermark_image']       = '';
		self::$options['pdf_watermark']['watermark_text']        = 'WXR2PDF.com';
		self::$options['pdf_watermark']['watermark_tranparency'] = '0.1';

		self::$options['pdffooter']['default_footer'][0] = '';
		self::$options['pdffooter']['default_footer'][1] = '';
		self::$options['pdffooter']['default_footer'][2] = '';
	}
}