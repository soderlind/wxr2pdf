<?php
declare( strict_types = 1 );
namespace Soderlind\WXR2PDF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * WXR2PDF Worker
 */
class Worker {

	static $urls = [];
	/**
	 * Build and save PDF
	 *
	 * @param array $args
	 * @return void
	 */
	static function callback( array $args ) {

		$wxr_file      = $args['file'];
		$pdf = CreatePDF::get_instance( $args['assoc_args'] );

		$str_post_type = ( isset( $pdf::$options['posttype'] ) && '' != $pdf::$options['posttype'] ) ? $pdf::$options['posttype'] : 'post';
		// $post_types = array_flip(explode(':', $str_post_type));
		$post_types  = explode( ':', $str_post_type );
		$total_posts = 0;
		foreach ( $post_types as $post_type ) {
			$total_posts += ( wp_count_posts( $post_type )->publish ) ? wp_count_posts( $post_type )->publish : 0;
		}

		if ( isset( $pdf::$options['language'] ) ) {
			$mofile = WXR2PDF_PATH . '/languages/' . $pdf::$options['language'] . '.mo';
			load_textdomain( 'wxr2pdf', $mofile );
		}
		// add twig template engine
		$loader = new \Twig\Loader\FilesystemLoader( WXR2PDF_PATH . '/templates/twig' );
		$twig   = new \Twig\Environment(
			$loader,
			[
				'cache' => WXR2PDF_PATH . '/var/twig',
			]
		);


		$parser = new Parser();
		$attachments = $parser->parse( $wxr_file, 'attachment' );

		$progress_bar = \WP_CLI\Utils\make_progress_bar( 'Making PDF: ', $total_posts );

		foreach ( $post_types as $post_type ) {
			$data  = [];
			$data  = $parser->parse( $wxr_file, $post_type );
			$posts = $sort_array = [];
			foreach ( $data['posts'] as $key => $post ) {

				if ( isset( $post['post_type'] ) && 'publish' == $post['status'] ) {
					/*
					 * test if variables are set
					 */
					$post['author_email']        = $data['authors'][ $post['post_author'] ]['author_email'];
					$post['author_display_name'] = $data['authors'][ $post['post_author'] ]['author_display_name'];
					$post['author_first_name']   = $data['authors'][ $post['post_author'] ]['author_first_name'];
					$post['author_last_name']    = $data['authors'][ $post['post_author'] ]['author_last_name'];

					$category = $tag = [];
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

					if ( isset( $post['postmeta'] ) ) {
						foreach ( $post['postmeta'] as $meta ) {
							if ( isset( $meta['key'] ) ) {
								switch ( $meta['key'] ) {
									case '_thumbnail_id':
										$thumb_id       = $meta['value'];
										$attachment     = self::_find_post( $attachments['posts'], [ 'post_id' => $thumb_id ] );
										$attachment_url = ! empty( $attachment['attachment_url'] ) ? $attachment['attachment_url'] : $attachment['guid'];
									break;
								}
								// export gets meta straight from the DB so could have a serialized string
								// if ( ! $value ) {
								// 	$value = maybe_unserialize( $meta['value'] );
								// }
							}
						}
					}

					// $attachment_url = '';
					// if ( isset( $post['postmeta'] ) && false != ( $featured = self::_find_post( $post['postmeta'], [ 'key' => '_thumbnail_id' ] ) ) ) {
					// 	$thumb_id       = $featured['value'];
					// 	$attachment     = self::_find_post( $attachments['posts'], [ 'post_id' => $thumb_id ] );
					// 	$attachment_url = ! empty( $attachment['attachment_url'] ) ? $attachment['attachment_url'] : $attachment['guid'];
					// }
					$post['featured_image'] = $attachment_url;

					// sortarray - from http://php.net/manual/en/function.ksort.php#98465
					foreach ( $post as $key => $value ) {
						if ( ! isset( $sort_array[ $key ] ) ) {
							$sort_array[ $key ] = [];
						}
						$sort_array[ $key ][] = $value;
					}

					// If param --nocomments, remove comments.
					// if ( isset( $pdf::$options['nocomments'], $post['comments'] ) ) {

					if ( isset( $pdf::$options['nocomments'], $post['comments'] ) ) {
						unset( $post['comments'] );
					}

					// add to posts array
					$posts[] = $post;
					$progress_bar->tick();
				}
			}

			// from from http://php.net/manual/en/function.ksort.php#98465
			$orderby = 'post_date'; // change this to whatever key you want from the array
			if ( ! $posts ) {
				if ( defined( 'WP_CLI' ) && WP_CLI ) {
					\WP_CLI::line( \WP_CLI::colorize( 'Post type "%C' . $post_type . '%n" not found in file.' ) );
				}
				continue;
			}
			array_multisort( $sort_array[ $orderby ], SORT_DESC, $posts );

			$all_post_slugs = [];
			$post_ids       = $sort_array['post_id'];
			foreach ( $sort_array['post_name'] as $key => $post_name ) {
				$all_post_slugs[ $post_ids[ $key ] ] = $post_name;
			}

			self::clidebug( $all_post_slugs );
			self::clidebug( array_filter( $data, 'is_scalar' ) ); // http://stackoverflow.com/a/13088138/1434155

			$pdf->init( $posts[0], $data['site_title'], $data['site_decription'] );

			$html     = $twig->render(
				'page.twig',
				[
					'posts'    => $posts,
					'site'     => array_filter( $data, 'is_scalar' ), // http://stackoverflow.com/a/13088138/1434155
					'posttype' => $post_type,
					'l10n'     => [
						'pretitle'    => __( 'Post Type', 'wxr2pdf' ),
						'by'          => __( 'By', 'wxr2pdf' ),
						'comments'    => __( 'Comments', 'wxr2pdf' ),
						'name'        => __( 'Name', 'wxr2pdf' ),
						'email'       => __( 'Email', 'wxr2pdf' ),
						'url'         => __( 'URL', 'wxr2pdf' ),
						'date'        => __( 'Date', 'wxr2pdf' ),
						'date_format' => __( 'm/d/Y', 'wxr2pdf' ),
					],
					'doc'      => [
						'title'       => basename( $wxr_file, '.xml' ),
						'madeby'      => 'wxr2pdf',
						'titleprefix' => __( 'File:', 'wxr2pdf' ),
					],
				]
			);
			$html     = apply_filters( 'the_content', $html );
			$filename = sprintf(
				'%s/%s-%s-%s.pdf',
				getcwd(),
				sanitize_title( $data['site_title'] ),
				$post_type,
				date( 'Ymdhis' )
			);

			$download_dir = getcwd() . '/' . sanitize_title( $data['site_title'] );

			// $this->clidebug( $pdf::$options );

			if (  true === $pdf::$options['noimg'] ) {
				$html = self::_remove_img_tag( $html );
			} else {
				$html = self::_remove_img_link( $html );
			}

			$html = self::_get_linked_elements( $html, $download_dir, $data['base_url'], $all_post_slugs );

			// return body->innerhtml
			$html = preg_replace( '~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $html );

			$html = $twig->render(
				'document.twig',
				[
					'content'  => $html,
					'urls'     => static::$urls,
					'site'     => array_filter( $data, 'is_scalar' ), // http://stackoverflow.com/a/13088138/1434155
					'posttype' => $post_type,
					'l10n'     => [
						'pretitle'       => __( 'Post Type', 'wxr2pdf' ),
						'by'             => __( 'By', 'wxr2pdf' ),
						'comments'       => __( 'Comments', 'wxr2pdf' ),
						'name'           => __( 'Name', 'wxr2pdf' ),
						'email'          => __( 'Email', 'wxr2pdf' ),
						'url'            => __( 'URL', 'wxr2pdf' ),
						'date'           => __( 'Date', 'wxr2pdf' ),
						'date_format'    => __( 'm/d/Y', 'wxr2pdf' ),
						// 'external_files' => __('External files','wxr2pdf')
						'external_files' => __( 'Eksterne filer', 'wxr2pdf' ),
					],
					'doc'      => [
						'title'       => basename( $wxr_file, '.xml' ),
						'madeby'      => 'This PDF is created using wxr2pdf',
						'titleprefix' => __( 'File:', 'wxr2pdf' ),
					],
				]
			);

			$pdf->create( $html );
			$pdf->save( $filename );

		}
		$progress_bar->finish();
	}

	/**
	 * Remove img tag.
	 *
	 * @param string $html
	 * @return string
	 */
	static function _remove_img_tag( string $html ) : string {
		// remove images
		$dom = new \DOMDocument();
		@$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) /*,LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD*/ );
		$images = $dom->getElementsByTagName( 'img' );

		// from http://us2.php.net/manual/en/domnode.removechild.php#90292
		$images_to_remove = [];
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

	/**
	 * Remove image link.
	 *
	 * @param string $html
	 * @return string
	 */
	static function _remove_img_link( string $html ) : string {
		// remove images
		$dom = new \DOMDocument();
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


	/**
	 * Find linked documents.
	 *
	 * @param string $html
	 * @param string $download_dir
	 * @param string $base_url
	 * @param array $all_post_slugs
	 * @return string
	 */
	static function _get_linked_elements( string $html, string $download_dir, string $base_url, array $all_post_slugs = [] ) : string {

		$dom = new \DOMDocument();
		@$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) /*, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD*/ );
		$dom->formatOutput       = true;
		$dom->preserveWhitespace = false;

		$tags = $dom->getElementsByTagName( 'a' );
		$i    = 1;
		for ( $k = $tags->length - 1; $k >= 0; $k-- ) {
			$tag  = $tags->item( $k );
			$url  = $tag->getAttribute( 'href' );
			$link = ( in_array( substr( $url, strrpos( $url, '.' ) + 1 ), [ 'pdf' ] ) ) ? $url : '';

			if ( false !== strpos( str_replace( [ 'http://', 'https://' ], '', $url ), str_replace( [ 'http://', 'https://' ], '', $base_url ) ) && '' !== $link ) {
				if ( ! file_exists( $download_dir ) ) {
					if ( true !== wp_mkdir_p( $download_dir ) ) {
						continue;
					}
				}
				if ( ! file_exists( $download_dir . '/' . basename( $url ) ) ) {
					self::copyfile_chunked( $url, $download_dir . '/' . basename( $url ) );
				}
				$tag->setAttribute( 'href', basename( $download_dir ) . '/' . basename( $url ) );
				self::$urls[] = [
					'url' => basename( $download_dir ) . '/' . basename( $url ),
					'txt' => $tag->textContent,
				];
				// create reference link
				// $e = $dom->createElement('a', ' [' . $i . ']');
				// $a = $dom->appendChild($e);
				// $a->setAttribute('href', '#wxr2pdf-urls');
				// $sup = $dom->createElement('span');
				// $sup->appendChild($a);
				// $tag->parentNode->insertBefore($sup, $tag->nextSibling);

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
				// $icon_external_link->appendXML('<span style="font-family: fontawesome; vertical-align: bottom;"> &#xf08e;</span>'); // insert arbitary html into the fragment
				$icon_external_link->appendXML( htmlentities( '<span style="vertical-align: bottom;"> (' . $url . ')</span>' ) ); // insert arbitary html into the fragment
				if ( ! empty( $icon_external_link ) ) {
					$tag->appendChild( $icon_external_link );
				}
			}
		}
		return $dom->saveHTML();
	}

	/**
	 * Summary of _in_slugs
	 * @param string $url
	 * @param array $slugs
	 * @return mixed
	 */
	static function _in_slugs( string $url, array $slugs ) {
		$slug_id = 0;
		$query   = parse_url( $url, PHP_URL_QUERY );
		if ( '' != $query ) {
			$args = wp_parse_args(
				$query,
				[]
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
	 * Copy remote file over HTTP one small chunk at a time.
	 *
	 * @link http://stackoverflow.com/a/4000569/1434155
	 * @param string $infile  The full URL to the remote file
	 * @param string $outfile The path where to save the file
	 * @return mixed
	 */
	static function copyfile_chunked( string $infile, string $outfile ) {
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
		$headers = [];
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

	/**
	 * findWhere.
	 *
	 * @link http://stackoverflow.com/a/19995603/1434155
	 * @param array $array
	 * @param array $matching
	 * @return mixed
	 */
	static function _find_post( array $array, array $matching ) {
		foreach ( (array) $array as $item ) {
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

	/**
	 * Recursly find key in array.
	 *
	 * @param mixed $key
	 * @param array $form
	 * @return mixed
	 */
	static function _array_find_element_by_key( $key, array $form ) {
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

	/**
	 * Write debug information.
	 * @param mixed $val
	 * @return void
	 */
	private static function clidebug( $val ) {
		if ( defined( 'WP_CLI' ) && WP_CLI && WXR2PDF_DEBUG ) {
			\WP_CLI::print_value( $val );
		}
	}


}
