<?php
declare( strict_types = 1 );
namespace Soderlind\WXR2PDF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Mpdf\Mpdf;

/**
 * Create the PDF.
 */
class CreatePDF extends Options {

	private static $instance;
	private $author_firstlast;
	private $author_lastfirst;
	private $title;
	private $subject;
	private $keywords;
	private $creator;
	private $pdf;

	/**
	 * Get the PDF instance.
	 *
	 * @param array $args
	 * @return mixed
	 */
	public static function get_instance( array $args = [] ) {

		if ( self::$instance ) {
			return self::$instance;
		}

		self::$instance = new self( $args );
		return self::$instance;
	}

	private function __construct( array $args = [] ) {
		parent::get_instance();
		$this->set_options( $args );
	}

	/**
	 * Set the commandline options.
	 *
	 * @param array $args
	 * @return void
	 */
	private function set_options( array $args = [] ) {
		foreach ( $args as $arg => $value ) {
			switch ( $arg ) {
				case 'language':
					self::$options['language'] = $value;
				break;
				case 'paper-format':
					self::$options['pdf_layout']['paper_format'] = ( 'a4' == strtolower( $value ) ) ? 'A4' : 'Letter';
				break;
				case 'paper-orientation':
					self::$options['pdf_layout']['paper_orientation'] = ( 'l' == strtolower( $value ) ) ? 'L' : 'P';
				break;
				case 'posttype':
					self::$options['posttype'] = $value;
				break;
				case 'watermark':
					self::$options['pdf_watermark']['watermark']      = 'watermark_text';
					self::$options['pdf_watermark']['watermark_text'] = $value;
				break;
				case 'noimg':
					self::$options['noimg'] = true;
				break;
				case 'nocomments':
					self::$options['noimg'] = true;
				break;
			}
		}

	}

	/**
	 * Setup the PDF document.
	 *
	 * @param array $post
	 * @param string $title
	 * @param string $decription
	 * @return void
	 */
	public function init( array $post, string $title = '', string $decription = '' ) {

		// $this->author_firstlast = sprintf("%s %s",get_the_author_meta('user_firstname',$post['post_author'],get_the_author_meta('user_lastname',$post['post_author']);
		// $this->author_lastfirst = sprintf("%s, %s",get_the_author_meta('user_firstname',$post['post_author'],get_the_author_meta('user_lastname',$post['post_author']);

		$this->author_firstlast = ( isset( $post['author_display_name'] ) ) ? $post['author_display_name'] : '';
		$this->title            = _x( 'PDF dump of:', 'Prefix title in PDF metadata', 'wxr2pdf' ) . ' ' . $title;
		$this->subject          = $decription;
		// $this->keywords         = $decription; //'TERMS';//$this->_get_taxonomies_terms($post);
		$this->creator = 'WXR2PDF ' . WXR2PDF_VERSION . ' by Per Soderlind, https://github.com/soderlind/wxr2pdf';

		// content
		// $this->html .= apply_filters('the_content', $content);

		// $html = $this->html;

		defined( '_MPDF_TEMP_PATH' ) || define( '_MPDF_TEMP_PATH', WXR2PDF_CACHE . '/tmp/' );
		defined( '_MPDF_TTFONTDATAPATH' ) || define( '_MPDF_TTFONTDATAPATH', WXR2PDF_CACHE . '/font/' );
		// _MPDF_SYSTEM_TTFONTS - us when implementing font management

		// $paper_format = sprintf(
		// "'%s-%s'",
		// ( 'custom_paper_format' == parent::$options['pdf_layout']['paper_format'] ) ? parent::$options['pdf_layout']['custom_paper_format'] : parent::$options['pdf_layout']['paper_format'],
		// parent::$options['pdf_layout']['paper_orientation']
		// );

		$this->pdf = new Mpdf(
			[
				'mode'              => 'utf-8',
				'format'            => self::$options['pdf_layout']['paper_format'],
				'default_font_size' => 0,
				'default_font'      => 'dejavusans',
				'margin_left'       => 15,
				'margin_right'      => 15,
				'margin_top'        => 16,
				'margin_bottom'     => 16,
				'margin_header'     => 9,
				'margin_footer'     => 9,
				'orientation'       => self::$options['pdf_layout']['paper_orientation'],
			]
		);

		// $this->pdf->fontdata = array(	"dejavusanscondensed" => array(
		// 'R' => "DejaVuSansCondensed.ttf",
		// 'B' => "DejaVuSansCondensed-Bold.ttf",
		// 'I' => "DejaVuSansCondensed-Oblique.ttf",
		// 'BI' => "DejaVuSansCondensed-BoldOblique.ttf",
		// )
		// );

		$this->pdf->fontdata = [
			'dejavusanscondensed'  => [
				'R'          => 'DejaVuSansCondensed.ttf',
				'B'          => 'DejaVuSansCondensed-Bold.ttf',
				'I'          => 'DejaVuSansCondensed-Oblique.ttf',
				'BI'         => 'DejaVuSansCondensed-BoldOblique.ttf',
				'useOTL'     => 0xFF,
				'useKashida' => 75,
			],
			'dejavusans'           => [
				'R'          => 'DejaVuSans.ttf',
				'B'          => 'DejaVuSans-Bold.ttf',
				'I'          => 'DejaVuSans-Oblique.ttf',
				'BI'         => 'DejaVuSans-BoldOblique.ttf',
				'useOTL'     => 0xFF,
				'useKashida' => 75,
			],
			'dejavuserif'          => [
				'R'  => 'DejaVuSerif.ttf',
				'B'  => 'DejaVuSerif-Bold.ttf',
				'I'  => 'DejaVuSerif-Italic.ttf',
				'BI' => 'DejaVuSerif-BoldItalic.ttf',
			],
			'dejavuserifcondensed' => [
				'R'  => 'DejaVuSerifCondensed.ttf',
				'B'  => 'DejaVuSerifCondensed-Bold.ttf',
				'I'  => 'DejaVuSerifCondensed-Italic.ttf',
				'BI' => 'DejaVuSerifCondensed-BoldItalic.ttf',
			],
			'dejavusansmono'       => [
				'R'          => 'DejaVuSansMono.ttf',
				'B'          => 'DejaVuSansMono-Bold.ttf',
				'I'          => 'DejaVuSansMono-Oblique.ttf',
				'BI'         => 'DejaVuSansMono-BoldOblique.ttf',
				'useOTL'     => 0xFF,
				'useKashida' => 75,
			],
			'fontawesome'          => [
				'R' => 'fontawesome-webfont.ttf',
			],
		];

		// $this->pdf->sans_fonts = array('dejavusanscondensed','sans','sans-serif');

		$this->pdf->SetTitle( $this->title );
		$this->pdf->SetAuthor( $this->author_firstlast );
		$this->pdf->SetSubject( $this->subject );
		$this->pdf->SetKeywords( $this->keywords );
		$this->pdf->SetCreator( $this->creator );

		// $this->pdf->autoScriptToLang = true;
		// $this->pdf->baseScript = 1;
		// $this->pdf->autoVietnamese = true;
		// $this->pdf->autoArabic = true;
		$this->pdf->autoLangToFont = true;

		$this->pdf->ignore_invalid_utf8 = true;
		$this->pdf->useSubstitutions    = false;
		$this->pdf->simpleTables        = true;
		$this->pdf->h2bookmarks         = [
			'H1' => 0,
			'H2' => 1,
			'H3' => 2,
		];
		$this->pdf->title2annots        = true;

		/**
		 * Watermark
		 */
		$watermark = parent::$options['pdf_watermark']['watermark'];
		switch ( $watermark ) {
			case 'watermark_text':
				$this->pdf->SetWatermarkText(
					parent::$options['pdf_watermark']['watermark_text'],
					parent::$options['pdf_watermark']['watermark_tranparency']
				);
				break;

			case 'watermark_image':
				$this->pdf->SetWatermarkImage(
					parent::$options['pdf_watermark']['watermark_image'],
					parent::$options['pdf_watermark']['watermark_tranparency']
				);
				break;
		}

		/**
		 * Protection
		 */
		$has_protection = parent::$options['pdf_protection']['protection'];
		if ( 'password_owner' == $has_protection ) {
			$user_can       = array_keys(
				array_intersect_key(
					[
						'copy'          => 1,
						'print'         => 1,
						'modify'        => 1,
						'extract'       => 1,
						'assemble'      => 1,
						'print-highres' => 1,
					],
					array_filter( parent::$options['pdf_protection']['user_can_do'] )
				)
			);
			$password_user  = parent::$options['pdf_protection']['password_user'];
			$password_owner = parent::$options['pdf_protection']['password_owner'];
			$this->pdf->SetProtection( $user_can, $password_user, $password_owner, 128 );
		}

		/**
		 * PDFA
		 */
		if ( '1' == parent::$options['pdf_layout']['pdfa'] ) {
			/*
			PDFA Fatal Errors
			Some issues cannot be fixed automatically by mPDF and will generate fatal errors:
			- $useCoreFontsOnly is set as TRUE (cannot embed core fonts)
			BIG5, SJIS, UHC or GB fonts cannot be used (cannot be embedded)
			- Watermarks - text or image - are not permitted (transparency is disallowed so will make text unreadable)
			Using CMYK colour in functions SetTextColor() SetDrawColor() or SetFillColor()
			PNG images with alpha channel transparency ('masks' not allowed)
			encryption is enabled
			 */
			$this->pdf->showWatermarkText  = false;
			$this->pdf->showWatermarkImage = false;
			$this->pdf->useCoreFontsOnly   = false;
			$this->pdf->PDFA               = true;
		}

		/**
		 * header and footer
		 */
		$print_css = '';
		$header    = parent::$options['pdf_header']['header'];
		switch ( $header ) {
			case 'XXXdefault_header':
				if ( ( '0' == parent::$options['pdf_header']['default_header'][0] &&
					 '0' == parent::$options['pdf_header']['default_header'][1] &&
					 '0' == parent::$options['pdf_header']['default_header'][2] ) ) {
					break;
				}
				$this->pdf->DefHeaderByName(
					'pdfheader',
					[
						'L'    => [
							'content'     => ( '0' != parent::$options['pdf_header']['default_header'][0] ) ? $this->_header_footer( $post, parent::$options['pdf_header']['default_header'][0] ) : '',
							'font-size'   => 10,
							'font-style'  => 'B',
							'font-family' => 'serif',
							'color'       => '#000000',
						],
						'C'    => [
							'content'     => ( '0' != parent::$options['pdf_header']['default_header'][1] ) ? $this->_header_footer( $post, parent::$options['pdf_header']['default_header'][1] ) : '',
							'font-size'   => 10,
							'font-style'  => 'B',
							'font-family' => 'serif',
							'color'       => '#000000',
						],
						'R'    => [
							'content'     => ( '0' != parent::$options['pdf_header']['default_header'][2] ) ? $this->_header_footer( $post, parent::$options['pdf_header']['default_header'][2] ) : '',
							'font-size'   => 10,
							'font-style'  => 'B',
							'font-family' => 'serif',
							'color'       => '#000000',
						],
						'line' => 1,
					]
				);
				break;
			case 'custom_header':
				$this->pdf->DefHTMLHeaderByName(
					'pdfheader',
					$this->_parse_header_footer( $post, parent::$options['pdf_header']['custom_header'] )
				);
				break;
		}

		$footer = parent::$options['pdf_footer']['footer'];
		switch ( $footer ) {
			case 'XXXdefault_footer':
				if ( ( '0' == parent::$options['pdf_footer']['default_footer'][0] &&
					 '0' == parent::$options['pdf_footer']['default_footer'][1] &&
					 '0' == parent::$options['pdf_footer']['default_footer'][2] ) ) {
					break;
				}
				$this->pdf->DefFooterByName(
					'pdffooter',
					[
						'L'    => [
							'content'     => ( '0' != parent::$options['pdf_footer']['default_footer'][0] ) ? $this->_header_footer( $post, parent::$options['pdf_footer']['default_footer'][0] ) : '',
							'font-size'   => 10,
							'font-style'  => 'B',
							'font-family' => 'serif',
							'color'       => '#000000',
						],
						'C'    => [
							'content'     => ( '0' != parent::$options['pdf_footer']['default_footer'][1] ) ? $this->_header_footer( $post, parent::$options['pdf_footer']['default_footer'][1] ) : '',
							'font-size'   => 10,
							'font-style'  => 'B',
							'font-family' => 'serif',
							'color'       => '#000000',
						],
						'R'    => [
							'content'     => ( '0' != parent::$options['pdf_footer']['default_footer'][2] ) ? $this->_header_footer( $post, parent::$options['pdf_footer']['default_footer'][2] ) : '',
							'font-size'   => 10,
							'font-style'  => 'B',
							'font-family' => 'serif',
							'color'       => '#000000',
						],
						'line' => 1,
					]
				);
				break;
			case 'custom_footer':
				$this->pdf->DefHTMLFooterByName(
					'pdffooter',
					$this->_parse_header_footer( $post, parent::$options['pdf_footer']['custom_footer'] )
				);
				break;
		}

		/**
		 * Default CSS
		 */
		if ( 'default_header' == $header || 'default_footer' == $footer ) {
			$this->pdf->WriteHTML( file_get_contents( WXR2PDF_PATH . '/templates/pdf/default-print.css' ), 1 );
		}

		/**
		 * Theme / Custom CSS, overrides default css
		 */

		// $css = parent::$options['pdf_css']['custom_css'];
		// switch ($css) {
		// case 'theme_style':
		// $post_styles = $this->_get_post_styles($post['ID'];
		// $link = "";
		// foreach ($post_styles as $post_style) {
		// $f = file_get_contents($post_style);
		// if (false !== $f) {
		// $link = $link . "\n" . $f;
		// }

		// }
		// $this->pdf->CSSselectMedia = 'all';
		// $this->pdf->WriteHTML($link,1);

		// $this->pdf->WriteHTML(file_get_contents(get_stylesheet_uri()),1);
		// break;
		// case 'css':
		// $this->pdf->WriteHTML(parent::$options['pdf_css']['css'],1);
		// break;

		// }

		/**
		 * Coverart
		 */
		// $coverart = parent::$options['pdf_cover']['art'];

		// if ('none' != $coverart) {
		// switch ($coverart) {

		// case 'feature_image':
		// $image_url = wp_get_attachment_url( get_post_thumbnail_id($post['ID'], 'thumbnail') );
		// $image_data = wp_get_attachment_metadata(get_post_thumbnail_id($post['ID'], 'thumbnail'));
		// $left = ($w / 2) - ($image_data['width']  / 2);
		// $top  = ($h / 2) - ($image_data['height'] / 2);
		// $this->pdf->AddPage('','','','','on');
		// if ('' != $image_url) {
		// $this->pdf->AddPageByArray(array(
		// 'suppress' => 'on', // supress header
		// ));
		// $this->pdf->WriteHTML(
		// sprintf('
		// <div style="position: absolute; left:0; right: 0; top: 0; bottom: 0;">
		// <img src="%s" style="width: 210mm; height: 297mm; margin: 1mm;" />
		// </div>',
		// $image_url
		// )
		// );
		// }
		// break;

		// case 'custom_image':
		// $image_url = parent::$options['pdf_cover']['custom_image'];
		// $image_data = wp_get_attachment_metadata(get_post_thumbnail_id($post['ID'], 'thumbnail'));
		// $left = ($w / 2) - ($image_data['width']  / 2);
		// $top  = ($h / 2) - ($image_data['height'] / 2);
		// $this->pdf->AddPage('','','','','on');
		// if ('' != $image_url) {
		// $this->pdf->AddPageByArray(array(
		// 'suppress' => 'on', // supress header
		// ));
		// $this->pdf->WriteHTML(sprintf('<div style="position: absolute; left:0; right: 0; top: 0; bottom: 0;">
		// <img src="%s" style="width: 210mm; height: 297mm; margin: 30;" /></div>',$image_url)
		// );
		// }
		// break;
		// }
		// we don't want watermarks on the cover page
		// $this->pdf->showWatermarkImage = false;
		// $this->pdf->showWatermarkText  = false;
		// }

		if ( '' != $title ) {
			$this->pdf->WriteHTML(
				// sprintf('
				// <div style="height: 200px; width: 400px;margin: 150px auto; background: #eee;">
				// <h1 style="font: 40px/200px Helvetica, sans-serif;text-align: center;">En tittel som er passe lang</h1>
				// </div>'
				// )
				sprintf(
					'
					<div>
						<h1 style="font-size: 200%%; text-align: center">%s</h1>
						<p>%s</p>
					</div>
					',
					$title,
					$decription
				)
			);
			$this->pdf->showWatermarkImage = false;
			$this->pdf->showWatermarkText  = false;
		}

		$toc = parent::$options['pdf_layout']['add_toc'];
		$this->pdf->AddPageByArray(
			[
				'suppress'     => 'off', // don't supress headers
				'ohname'       => ( '0' != $header ) ? ( 'custom_header' == $header ) ? 'html_pdfheader' : 'pdfheader' : '',
				'ehname'       => ( '0' != $header ) ? ( 'custom_header' == $header ) ? 'html_pdfheader' : 'pdfheader' : '',
				'ofname'       => ( '0' != $footer ) ? ( 'custom_footer' == $footer ) ? 'html_pdffooter' : 'pdffooter' : '',
				'efname'       => ( '0' != $footer ) ? ( 'custom_footer' == $footer ) ? 'html_pdffooter' : 'pdffooter' : '',
				'ohvalue'      => ( '0' != $header ) ? 1 : 0,
				'ehvalue'      => ( '0' != $header ) ? 1 : 0,
				'ofvalue'      => ( '0' != $footer ) ? 1 : 0,
				'efvalue'      => ( '0' != $footer ) ? 1 : 0,
				'resetpagenum' => ( '0' != $toc ) ? 2 : 1,
			]
		);

		/**
		 * Table og contents
		 */

		if ( '0' !== $toc ) {
			$toc_start = ( '0' == parent::$options['pdf_layout']['toc'][0] ) ? 1 : parent::$options['pdf_layout']['toc'][0];
			$toc_stop  = ( '0' == parent::$options['pdf_layout']['toc'][1] ) ? 2 : parent::$options['pdf_layout']['toc'][1];
			if ( $toc_start > $toc_stop ) {
				$toc_stop = $toc_start + 1;
			}
			$toc_arr = [];
			$j       = 0;
			for ( $i = $toc_start; $i <= $toc_stop; $i++ ) {
				$toc_arr[ sprintf( 'H%s', $i ) ] = $j++;
			}
			$this->pdf->h2toc = $toc_arr;
			$this->pdf->TOCpagebreakByArray(
				[
					// 'tocfont' => '',
					// 'tocfontsize' => '',
					// 'outdent' => '2em',
					'TOCusePaging'     => true,
					'TOCuseLinking'    => true,
					// 'toc_orientation' => '',
					// 'toc_mgl' => '',
					// 'toc_mgr' => '',
					// 'toc_mgt' => '',
					// 'toc_mgb' => '',
					// 'toc_mgh' => '',
					// 'toc_mgf' => '',
					// 'toc_ohname' => ('0' != $header ) ? ('custom_header' == $header) ? 'html_pdfheader' : 'pdfheader' : '',
					// 'toc_ehname' => ('0' != $header ) ? ('custom_header' == $header) ? 'html_pdfheader' : 'pdfheader' : '',
					// 'toc_ofname' => ('0' != $footer ) ? ('custom_footer' == $footer) ? 'html_pdffooter' : 'pdffooter' : '',
					// 'toc_efname' => ('0' != $footer ) ? ('custom_footer' == $footer) ? 'html_pdffooter' : 'pdffooter' : '',
					// 'toc_ohvalue' => ('0' != $header ) ? 1 : 0,
					// 'toc_ehvalue' => ('0' != $header ) ? 1 : 0,
					// 'toc_ofvalue' => ('0' != $footer ) ? 1 : 0,
					// 'toc_efvalue' => ('0' != $footer ) ? 1 : 0,
					'toc_ohvalue'      => -1,
					'toc_ehvalue'      => -1,
					'toc_ofvalue'      => -1,
					'toc_efvalue'      => -1,
					'toc_preHTML'      => '<h1>' . __( 'Contents', 'wxr2pdf' ) . '</h1>',
					'toc_postHTML'     => '',
					'toc_bookmarkText' => __( 'Contents', 'wxr2pdf' ),
					'resetpagenum'     => 2,
					'pagenumstyle'     => '',
					'suppress'         => 'off',
					'orientation'      => '',
				// 'mgl' => '',
				// 'mgr' => '',
				// 'mgt' => '',
				// 'mgb' => '',
				// 'mgh' => '',
				// 'mgf' => '',
				// 'ohname' => ('0' != $header ) ? ('custom_header' == $header) ? 'html_pdfheader' : 'pdfheader' : '',
				// 'ehname' => ('0' != $header ) ? ('custom_header' == $header) ? 'html_pdfheader' : 'pdfheader' : '',
				// 'ofname' => ('0' != $footer ) ? ('custom_footer' == $footer) ? 'html_pdffooter' : 'pdffooter' : '',
				// 'efname' => ('0' != $footer ) ? ('custom_footer' == $footer) ? 'html_pdffooter' : 'pdffooter' : '',
				// 'ohvalue' => ('0' != $header ) ? 1 : 0,
				// 'ehvalue' => ('0' != $header ) ? 1 : 0,
				// 'ofvalue' => ('0' != $footer ) ? 1 : 0,
				// 'efvalue' => ('0' != $footer ) ? 1 : 0,
				// 'toc_id' => 0,
				// 'pagesel' => '',
				// 'toc_pagesel' => '',
				// 'sheetsize' => '',
				// 'toc_sheetsize' => '',
				]
			);
		}
		// if waters are set, show them
		$this->pdf->showWatermarkImage = true;
		$this->pdf->showWatermarkText  = true;

	}

	/**
	 * Write HTML code to the document Also used internally to parse HTML into buffer.
	 *
	 * @param string $html
	 * @return void
	 */
	function create( string $html ) {

		// $html = '<h1 class="entry-title">' . $post['post_title'] . '</h1>';
		// $content = $post['post_content'];
		// $content = preg_replace("/\[\\/?readoffline(\\s+.*?\]|\])/i", "", $content); // remove all [readonline] shortcodes

		$this->pdf->WriteHTML( $html );
	}

	// function attach($file_list) {
	// $this->pdf->SetImportUse();
	// foreach ($file_list as $file) {
	// $pagecount = $this->pdf->SetSourceFile($file['url']);
	// for ($i=1; $i<=($pagecount); $i++) {
	// $this->pdf->AddPage();
	// $import_page = $this->pdf->ImportPage($i);
	// $this->pdf->UseTemplate($import_page);
	// }
	// }
	// }

	function save( string $filename ) {
		$this->pdf->Output( $filename, 'F' );
	}

	private function _header_footer( array $post, string $type ) {
		$val = '';
		switch ( $type ) {
			case 'document_title':
				$val = $post['post_title'];
				break;
			case 'author':
				$val = get_the_author_meta( 'display_name', $post['post_author'] );
				break;
			case 'document_url':
				$val = get_permalink( $post['ID'] );
				break;
			case 'site_url':
				$val = home_url();
				break;
			case 'site_title':
				$val = get_bloginfo( 'name' );
				break;
			case 'page_number':
				$val = '{PAGENO}/{nbpg}';
				break;
			case 'date':
				$val = get_the_date( get_option( 'date_format' ), $post['ID'] );
				break;
		}
		return $val;
	}

	private function _parse_header_footer( array $post, string $html, bool $strip_tages = false ) : string {
		// {DATE}, {TODAY}, {TITLE}, {AUTHOR}, {DOCURL}, {SITENAME}, {SITEURL}
		if ( false !== $strip_tages ) {
			$html = addslashes( strip_tags( $html ) );
		}

		$html = str_replace( '{DATE}', get_the_date( get_option( 'date_format' ), $post['ID'] ), $html );
		$html = str_replace( '{TODAY}', sprintf( '{DATE %s}', get_option( 'date_format' ) ), $html );
		$html = str_replace( '{TITLE}', $post['post_title'], $html );
		$html = str_replace( '{AUTHOR}', get_the_author_meta( 'display_name', $post['post_author'] ), $html );
		$html = str_replace( '{DOCURL}', get_permalink( $post['ID'] ), $html );
		$html = str_replace( '{SITENAME}', get_bloginfo( 'name' ), $html );
		$html = str_replace( '{SITEURL}', home_url(), $html );
		return $html;
	}
}
