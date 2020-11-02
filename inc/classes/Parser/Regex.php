<?php
declare( strict_types = 1 );
namespace Soderlind\WXR2PDF\Parser;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Regex {

	var $site_title      = '';
	var $site_decription = '';
	var $language        = '';
	var $authors         = [];
	var $posts           = [];
	var $categories      = [];
	var $tags            = [];
	var $terms           = [];
	var $base_url        = '';
	var $has_gzip        = false;

	function __construct() {
		$this->has_gzip = is_callable( 'gzopen' );
	}

	/**
	 * Parse the WXR file.
	 *
	 * @param string $file
	 * @param string $post_type
	 * @return \WP_Error|array
	 */
	public function parse( string $file, string $post_type ) {

		$wxr_version = $in_post = false;

		$fp = $this->fopen( $file, 'r' );
		if ( $fp ) {
			while ( ! $this->feof( $fp ) ) {
				$importline = rtrim( $this->fgets( $fp ) );

				if ( ! $wxr_version && preg_match( '|<wp:wxr_version>(\d+\.\d+)</wp:wxr_version>|', $importline, $version ) ) {
					$wxr_version = $version[1];
				}

				if ( false !== strpos( $importline, '<wp:base_site_url>' ) ) {
					preg_match( '|<wp:base_site_url>(.*?)</wp:base_site_url>|is', $importline, $url );
					$this->base_url = $url[1];
					continue;
				}
				if ( ! $in_post && ( false !== strpos( $importline, '<title>' ) ) ) {
					preg_match( '|<title>(.*?)</title>|is', $importline, $site_title );
					$this->site_title = $site_title[1];
					continue;
				}
				if ( ! $in_post && ( false !== strpos( $importline, '<description>' ) ) ) {
					preg_match( '|<description>(.*?)</description>|is', $importline, $site_decription );
					$this->site_decription = $site_decription[1];
					continue;
				}
				if ( ! $in_post && ( false !== strpos( $importline, '<language>' ) ) ) {
					preg_match( '|<language>(.*?)</language>|is', $importline, $language );
					$this->language = str_replace( '-', '_', $language[1] );
					continue;
				}
				if ( false !== strpos( $importline, '<wp:category>' ) ) {
					preg_match( '|<wp:category>(.*?)</wp:category>|is', $importline, $category );
					$this->categories[] = $this->process_category( $category[1] );
					continue;
				}
				if ( false !== strpos( $importline, '<wp:tag>' ) ) {
					preg_match( '|<wp:tag>(.*?)</wp:tag>|is', $importline, $tag );
					$this->tags[] = $this->process_tag( $tag[1] );
					continue;
				}
				if ( false !== strpos( $importline, '<wp:term>' ) ) {
					preg_match( '|<wp:term>(.*?)</wp:term>|is', $importline, $term );
					$this->terms[] = $this->process_term( $term[1] );
					continue;
				}
				if ( false !== strpos( $importline, '<wp:author>' ) ) {
					preg_match( '|<wp:author>(.*?)</wp:author>|is', $importline, $author );
					$a                                   = $this->process_author( $author[1] );
					$this->authors[ $a['author_login'] ] = $a;
					continue;
				}
				if ( false !== strpos( $importline, '<item>' ) ) {
					$post    = '';
					$in_post = true;
					continue;
				}
				if ( false !== strpos( $importline, '</item>' ) ) {
					$in_post = false;
					if ( $post_type == $this->get_tag( $post, 'wp:post_type' ) ) {
						$this->posts[] = $this->process_post( $post );
					} else {
						$this->posts[] = [];
					}
					continue;
				}
				if ( $in_post ) {
					$post .= $importline . "\n";
				}
			}

			$this->fclose( $fp );
		}

		if ( ! $wxr_version ) {
			return new \WP_Error( 'WXR_parse_error', __( 'This does not appear to be a WXR file, missing/invalid WXR version number', 'wordpress-importer' ) );
		}

		return [
			'authors'         => $this->authors,
			'posts'           => $this->posts,
			'categories'      => $this->categories,
			'tags'            => $this->tags,
			'terms'           => $this->terms,
			'base_url'        => $this->base_url,
			'site_title'      => $this->site_title,
			'site_decription' => $this->site_decription,
			'language'        => $this->language,
			'version'         => $wxr_version,
		];
	}

	/**
	 * Get tag from string.
	 *
	 * @param string $string
	 * @param string $tag
	 * @return string
	 */
	function get_tag( string $string, string $tag ) : string {
		preg_match( "|<$tag.*?>(.*?)</$tag>|is", $string, $return );
		if ( isset( $return[1] ) ) {
			if ( substr( $return[1], 0, 9 ) == '<![CDATA[' ) {
				if ( strpos( $return[1], ']]]]><![CDATA[>' ) !== false ) {
					preg_match_all( '|<!\[CDATA\[(.*?)\]\]>|s', $return[1], $matches );
					$return = '';
					foreach ( $matches[1] as $match ) {
						$return .= $match;
					}
				} else {
					$return = preg_replace( '|^<!\[CDATA\[(.*)\]\]>$|s', '$1', $return[1] );
				}
			} else {
				$return = $return[1];
			}
		} else {
			$return = '';
		}
		return $return;
	}

	function process_category( string $category ) : array {
		return [
			'term_id'              => $this->get_tag( $category, 'wp:term_id' ),
			'cat_name'             => $this->get_tag( $category, 'wp:cat_name' ),
			'category_nicename'    => $this->get_tag( $category, 'wp:category_nicename' ),
			'category_parent'      => $this->get_tag( $category, 'wp:category_parent' ),
			'category_description' => $this->get_tag( $category, 'wp:category_description' ),
		];
	}

	function process_tag( string $tag ) : array {
		return [
			'term_id'         => $this->get_tag( $tag, 'wp:term_id' ),
			'tag_name'        => $this->get_tag( $tag, 'wp:tag_name' ),
			'tag_slug'        => $this->get_tag( $tag, 'wp:tag_slug' ),
			'tag_description' => $this->get_tag( $tag, 'wp:tag_description' ),
		];
	}

	function process_term( string $term ) : array {
		return [
			'term_id'          => $this->get_tag( $term, 'wp:term_id' ),
			'term_taxonomy'    => $this->get_tag( $term, 'wp:term_taxonomy' ),
			'slug'             => $this->get_tag( $term, 'wp:term_slug' ),
			'term_parent'      => $this->get_tag( $term, 'wp:term_parent' ),
			'term_name'        => $this->get_tag( $term, 'wp:term_name' ),
			'term_description' => $this->get_tag( $term, 'wp:term_description' ),
		];
	}

	function process_author( string $author ) : array {
		return [
			'author_id'           => $this->get_tag( $author, 'wp:author_id' ),
			'author_login'        => $this->get_tag( $author, 'wp:author_login' ),
			'author_email'        => $this->get_tag( $author, 'wp:author_email' ),
			'author_display_name' => $this->get_tag( $author, 'wp:author_display_name' ),
			'author_first_name'   => $this->get_tag( $author, 'wp:author_first_name' ),
			'author_last_name'    => $this->get_tag( $author, 'wp:author_last_name' ),
		];
	}

	function process_post( string $post ) : array {
		$post_id        = $this->get_tag( $post, 'wp:post_id' );
		$post_title     = $this->get_tag( $post, 'title' );
		$post_date      = $this->get_tag( $post, 'wp:post_date' );
		$post_date_gmt  = $this->get_tag( $post, 'wp:post_date_gmt' );
		$comment_status = $this->get_tag( $post, 'wp:comment_status' );
		$ping_status    = $this->get_tag( $post, 'wp:ping_status' );
		$status         = $this->get_tag( $post, 'wp:status' );
		$post_name      = $this->get_tag( $post, 'wp:post_name' );
		$post_parent    = $this->get_tag( $post, 'wp:post_parent' );
		$menu_order     = $this->get_tag( $post, 'wp:menu_order' );
		$post_type      = $this->get_tag( $post, 'wp:post_type' );
		$post_password  = $this->get_tag( $post, 'wp:post_password' );
		$is_sticky      = $this->get_tag( $post, 'wp:is_sticky' );
		$guid           = $this->get_tag( $post, 'guid' );
		$post_author    = $this->get_tag( $post, 'dc:creator' );

		$post_excerpt = $this->get_tag( $post, 'excerpt:encoded' );
		$post_excerpt = preg_replace_callback( '|<(/?[A-Z]+)|', [ &$this, '_normalize_tag' ], $post_excerpt );
		$post_excerpt = str_replace( '<br>', '<br />', $post_excerpt );
		$post_excerpt = str_replace( '<hr>', '<hr />', $post_excerpt );

		$post_content = $this->get_tag( $post, 'content:encoded' );
		$post_content = preg_replace_callback( '|<(/?[A-Z]+)|', [ &$this, '_normalize_tag' ], $post_content );
		$post_content = str_replace( '<br>', '<br />', $post_content );
		$post_content = str_replace( '<hr>', '<hr />', $post_content );

		$postdata = compact(
			'post_id',
			'post_author',
			'post_date',
			'post_date_gmt',
			'post_content',
			'post_excerpt',
			'post_title',
			'status',
			'post_name',
			'comment_status',
			'ping_status',
			'guid',
			'post_parent',
			'menu_order',
			'post_type',
			'post_password',
			'is_sticky'
		);

		$attachment_url = $this->get_tag( $post, 'wp:attachment_url' );
		if ( $attachment_url ) {
			$postdata['attachment_url'] = $attachment_url;
		}

		preg_match_all( '|<category domain="([^"]+?)" nicename="([^"]+?)">(.+?)</category>|is', $post, $terms, PREG_SET_ORDER );
		foreach ( $terms as $t ) {
			$post_terms[] = [
				'slug'   => $t[2],
				'domain' => $t[1],
				'name'   => str_replace( [ '<![CDATA[', ']]>' ], '', $t[3] ),
			];
		}
		if ( ! empty( $post_terms ) ) {
			$postdata['terms'] = $post_terms;
		}

		preg_match_all( '|<wp:comment>(.+?)</wp:comment>|is', $post, $comments );
		$comments = $comments[1];
		if ( $comments ) {
			foreach ( $comments as $comment ) {
				preg_match_all( '|<wp:commentmeta>(.+?)</wp:commentmeta>|is', $comment, $commentmeta );
				$commentmeta = $commentmeta[1];
				$c_meta      = [];
				foreach ( $commentmeta as $m ) {
					$c_meta[] = [
						'key'   => $this->get_tag( $m, 'wp:meta_key' ),
						'value' => $this->get_tag( $m, 'wp:meta_value' ),
					];
				}

				$post_comments[] = [
					'comment_id'           => $this->get_tag( $comment, 'wp:comment_id' ),
					'comment_author'       => $this->get_tag( $comment, 'wp:comment_author' ),
					'comment_author_email' => $this->get_tag( $comment, 'wp:comment_author_email' ),
					'comment_author_IP'    => $this->get_tag( $comment, 'wp:comment_author_IP' ),
					'comment_author_url'   => $this->get_tag( $comment, 'wp:comment_author_url' ),
					'comment_date'         => $this->get_tag( $comment, 'wp:comment_date' ),
					'comment_date_gmt'     => $this->get_tag( $comment, 'wp:comment_date_gmt' ),
					'comment_content'      => $this->get_tag( $comment, 'wp:comment_content' ),
					'comment_approved'     => $this->get_tag( $comment, 'wp:comment_approved' ),
					'comment_type'         => $this->get_tag( $comment, 'wp:comment_type' ),
					'comment_parent'       => $this->get_tag( $comment, 'wp:comment_parent' ),
					'comment_user_id'      => $this->get_tag( $comment, 'wp:comment_user_id' ),
					'commentmeta'          => $c_meta,
				];
			}
		}
		if ( ! empty( $post_comments ) ) {
			$postdata['comments'] = $post_comments;
		}

		preg_match_all( '|<wp:postmeta>(.+?)</wp:postmeta>|is', $post, $postmeta );
		$postmeta = $postmeta[1];
		if ( $postmeta ) {
			foreach ( $postmeta as $p ) {
				$post_postmeta[] = [
					'key'   => $this->get_tag( $p, 'wp:meta_key' ),
					'value' => $this->get_tag( $p, 'wp:meta_value' ),
				];
			}
		}
		if ( ! empty( $post_postmeta ) ) {
			$postdata['postmeta'] = $post_postmeta;
		}

		return $postdata;
	}

	/**
	 * Fix tag
	 *
	 * @param array $matches
	 * @return string
	 */
	function _normalize_tag( array $matches ) : string {
		return '<' . strtolower( $matches[1] );
	}

	/**
	 * Open file
	 *
	 * @param string $filename
	 * @param string $mode
	 * @return resource
	 */
	function fopen( string $filename, string $mode = 'r' ) {
		if ( $this->has_gzip ) {
			return gzopen( $filename, $mode );
		}
		return fopen( $filename, $mode );
	}

	/**
	 * Check if EOF.
	 *
	 * @param mixed $fp
	 * @return bool|int
	 */
	function feof( $fp ) {
		if ( $this->has_gzip ) {
			return gzeof( $fp );
		}
		return feof( $fp );
	}

	/**
	 * Get line from file pointer.
	 *
	 * @param mixed $fp
	 * @param mixed $len
	 * @return string
	 */
	function fgets( $fp, $len = 8192 ) {
		if ( $this->has_gzip ) {
			return gzgets( $fp, $len );
		}
		return fgets( $fp, $len );
	}

	/**
	 * Close file.
	 *
	 * @param mixed $fp
	 * @return bool
	 */
	function fclose( $fp ) {
		if ( $this->has_gzip ) {
			return gzclose( $fp );
		}
		return fclose( $fp );
	}
}
