<?php
namespace Soderlind\WXR2PDF;
! defined( 'WP_CLI' ) and exit;

define('IMPORT_DEBUG', false);

class Parser {

//	public $error_message = '';

	function parse( $file, $post_type = 'post' ) {
		// Attempt to use proper XML parsers first
		if ( extension_loaded( 'simplexml' ) ) {
			$parser = new Parser\SimpleXML;
			$result = $parser->parse( $file, $post_type );

			// If SimpleXML succeeds or this is an invalid WXR file then return the results
			if ( ! is_wp_error( $result ) || 'SimpleXML_parse_error' != $result->get_error_code() ) {
				return $result;
			}
			// } else {
			// 	$msg = array();
			// 	foreach  ( $result->get_error_data() as $error ) {
			// 		$msg[] = $error->line . ':' . $error->column . ' ' . esc_html( $error->message );
			// 	}
			// 	$msg[] =  WP_CLI::colorize('%R' . __( 'There was an error when reading this WXR file', 'wxr2pdf' ) . '%n' );
			// 	$this->error_message = implode( PHP_EOL, $msg );
			// 	return false;
			// }
		}
		// We have a malformed XML file, so display the error and fallthrough to regex
		if ( isset($result) && defined('IMPORT_DEBUG') && IMPORT_DEBUG ) {
			echo '<pre>';
			if ( 'SimpleXML_parse_error' == $result->get_error_code() ) {
				foreach  ( $result->get_error_data() as $error )
					echo $error->line . ':' . $error->column . ' ' . esc_html( $error->message ) . "\n";
			} else if ( 'XML_parse_error' == $result->get_error_code() ) {
				$error = $result->get_error_data();
				echo $error[0] . ':' . $error[1] . ' ' . esc_html( $error[2] );
			}
			echo '</pre>';
			echo '<p><strong>' . __( 'There was an error when reading this WXR file', 'wxr2pdf' ) . '</strong><br />';
		}

		// use regular expressions if nothing else available or this is bad XML
		$regexp_parser = new Parser\Regex;
		return $regexp_parser->parse( $file,  $post_type);
	}
}
