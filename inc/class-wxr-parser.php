<?php

//require_once (dirname(__FILE__)) . '/parsers.php'

class WXR2PDF_WXR_Parser {
	function parse( $file, $post_type = 'post' ) {
		// Attempt to use proper XML parsers first
		if ( extension_loaded( 'simplexml' ) ) {
			$parser = new WXR2PDF_WXR_Parser_SimpleXML;
			$result = $parser->parse( $file, $post_type );

			// If SimpleXML succeeds or this is an invalid WXR file then return the results
			if ( ! is_wp_error( $result ) || 'SimpleXML_parse_error' != $result->get_error_code() )
				return $result;
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
			echo '<p><strong>' . __( 'There was an error when reading this WXR file', 'wordpress-importer' ) . '</strong><br />';
		}
	}
}


/**
 * WXR Parser that makes use of the SimpleXML PHP extension.
 */
class WXR2PDF_WXR_Parser_SimpleXML {
	function parse( $file, $post_type ) {
		$authors = $posts = $categories = $tags = $terms = array();

		$internal_errors = libxml_use_internal_errors(true);

		$dom = new DOMDocument;
		$old_value = null;
		if ( function_exists( 'libxml_disable_entity_loader' ) ) {
			$old_value = libxml_disable_entity_loader( true );
		}
		$success = $dom->loadXML( file_get_contents( $file ) );
		if ( ! is_null( $old_value ) ) {
			libxml_disable_entity_loader( $old_value );
		}

		if ( ! $success || isset( $dom->doctype ) ) {
			return new WP_Error( 'SimpleXML_parse_error', __( 'There was an error when reading this WXR file', 'wordpress-importer' ), libxml_get_errors() );
		}

		$xml = simplexml_import_dom( $dom );
		unset( $dom );

		// halt if loading produces an error
		if ( ! $xml )
			return new WP_Error( 'SimpleXML_parse_error', __( 'There was an error when reading this WXR file', 'wordpress-importer' ), libxml_get_errors() );

		$wxr_version = $xml->xpath('/rss/channel/wp:wxr_version');
		if ( ! $wxr_version )
			return new WP_Error( 'WXR_parse_error', __( 'This does not appear to be a WXR file, missing/invalid WXR version number', 'wordpress-importer' ) );

		$wxr_version = (string) trim( $wxr_version[0] );
		// confirm that we are dealing with the correct file format
		if ( ! preg_match( '/^\d+\.\d+$/', $wxr_version ) )
			return new WP_Error( 'WXR_parse_error', __( 'This does not appear to be a WXR file, missing/invalid WXR version number', 'wordpress-importer' ) );

		$base_url = $xml->xpath('/rss/channel/wp:base_site_url');
		$base_url = (string) trim( $base_url[0] );

		$namespaces = $xml->getDocNamespaces();
		if ( ! isset( $namespaces['wp'] ) )
			$namespaces['wp'] = 'http://wordpress.org/export/1.1/';
		if ( ! isset( $namespaces['excerpt'] ) )
			$namespaces['excerpt'] = 'http://wordpress.org/export/1.1/excerpt/';

		// grab authors
		foreach ( $xml->xpath('/rss/channel/wp:author') as $author_arr ) {
			$a = $author_arr->children( $namespaces['wp'] );
			$login = (string) $a->author_login;
			$authors[$login] = array(
				'author_id' => (int) $a->author_id,
				'author_login' => $login,
				'author_email' => (string) $a->author_email,
				'author_display_name' => (string) $a->author_display_name,
				'author_first_name' => (string) $a->author_first_name,
				'author_last_name' => (string) $a->author_last_name
			);
		}

		// grab cats, tags and terms
		foreach ( $xml->xpath('/rss/channel/wp:category') as $term_arr ) {
			$t = $term_arr->children( $namespaces['wp'] );
			$categories[] = array(
				'term_id' => (int) $t->term_id,
				'category_nicename' => (string) $t->category_nicename,
				'category_parent' => (string) $t->category_parent,
				'cat_name' => (string) $t->cat_name,
				'category_description' => (string) $t->category_description
			);
		}

		foreach ( $xml->xpath('/rss/channel/wp:tag') as $term_arr ) {
			$t = $term_arr->children( $namespaces['wp'] );
			$tags[] = array(
				'term_id' => (int) $t->term_id,
				'tag_slug' => (string) $t->tag_slug,
				'tag_name' => (string) $t->tag_name,
				'tag_description' => (string) $t->tag_description
			);
		}

		foreach ( $xml->xpath('/rss/channel/wp:term') as $term_arr ) {
			$t = $term_arr->children( $namespaces['wp'] );
			$terms[] = array(
				'term_id' => (int) $t->term_id,
				'term_taxonomy' => (string) $t->term_taxonomy,
				'slug' => (string) $t->term_slug,
				'term_parent' => (string) $t->term_parent,
				'term_name' => (string) $t->term_name,
				'term_description' => (string) $t->term_description
			);
		}

		// grab posts
		foreach ( $xml->channel->item as $item ) {

			$wp = $item->children( $namespaces['wp'] );
			if ($post_type == (string) $wp->post_type) )
				$post = array(
					'post_title' => (string) $item->title,
					'guid' => (string) $item->guid,
				);

				$dc = $item->children( 'http://purl.org/dc/elements/1.1/' );
				$post['post_author'] = (string) $dc->creator;

				$content = $item->children( 'http://purl.org/rss/1.0/modules/content/' );
				$excerpt = $item->children( $namespaces['excerpt'] );
				$post['post_content'] = (string) $content->encoded;
				$post['post_excerpt'] = (string) $excerpt->encoded;


				$post['post_id'] = (int) $wp->post_id;
				$post['post_date'] = (string) $wp->post_date;
				$post['post_date_gmt'] = (string) $wp->post_date_gmt;
				$post['comment_status'] = (string) $wp->comment_status;
				$post['ping_status'] = (string) $wp->ping_status;
				$post['post_name'] = (string) $wp->post_name;
				$post['status'] = (string) $wp->status;
				$post['post_parent'] = (int) $wp->post_parent;
				$post['menu_order'] = (int) $wp->menu_order;
				$post['post_type'] = (string) $wp->post_type;
				$post['post_password'] = (string) $wp->post_password;
				$post['is_sticky'] = (int) $wp->is_sticky;

				if ( isset($wp->attachment_url) )
					$post['attachment_url'] = (string) $wp->attachment_url;

				foreach ( $item->category as $c ) {
					$att = $c->attributes();
					if ( isset( $att['nicename'] ) )
						$post['terms'][] = array(
							'name' => (string) $c,
							'slug' => (string) $att['nicename'],
							'domain' => (string) $att['domain']
						);
				}

				foreach ( $wp->postmeta as $meta ) {
					$post['postmeta'][] = array(
						'key' => (string) $meta->meta_key,
						'value' => (string) $meta->meta_value
					);
				}

				foreach ( $wp->comment as $comment ) {
					$meta = array();
					if ( isset( $comment->commentmeta ) ) {
						foreach ( $comment->commentmeta as $m ) {
							$meta[] = array(
								'key' => (string) $m->meta_key,
								'value' => (string) $m->meta_value
							);
						}
					}
				
					$post['comments'][] = array(
						'comment_id' => (int) $comment->comment_id,
						'comment_author' => (string) $comment->comment_author,
						'comment_author_email' => (string) $comment->comment_author_email,
						'comment_author_IP' => (string) $comment->comment_author_IP,
						'comment_author_url' => (string) $comment->comment_author_url,
						'comment_date' => (string) $comment->comment_date,
						'comment_date_gmt' => (string) $comment->comment_date_gmt,
						'comment_content' => (string) $comment->comment_content,
						'comment_approved' => (string) $comment->comment_approved,
						'comment_type' => (string) $comment->comment_type,
						'comment_parent' => (string) $comment->comment_parent,
						'comment_user_id' => (int) $comment->comment_user_id,
						'commentmeta' => $meta,
					);
				}

				$posts[] = $post;
			}
		}

		return array(
			'authors' => $authors,
			'posts' => $posts,
			'categories' => $categories,
			'tags' => $tags,
			'terms' => $terms,
			'base_url' => $base_url,
			'version' => $wxr_version
		);
	}
}