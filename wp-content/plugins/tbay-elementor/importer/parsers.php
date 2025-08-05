<?php
/**
 * WordPress eXtended RSS file parser implementations
 *
 * @package WordPress
 * @subpackage Importer
 */


/**
 * WordPress Importer class for managing parsing of WXR files.
 */
class WXR_Parser {
	function parse( $file ) {
		// Attempt to use proper XML parsers first
		if ( extension_loaded( 'simplexml' ) ) {
			$parser = new WXR_Parser_SimpleXML;
			$result = $parser->parse( $file );

			// If SimpleXML succeeds or this is an invalid WXR file then return the results
			if ( ! is_wp_error( $result ) || 'SimpleXML_parse_error' != $result->get_error_code() )
				return $result;
		} else if ( extension_loaded( 'xml' ) ) {
			$parser = new WXR_Parser_XML;
			$result = $parser->parse( $file );

			// If XMLParser succeeds or this is an invalid WXR file then return the results
			if ( ! is_wp_error( $result ) || 'XML_parse_error' != $result->get_error_code() )
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
			echo __( 'Details are shown above. The importer will now try again with a different parser...', 'wordpress-importer' ) . '</p>';
		}

		// use regular expressions if nothing else available or this is bad XML
		$parser = new WXR_Parser_Regex;
		return $parser->parse( $file );
	}
}


class WXR_Parser_SimpleXML {
	function parse( $file ) {
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
		$base_url = (string) trim( isset( $base_url[0] ) ? $base_url[0] : '' );


		$base_blog_url = $xml->xpath('/rss/channel/wp:base_blog_url');
		if ( $base_blog_url ) {
			$base_blog_url = (string) trim( $base_blog_url[0] );
		} else {
			$base_blog_url = $base_url;
		}

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
			$category = array(
				'term_id' => (int) $t->term_id,
				'category_nicename' => (string) $t->category_nicename,
				'category_parent' => (string) $t->category_parent,
				'cat_name' => (string) $t->cat_name,
				'category_description' => (string) $t->category_description
			);

			foreach ( $t->termmeta as $meta ) {
				$category['termmeta'][] = array(
					'key' => (string) $meta->meta_key,
					'value' => (string) $meta->meta_value
				);
			}

			$categories[] = $category;
		}

		foreach ( $xml->xpath('/rss/channel/wp:tag') as $term_arr ) {
			$t = $term_arr->children( $namespaces['wp'] );
			$tag = array(
				'term_id' => (int) $t->term_id,
				'tag_slug' => (string) $t->tag_slug,
				'tag_name' => (string) $t->tag_name,
				'tag_description' => (string) $t->tag_description
			);

			foreach ( $t->termmeta as $meta ) {
				$tag['termmeta'][] = array(
					'key' => (string) $meta->meta_key,
					'value' => (string) $meta->meta_value
				);
			}

			$tags[] = $tag;
		}

		foreach ( $xml->xpath('/rss/channel/wp:term') as $term_arr ) {
			$t = $term_arr->children( $namespaces['wp'] );
			$term = array(
				'term_id' => (int) $t->term_id,
				'term_taxonomy' => (string) $t->term_taxonomy,
				'slug' => (string) $t->term_slug,
				'term_parent' => (string) $t->term_parent,
				'term_name' => (string) $t->term_name,
				'term_description' => (string) $t->term_description
			);

			foreach ( $t->termmeta as $meta ) {
				$term['termmeta'][] = array(
					'key' => (string) $meta->meta_key,
					'value' => (string) $meta->meta_value
				);
			}

			$terms[] = $term;
		}

		// grab posts
		foreach ( $xml->channel->item as $item ) {
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

			$wp = $item->children( $namespaces['wp'] );
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

		return array(
			'authors' => $authors,
			'posts' => $posts,
			'categories' => $categories,
			'tags' => $tags,
			'terms' => $terms,
			'base_url' => $base_url,
			'base_blog_url' => $base_blog_url,
			'version' => $wxr_version
		);
	}
}


class WXR_Parser_Regex {
	var $authors = array();
	var $posts = array();
	var $categories = array();
	var $tags = array();
	var $terms = array();
	var $base_url = '';
	var $base_blog_url = '';

	function __construct() {
		$this->has_gzip = is_callable( 'gzopen' );
	}

	function parse( $file ) {
		$wxr_version = $in_multiline = false;

		$multiline_content = '';

		$multiline_tags = array(
			'item'        => array( 'posts', array( $this, 'process_post' ) ),
			'wp:category' => array( 'categories', array( $this, 'process_category' ) ),
			'wp:tag'      => array( 'tags', array( $this, 'process_tag' ) ),
			'wp:term'     => array( 'terms', array( $this, 'process_term' ) ),
		);

		$fp = $this->fopen( $file, 'r' );
		if ( $fp ) {
			while ( ! $this->feof( $fp ) ) {
				$importline = rtrim( $this->fgets( $fp ) );

				if ( ! $wxr_version && preg_match( '|<wp:wxr_version>(\d+\.\d+)</wp:wxr_version>|', $importline, $version ) )
					$wxr_version = $version[1];

				if ( false !== strpos( $importline, '<wp:base_site_url>' ) ) {
					preg_match( '|<wp:base_site_url>(.*?)</wp:base_site_url>|is', $importline, $url );
					$this->base_url = $url[1];
					continue;
				}

				if ( false !== strpos( $importline, '<wp:base_blog_url>' ) ) {
					preg_match( '|<wp:base_blog_url>(.*?)</wp:base_blog_url>|is', $importline, $blog_url );
					$this->base_blog_url = $blog_url[1];
					continue;
				} else {
					$this->base_blog_url = $this->base_url;
				}

				if ( false !== strpos( $importline, '<wp:author>' ) ) {
					preg_match( '|<wp:author>(.*?)</wp:author>|is', $importline, $author );
					$a = $this->process_author( $author[1] );
					$this->authors[$a['author_login']] = $a;
					continue;
				}

				foreach ( $multiline_tags as $tag => $handler ) {
					// Handle multi-line tags on a singular line
					if ( preg_match( '|<' . $tag . '>(.*?)</' . $tag . '>|is', $importline, $matches ) ) {
						$this->{$handler[0]}[] = call_user_func( $handler[1], $matches[1] );

					} elseif ( false !== ( $pos = strpos( $importline, "<$tag>" ) ) ) {
						// Take note of any content after the opening tag
						$multiline_content = trim( substr( $importline, $pos + strlen( $tag ) + 2 ) );

						// We don't want to have this line added to `$is_multiline` below.
						$importline        = '';
						$in_multiline      = $tag;

					} elseif ( false !== ( $pos = strpos( $importline, "</$tag>" ) ) ) {
						$in_multiline          = false;
						$multiline_content    .= trim( substr( $importline, 0, $pos ) );

						$this->{$handler[0]}[] = call_user_func( $handler[1], $multiline_content );
					}
				}

				if ( $in_multiline && $importline ) {
					$multiline_content .= $importline . "\n";
				}
			}

			$this->fclose($fp);
		}

		if ( ! $wxr_version )
			return new WP_Error( 'WXR_parse_error', __( 'This does not appear to be a WXR file, missing/invalid WXR version number', 'wordpress-importer' ) );

		return array(
			'authors' => $this->authors,
			'posts' => $this->posts,
			'categories' => $this->categories,
			'tags' => $this->tags,
			'terms' => $this->terms,
			'base_url' => $this->base_url,
			'base_blog_url' => $this->base_blog_url,
			'version' => $wxr_version
		);
	}

	function get_tag( $string, $tag ) {
		preg_match( "|<$tag.*?>(.*?)</$tag>|is", $string, $return );
		if ( isset( $return[1] ) ) {
			if ( substr( $return[1], 0, 9 ) == '<![CDATA[' ) {
				if ( strpos( $return[1], ']]]]><![CDATA[>' ) !== false ) {
					preg_match_all( '|<!\[CDATA\[(.*?)\]\]>|s', $return[1], $matches );
					$return = '';
					foreach( $matches[1] as $match )
						$return .= $match;
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

	function process_category( $c ) {
		$term = array(
			'term_id' => $this->get_tag( $c, 'wp:term_id' ),
			'cat_name' => $this->get_tag( $c, 'wp:cat_name' ),
			'category_nicename'	=> $this->get_tag( $c, 'wp:category_nicename' ),
			'category_parent' => $this->get_tag( $c, 'wp:category_parent' ),
			'category_description' => $this->get_tag( $c, 'wp:category_description' ),
		);

		$term_meta = $this->process_meta( $c, 'wp:termmeta' );
		if ( ! empty( $term_meta ) ) {
			$term['termmeta'] = $term_meta;
		}

		return $term;
	}

	function process_tag( $t ) {
		$term = array(
			'term_id' => $this->get_tag( $t, 'wp:term_id' ),
			'tag_name' => $this->get_tag( $t, 'wp:tag_name' ),
			'tag_slug' => $this->get_tag( $t, 'wp:tag_slug' ),
			'tag_description' => $this->get_tag( $t, 'wp:tag_description' ),
		);

		$term_meta = $this->process_meta( $t, 'wp:termmeta' );
		if ( ! empty( $term_meta ) ) {
			$term['termmeta'] = $term_meta;
		}

		return $term;
	}

	function process_term( $t ) {
		$term = array(
			'term_id' => $this->get_tag( $t, 'wp:term_id' ),
			'term_taxonomy' => $this->get_tag( $t, 'wp:term_taxonomy' ),
			'slug' => $this->get_tag( $t, 'wp:term_slug' ),
			'term_parent' => $this->get_tag( $t, 'wp:term_parent' ),
			'term_name' => $this->get_tag( $t, 'wp:term_name' ),
			'term_description' => $this->get_tag( $t, 'wp:term_description' ),
		);

		$term_meta = $this->process_meta( $t, 'wp:termmeta' );
		if ( ! empty( $term_meta ) ) {
			$term['termmeta'] = $term_meta;
		}

		return $term;
	}

	function process_meta( $string, $tag ) {
		$parsed_meta = array();

		preg_match_all( "|<$tag>(.+?)</$tag>|is", $string, $meta );

		if ( ! isset( $meta[1] ) ) {
			return $parsed_meta;
		}

		foreach ( $meta[1] as $m ) {
			$parsed_meta[] = array(
				'key'   => $this->get_tag( $m, 'wp:meta_key' ),
				'value' => $this->get_tag( $m, 'wp:meta_value' ),
			);
		}

		return $parsed_meta;
	}

	function process_author( $a ) {
		return array(
			'author_id' => $this->get_tag( $a, 'wp:author_id' ),
			'author_login' => $this->get_tag( $a, 'wp:author_login' ),
			'author_email' => $this->get_tag( $a, 'wp:author_email' ),
			'author_display_name' => $this->get_tag( $a, 'wp:author_display_name' ),
			'author_first_name' => $this->get_tag( $a, 'wp:author_first_name' ),
			'author_last_name' => $this->get_tag( $a, 'wp:author_last_name' ),
		);
	}

	function process_post( $post ) {
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
		$post_excerpt = preg_replace_callback( '|<(/?[A-Z]+)|', array( &$this, '_normalize_tag' ), $post_excerpt );
		$post_excerpt = str_replace( '<br>', '<br />', $post_excerpt );
		$post_excerpt = str_replace( '<hr>', '<hr />', $post_excerpt );

		$post_content = $this->get_tag( $post, 'content:encoded' );
		$post_content = preg_replace_callback( '|<(/?[A-Z]+)|', array( &$this, '_normalize_tag' ), $post_content );
		$post_content = str_replace( '<br>', '<br />', $post_content );
		$post_content = str_replace( '<hr>', '<hr />', $post_content );

		$postdata = compact( 'post_id', 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_excerpt',
			'post_title', 'status', 'post_name', 'comment_status', 'ping_status', 'guid', 'post_parent',
			'menu_order', 'post_type', 'post_password', 'is_sticky'
		);

		$attachment_url = $this->get_tag( $post, 'wp:attachment_url' );
		if ( $attachment_url )
			$postdata['attachment_url'] = $attachment_url;

		preg_match_all( '|<category domain="([^"]+?)" nicename="([^"]+?)">(.+?)</category>|is', $post, $terms, PREG_SET_ORDER );
		foreach ( $terms as $t ) {
			$post_terms[] = array(
				'slug' => $t[2],
				'domain' => $t[1],
				'name' => str_replace( array( '<![CDATA[', ']]>' ), '', $t[3] ),
			);
		}
		if ( ! empty( $post_terms ) ) $postdata['terms'] = $post_terms;

		preg_match_all( '|<wp:comment>(.+?)</wp:comment>|is', $post, $comments );
		$comments = $comments[1];
		if ( $comments ) {
			foreach ( $comments as $comment ) {
				$post_comments[] = array(
					'comment_id' => $this->get_tag( $comment, 'wp:comment_id' ),
					'comment_author' => $this->get_tag( $comment, 'wp:comment_author' ),
					'comment_author_email' => $this->get_tag( $comment, 'wp:comment_author_email' ),
					'comment_author_IP' => $this->get_tag( $comment, 'wp:comment_author_IP' ),
					'comment_author_url' => $this->get_tag( $comment, 'wp:comment_author_url' ),
					'comment_date' => $this->get_tag( $comment, 'wp:comment_date' ),
					'comment_date_gmt' => $this->get_tag( $comment, 'wp:comment_date_gmt' ),
					'comment_content' => $this->get_tag( $comment, 'wp:comment_content' ),
					'comment_approved' => $this->get_tag( $comment, 'wp:comment_approved' ),
					'comment_type' => $this->get_tag( $comment, 'wp:comment_type' ),
					'comment_parent' => $this->get_tag( $comment, 'wp:comment_parent' ),
					'comment_user_id' => $this->get_tag( $comment, 'wp:comment_user_id' ),
					'commentmeta' => $this->process_meta( $comment, 'wp:commentmeta' ),
				);
			}
		}
		if ( ! empty( $post_comments ) ) {
			$postdata['comments'] = $post_comments;
		}

		$post_meta = $this->process_meta( $post, 'wp:postmeta' );
		if ( ! empty( $post_meta ) ) {
			$postdata['postmeta'] = $post_meta;
		}

		return $postdata;
	}

	function _normalize_tag( $matches ) {
		return '<' . strtolower( $matches[1] );
	}

	function fopen( $filename, $mode = 'r' ) {
		if ( $this->has_gzip )
			return gzopen( $filename, $mode );
		return fopen( $filename, $mode );
	}

	function feof( $fp ) {
		if ( $this->has_gzip )
			return gzeof( $fp );
		return feof( $fp );
	}

	function fgets( $fp, $len = 8192 ) {
		if ( $this->has_gzip )
			return gzgets( $fp, $len );
		return fgets( $fp, $len );
	}

	function fclose( $fp ) {
		if ( $this->has_gzip )
			return gzclose( $fp );
		return fclose( $fp );
	}
}
