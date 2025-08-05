<?php if ( ! defined('ZOTA_THEME_DIR')) exit('No direct script access allowed');

if ( ! function_exists( 'zota_tbay_body_classes' ) ) {
	function zota_tbay_body_classes( $classes ) {
		global $post;
		if ( is_page() && is_object($post) ) {
			$class = get_post_meta( $post->ID, 'tbay_page_extra_class', true );
			if ( !empty($class) ) {
				$classes[] = trim($class);
			}
		}
		if ( zota_tbay_get_config('preload') ) {
			$classes[] = 'tbay-body-loader';
		}		

		if ( zota_tbay_is_home_page() ) {
			$classes[] = 'tbay-homepage-demo';
		}
		  
		if( !defined('TBAY_ELEMENTOR_ACTIVED') ) {
			$classes[] = 'tbay-body-default';
		}

		$enable_search 	= zota_tbay_get_config('enable_menu_mobile_search', true);
		$menu_mobile_search 	= zota_tbay_get_config('all_page_menu_mobile_search', false);
		if ($enable_search) { 
			if( $menu_mobile_search || zota_tbay_is_home_page()) {
				$classes[] = 'tbay-search-mb';
			}
		}

		if( zota_checkout_optimized() ) {
            $classes[] = 'tbay-checkout-optimized';
        }

		$classes[] = 'skin-'.zota_tbay_get_theme();
		   

		return $classes;
	}
	add_filter( 'body_class', 'zota_tbay_body_classes' );
}


if ( ! function_exists( 'zota_tbay_body_home_classes' ) ) {
	function zota_tbay_body_home_classes( $classes ) {
		global $post;
		if ( is_page() && is_object($post) ) {
			$slug = get_queried_object()->post_name;
			if ( !empty($slug) ) {
				$classes[] = trim($slug);
			}
		} 

		if( is_front_page() ) {
			$class = 'tbay-home';
			if ( !empty($class) ) {
				$classes[] = trim($class);
			}
		}

		return $classes;
	}
	add_filter( 'body_class', 'zota_tbay_body_home_classes' );
}

if ( ! function_exists( 'zota_tbay_get_shortcode_regex' ) ) {
	function zota_tbay_get_shortcode_regex( $tagregexp = '' ) {
		// WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
		// Also, see shortcode_unautop() and shortcode.js.
		return
			'\\['                                // Opening bracket
			. '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
			. "($tagregexp)"                     // 2: Shortcode name
			. '(?![\\w-])'                       // Not followed by word character or hyphen
			. '('                                // 3: Unroll the loop: Inside the opening shortcode tag
			. '[^\\]\\/]*'                   // Not a closing bracket or forward slash
			. '(?:'
			. '\\/(?!\\])'               // A forward slash not followed by a closing bracket
			. '[^\\]\\/]*'               // Not a closing bracket or forward slash
			. ')*?'
			. ')'
			. '(?:'
			. '(\\/)'                        // 4: Self closing tag ...
			. '\\]'                          // ... and closing bracket
			. '|'
			. '\\]'                          // Closing bracket
			. '(?:'
			. '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
			. '[^\\[]*+'             // Not an opening bracket
			. '(?:'
			. '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
			. '[^\\[]*+'         // Not an opening bracket
			. ')*+'
			. ')'
			. '\\[\\/\\2\\]'             // Closing shortcode tag
			. ')?'
			. ')'
			. '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
	}
}

if ( ! function_exists( 'zota_tbay_tagregexp' ) ) {
	function zota_tbay_tagregexp() {
		return apply_filters( 'zota_tbay_custom_tagregexp', 'video|audio|playlist|video-playlist|embed|zota_tbay_media' );
	}
}


if( ! function_exists( 'zota_tbay_text_line')) {
	function zota_tbay_text_line( $str ) {
		return trim(preg_replace("/('|\"|\r?\n)/", '', $str)); 
	}
}

if ( !function_exists('zota_tbay_get_themes') ) {
	function zota_tbay_get_themes() {
		$themes = array();

		$themes['electronics'] = array(
			'title' => esc_html__( 'Electronics', 'zota' ),
			'img'   => ZOTA_ASSETS_IMAGES . '/active_theme/electronics.jpg'
		);

		$themes['beauty'] = array(
			'title' => esc_html__( 'Beauty & Cosmetic', 'zota' ),
			'img'   => ZOTA_ASSETS_IMAGES . '/active_theme/beauty.jpg'
		);

		$themes['fashion'] = array(
			'title' => esc_html__( 'Fashion', 'zota' ),
			'img'   => ZOTA_ASSETS_IMAGES . '/active_theme/fashion.jpg'
		);

		$themes['organic'] = array(
			'title' => esc_html__( 'Organic', 'zota' ),
			'img'   => ZOTA_ASSETS_IMAGES . '/active_theme/organic.jpg'
		);
		$themes['furniture'] = array(
			'title' => esc_html__( 'Furniture', 'zota' ),
			'img'   => ZOTA_ASSETS_IMAGES . '/active_theme/furniture.jpg'
		);

		$themes['hand-made'] = array(
			'title' => esc_html__( 'Hand Made', 'zota' ),
			'img'   => ZOTA_ASSETS_IMAGES . '/active_theme/hand-made.jpg'
		);

		$themes['auto-part'] = array(
			'title' => esc_html__( 'Auto Part', 'zota' ),
			'img'   => ZOTA_ASSETS_IMAGES . '/active_theme/auto-part.jpg'
		);

		$themes['bag'] = array(
			'title' => esc_html__( 'Bag', 'zota' ),
			'img'   => ZOTA_ASSETS_IMAGES . '/active_theme/bag.jpg'
		);
		
		return $themes;

	}
}

if ( !function_exists('zota_tbay_get_theme') ) {
	function zota_tbay_get_theme() {
		$kin_default = 'electronics';

		if( !empty($_GET['skin']) ) return $_GET['skin'];

		if( !empty(zota_tbay_get_global_config('active_theme',$kin_default)) ) {
		   return zota_tbay_get_global_config('active_theme',$kin_default);
		} else {
		   return $kin_default;
		}
	}
}

/**
 * Retrieves an array of header layouts for the theme.
 *
 * @return array Array of header layouts with slug as key and title as value.
 */
if ( ! function_exists( 'zota_tbay_get_header_layouts' ) ) {
	function zota_tbay_get_header_layouts() {
		// Generate a unique cache key.
		$cache_key = 'zota_header_layouts';

		// Attempt to retrieve cached header layouts.
		$headers = get_transient( $cache_key );

		if ( false === $headers ) {
			// Initialize default header.
			$headers = array(
				'header_default' => esc_html__( 'Default', 'zota' ),
			);

			// Define query arguments for WP_Query.
			$query_args = array(
				'posts_per_page' => apply_filters( 'zota_tbay_get_header_layouts_posts_per_page', 20 ),
				'offset'         => 0,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'post_type'      => 'tbay_header',
				'post_status'    => 'publish',
				'no_found_rows'  => true,
				'fields'         => 'ids', // Retrieve only IDs for efficiency.
			);

			// Allow filtering of query arguments.
			$query_args = apply_filters( 'zota_tbay_get_header_layouts_query_args', $query_args );

			// Execute the query.
			$query = new WP_Query( $query_args );

			if ( $query->have_posts() ) {
				foreach ( $query->posts as $post_id ) {
					$post_name  = sanitize_key( get_post_field( 'post_name', $post_id ) );
					$post_title = sanitize_text_field( get_the_title( $post_id ) );

					if ( ! empty( $post_name ) && ! empty( $post_title ) ) {
						$headers[ $post_name ] = $post_title;
					}
				}
			}

			// Cache the result for one day.
			set_transient( $cache_key, $headers, DAY_IN_SECONDS );
		}

		// Allow filtering of the final headers array.
		return apply_filters( 'zota_tbay_get_header_layouts', $headers );
	}
}

if ( !function_exists('zota_tbay_get_header_layout') ) {
	function zota_tbay_get_header_layout() {
		if ( is_page() ) {
			global $post; 
			$header = '';
			if ( is_object($post) && isset($post->ID) ) {
				$header = get_post_meta( $post->ID, 'tbay_page_header_type', true );
				if ( $header == 'global' ||  $header == '') {
					return zota_tbay_get_config('header_type', 'header_default');
				}
			}
			return $header;
		} else if( class_exists( 'WooCommerce' ) && is_shop() ) {
			return zota_tbay_woo_get_header_layout( wc_get_page_id( 'shop' ) );
		} else if( class_exists( 'WooCommerce' ) && is_cart() ) {
			return zota_tbay_woo_get_header_layout( wc_get_page_id( 'cart' ) );
		} else if( class_exists( 'WooCommerce' ) && is_checkout() ) {
			return zota_tbay_woo_get_header_layout( wc_get_page_id( 'checkout' ) );
		}

		return zota_tbay_get_config('header_type', 'header_default');
	}
	add_filter('zota_tbay_get_header_layout', 'zota_tbay_get_header_layout');
}

if ( !function_exists('zota_tbay_woo_get_header_layout') ) {
	function zota_tbay_woo_get_header_layout( $page_id ) {
		$header = get_post_meta( $page_id, 'tbay_page_header_type', true );

		if ( $header == 'global' ||  $header == '') {
			return zota_tbay_get_config('header_type', 'header_default');
		} else {
			return $header;
		}
	}
}

if (!function_exists('zota_tbay_get_footer_layouts')) {
    function zota_tbay_get_footer_layouts() {
        $footers = get_transient('zota_footer_layouts');
        if (false === $footers) {
            $footers = array('footer_default' => esc_html__('Default', 'zota'));

            $args = array(
                'posts_per_page'   => apply_filters('zota_tbay_get_footer_layouts_posts_per_page', 20),
                'offset'           => 0,
                'orderby'          => 'date',
                'order'            => 'DESC',
                'post_type'        => 'tbay_footer',
                'post_status'      => 'publish',
                'suppress_filters' => true,
                'no_found_rows'    => true, 
            );

            $posts = get_posts($args);

            foreach ($posts as $post) {
                $footers[$post->post_name] = $post->post_title;
            }

            set_transient('zota_footer_layouts', $footers, 24 * HOUR_IN_SECONDS);
        }

        return $footers;
    }
}

if ( !function_exists('zota_tbay_get_footer_layout') ) {
	function zota_tbay_get_footer_layout() {
		if ( is_page() ) {
			global $post;
			$footer = '';
			if ( is_object($post) && isset($post->ID) ) {
				$footer = get_post_meta( $post->ID, 'tbay_page_footer_type', true );
				if ( $footer == 'global' ||  $footer == '') {
					return zota_tbay_get_config('footer_type', 'footer_default');
				}
			}
			return $footer;
		} else if( class_exists( 'WooCommerce' ) && is_shop() ) {
			return zota_tbay_woo_get_footer_layout( wc_get_page_id( 'shop' ) );
		} else if( class_exists( 'WooCommerce' ) && is_cart() ) {
			return zota_tbay_woo_get_footer_layout( wc_get_page_id( 'cart' ) );
		} else if( class_exists( 'WooCommerce' ) && is_checkout() ) {
			return zota_tbay_woo_get_footer_layout( wc_get_page_id( 'checkout' ) );
		}

		return zota_tbay_get_config('footer_type', 'footer_default');
	}
	add_filter('zota_tbay_get_footer_layout', 'zota_tbay_get_footer_layout');
}

if ( !function_exists('zota_tbay_woo_get_footer_layout') ) {
	function zota_tbay_woo_get_footer_layout( $page_id ) {
		$footer = get_post_meta( $page_id, 'tbay_page_footer_type', true );

		if ( $footer == 'global' ||  $footer == '') {
			return zota_tbay_get_config('footer_type', 'footer_default');
		} else {
			return $footer;
		}
	}
}

if ( !function_exists('zota_tbay_blog_content_class') ) {
	function zota_tbay_blog_content_class( $class ) {
		$page = 'archive';
		if ( is_singular( 'post' ) ) {
            $page = 'single';
        }
		if ( zota_tbay_get_config('blog_'.$page.'_fullwidth') ) {
			return 'container-fluid';
		}
		return $class;
	}
}
add_filter( 'zota_tbay_blog_content_class', 'zota_tbay_blog_content_class', 1 , 1  );

// layout class for woo page
if ( !function_exists('zota_tbay_post_content_class') ) {
    function zota_tbay_post_content_class( $class ) {
        $page = 'archive';
        if ( is_singular( 'post' ) ) {
            $page = 'single';

            if( !isset($_GET['blog_'.$page.'_layout']) ) {
                $class .= ' '.zota_tbay_get_config('blog_'.$page.'_layout');
            }  else {
                $class .= ' '.$_GET['blog_'.$page.'_layout'];
            }

        } else {

            if( !isset($_GET['blog_'.$page.'_layout']) ) {
                $class .= ' '.zota_tbay_get_config('blog_'.$page.'_layout');
            }  else {
                $class .= ' '.$_GET['blog_'.$page.'_layout'];
            }

        }
        return $class;
    }
}
add_filter( 'zota_tbay_post_content_class', 'zota_tbay_post_content_class' );


if ( !function_exists('zota_tbay_get_page_layout_configs') ) {
	function zota_tbay_get_page_layout_configs() {
		global $post;
		if( isset($post->ID) ) {
			$left = get_post_meta( $post->ID, 'tbay_page_left_sidebar', true );
			$right = get_post_meta( $post->ID, 'tbay_page_right_sidebar', true );

			switch ( get_post_meta( $post->ID, 'tbay_page_layout', true ) ) {
				case 'left-main':
					$configs['sidebar'] = array( 'id' => $left, 'class' => 'col-12 col-lg-3'  );
					$configs['main'] 	= array( 'class' => 'col-12 col-lg-9' );
					break;
				case 'main-right':
					$configs['sidebar'] = array( 'id' => $right,  'class' => 'col-12 col-lg-3' ); 
					$configs['main'] 	= array( 'class' => 'col-12 col-lg-9' );
					break;
				case 'main':
					$configs['main'] = array( 'class' => 'col-12' );
					break;
				default:
					$configs['main'] = array( 'class' => 'col-12' );
					break;
			}

			return $configs; 
		}
	}
}

if ( ! function_exists( 'zota_tbay_get_first_url_from_string' ) ) {
	function zota_tbay_get_first_url_from_string( $string ) {
		$pattern = "/^\b(?:(?:https?|ftp):\/\/)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i";
		preg_match( $pattern, $string, $link );

		return ( ! empty( $link[0] ) ) ? $link[0] : false;
	}
}

/*Check in home page*/
if ( !function_exists('zota_tbay_is_home_page') ) {
	function zota_tbay_is_home_page() {
		$is_home = false;

		if( is_home() || is_front_page() || is_page( 'home-1' ) || is_page( 'home-2' ) || is_page( 'home-3' ) || is_page( 'home-4' ) || is_page( 'home-5' ) || is_page( 'home-6' ) || is_page( 'home-7' )) {
			$is_home = true;
		}

		return $is_home;
	}
}

if ( !function_exists( 'zota_tbay_get_link_attributes' ) ) {
	function zota_tbay_get_link_attributes( $string ) {
		preg_match( '/<a href="(.*?)">/i', $string, $atts );

		return ( ! empty( $atts[1] ) ) ? $atts[1] : '';
	}
}

if ( !function_exists( 'zota_tbay_post_media' ) ) {
	function zota_tbay_post_media( $content ) {
		$is_video = ( get_post_format() == 'video' ) ? true : false;
		$media = zota_tbay_get_first_url_from_string( $content );
		if ( ! empty( $media ) ) {
			global $wp_embed;
			$content = do_shortcode( $wp_embed->run_shortcode( '[embed]' . $media . '[/embed]' ) );
		} else {
			$pattern = zota_tbay_get_shortcode_regex( zota_tbay_tagregexp() );
			preg_match( '/' . $pattern . '/s', $content, $media );
			if ( ! empty( $media[2] ) ) {
				if ( $media[2] == 'embed' ) {
					global $wp_embed;
					$content = do_shortcode( $wp_embed->run_shortcode( $media[0] ) );
				} else {
					$content = do_shortcode( $media[0] );
				}
			}
		}
		if ( ! empty( $media ) ) {
			$output = '<div class="entry-media">';
			$output .= ( $is_video ) ? '<div class="pro-fluid"><div class="pro-fluid-inner">' : '';
			$output .= $content;
			$output .= ( $is_video ) ? '</div></div>' : '';
			$output .= '</div>';

			return $output;
		}

		return false;
	}
}

if ( !function_exists( 'zota_tbay_post_gallery' ) ) {
	function zota_tbay_post_gallery( $content ) {
		$pattern = zota_tbay_get_shortcode_regex( 'gallery' );
		preg_match( '/' . $pattern . '/s', $content, $media );
		if ( ! empty( $media[2] )  ) {
			return '<div class="entry-gallery">' . do_shortcode( $media[0] ) . '<hr class="pro-clear" /></div>';
		}

		return false;
	}
}

if ( !function_exists( 'zota_tbay_random_key' ) ) {
    function zota_tbay_random_key($length = 5) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $return = '';
        for ($i = 0; $i < $length; $i++) {
            $return .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $return;
    }
}

if ( !function_exists('zota_tbay_substring') ) {
    function zota_tbay_substring($string, $limit, $afterlimit = '[...]') {
        if ( empty($string) ) {
        	return $string;
        }
       	$string = explode(' ', strip_tags( $string ), $limit);

        if (count($string) >= $limit) {
            array_pop($string);
            $string = implode(" ", $string) .' '. $afterlimit;
        } else {
            $string = implode(" ", $string);
        }
        $string = preg_replace('`[[^]]*]`','',$string);
        return strip_shortcodes( $string );
    }
}

if ( !function_exists('zota_tbay_subschars') ) {
    function zota_tbay_subschars($string, $limit, $afterlimit='...'){

	    if(strlen($string) > $limit){
	        $string = substr($string, 0, $limit);
	    }else{
	        $afterlimit = '';
	    }
	    return $string . $afterlimit;
	}
}


/*Zota get template parts*/
if ( !function_exists('zota_tbay_get_page_templates_parts') ) {
	function zota_tbay_get_page_templates_parts($slug = 'logo', $name = null) {
		return get_template_part( 'page-templates/parts/'.$slug.'',$name);
	}
}

/*testimonials*/
if (!function_exists('zota_tbay_get_testimonials_layouts')) {
    function zota_tbay_get_testimonials_layouts() {
        $testimonials = get_transient('zota_testimonial_layouts');
        if (false === $testimonials) {
            $testimonials = array();
            $files = glob(get_template_directory() . '/vc_templates/testimonial/testimonial.php');
            if (is_array($files)) {
                foreach ($files as $file) {
                    if (file_exists($file)) {
                        $testi = str_replace("testimonial", '', str_replace('.php', '', basename($file)));
                        $testimonials[$testi] = $testi;
                    }
                }
            }
            set_transient('zota_testimonial_layouts', $testimonials, 24 * HOUR_IN_SECONDS);
        }
        return $testimonials;
    }
}

/*Blog*/
if ( !function_exists('zota_tbay_get_blog_layouts') ) {
	function zota_tbay_get_blog_layouts() {
		$blogs = array(
			esc_html__('Grid', 'zota') => 'grid',
			esc_html__('Vertical', 'zota') => 'vertical',
		);
		$files = glob( get_template_directory() . '/vc_templates/post/carousel/_single_*.php' );
	    if ( !empty( $files ) ) {
	        foreach ( $files as $file ) {
	        	$str = str_replace( "_single_", '', str_replace( '.php', '', basename($file) ) );
	            $blogs[$str] = $str;
	        }
	    }

		return $blogs;
	}
}

// Number of blog per row
if ( !function_exists('zota_tbay_blog_loop_columns') ) {
    function zota_tbay_blog_loop_columns($number) {

    		$sidebar_configs = zota_tbay_get_blog_layout_configs();

    		$columns 	= zota_tbay_get_config('blog_columns');

        if( isset($_GET['blog_columns']) && is_numeric($_GET['blog_columns']) ) {
            $value = $_GET['blog_columns']; 
        } elseif( empty($columns) && isset($sidebar_configs['columns']) ) {
    			$value = 	$sidebar_configs['columns']; 
    		} else {
          	$value = $columns;          
        }

        if ( in_array( $value, array(1, 2, 3, 4, 5, 6) ) ) {
            $number = $value;
        }
        return $number;
    }
}
add_filter( 'loop_blog_columns', 'zota_tbay_blog_loop_columns' );

/*Check style blog image full*/
if ( !function_exists( 'zota_tbay_blog_image_sizes_full' ) ) {
    function zota_tbay_blog_image_sizes_full() {
    	$style = false;
    	$sidebar_configs = zota_tbay_get_blog_layout_configs();

       	if ( !is_singular( 'post' ) ) {
       		if( isset($sidebar_configs['image_sizes']) && $sidebar_configs['image_sizes'] == 'full') :
       			$style = true;
       		endif;
        }

        return  $style;

    }
}


// Number of post per page
if ( !function_exists('zota_tbay_loop_post_per_page') ) {
    function zota_tbay_loop_post_per_page($number) {

        if( isset($_GET['posts_per_page']) && is_numeric($_GET['posts_per_page']) ) {
            $value = $_GET['posts_per_page']; 
        } else {
            $value = get_option( 'posts_per_page' );       
        }

        if ( is_numeric( $value ) && $value ) {
            $number = absint( $value );
        }
        
        return $number;
    }
  add_filter( 'loop_post_per_page', 'zota_tbay_loop_post_per_page' );
}

if ( !function_exists('zota_tbay_posts_per_page') ) {
	function zota_tbay_posts_per_page( $wp_query ){

			if ( is_admin() || ! $wp_query->is_main_query() )
	        return;

			$value = apply_filters( 'loop_post_per_page', 6 );

		 	if( isset($value) && is_category() )
		    $wp_query->query_vars['posts_per_page'] = $value;
		 	return $wp_query;
	}
	add_action( 'pre_get_posts', 'zota_tbay_posts_per_page' );
}

if ( !function_exists('zota_tbay_share_js') ) {
	function zota_tbay_share_js() {
		  if( !zota_tbay_get_config('enable_code_share',false) || zota_tbay_get_config('select_share_type') == 'custom' ) return;
		 if ( is_single() ) {
		 	echo zota_tbay_get_config('code_share');
		 }
	}
	add_action('wp_head', 'zota_tbay_share_js');
}


/*Post Views*/
if ( !function_exists('zota_set_post_views') ) {
	function zota_set_post_views($postID) {
	    $count_key = 'zota_post_views_count';
	    $count 		 = get_post_meta($postID, $count_key, true);
	    if( $count == '' ){
	        $count = 1;
	        delete_post_meta($postID, $count_key);
	        add_post_meta($postID, $count_key, '1');
	    }else{
	        $count++;
	        update_post_meta($postID, $count_key, $count);
	    }
	}
}
//To keep the count accurate, lets get rid of prefetching
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

if ( !function_exists('zota_track_post_views') ) {
	function zota_track_post_views ($post_id) {
	    if ( !is_single() ) return;
	    if ( empty ( $post_id) ) {
	        global $post;
	        $post_id = $post->ID;    
	    }
	    zota_set_post_views($post_id);
	}
	add_action( 'wp_head', 'zota_track_post_views');
}

if ( !function_exists('zota_get_post_views') ) {
	function zota_get_post_views($postID, $text = ''){
	    $count_key = 'zota_post_views_count';
	    $count = get_post_meta($postID, $count_key, true);

	    if( $count == '' ){
	        delete_post_meta($postID, $count_key);
	        add_post_meta($postID, $count_key, '0');
	        return "0";
	    }
	    return $count.$text;
	}
}

/*Get Preloader*/
if ( ! function_exists( 'zota_get_select_preloader' ) ) {
	add_action( 'wp_body_open', 'zota_get_select_preloader', 10 );
    function zota_get_select_preloader( ) {
 
 		$enable_preload = zota_tbay_get_global_config('preload',false);

    	if( !$enable_preload ) return;

    	$preloader 	= zota_tbay_get_global_config('select_preloader', 'loader1');
    	$media 		= zota_tbay_get_global_config('media-preloader');
    	
    	if( isset($preloader) ) {
	    	switch ($preloader) {
	    		case 'loader1': 
	    			?>
	                <div class="tbay-page-loader">
					  	<div id="loader"></div>
					  	<div class="loader-section section-left"></div>
					  	<div class="loader-section section-right"></div>
					</div>
	    			<?php
	    			break;    		

	    		case 'loader2':
	    			?>
					<div class="tbay-page-loader">
					    <div class="tbay-loader tbay-loader-two">
					    	<span></span>
					    	<span></span>
					    	<span></span>
					    	<span></span>
					    </div>
					</div>
	    			<?php
	    			break;    		
	    		case 'loader3':
	    			?>
					<div class="tbay-page-loader">
					    <div class="tbay-loader tbay-loader-three">
					    	<span></span>
					    	<span></span>
					    	<span></span>
					    	<span></span>
					    	<span></span>
					    </div>
					</div>
	    			<?php
	    			break;    		
	    		case 'loader4':
	    			?>
					<div class="tbay-page-loader">
					    <div class="tbay-loader tbay-loader-four"> <span class="spinner-cube spinner-cube1"></span> <span class="spinner-cube spinner-cube2"></span> <span class="spinner-cube spinner-cube3"></span> <span class="spinner-cube spinner-cube4"></span> <span class="spinner-cube spinner-cube5"></span> <span class="spinner-cube spinner-cube6"></span> <span class="spinner-cube spinner-cube7"></span> <span class="spinner-cube spinner-cube8"></span> <span class="spinner-cube spinner-cube9"></span> </div>
					</div>
	    			<?php
	    			break;    		
	    		case 'loader5':
	    			?>
					<div class="tbay-page-loader">
					    <div class="tbay-loader tbay-loader-five"> <span class="spinner-cube-1 spinner-cube"></span> <span class="spinner-cube-2 spinner-cube"></span> <span class="spinner-cube-4 spinner-cube"></span> <span class="spinner-cube-3 spinner-cube"></span> </div>
					</div>
	    			<?php
	    			break;    		
	    		case 'loader6':
	    			?>
					<div class="tbay-page-loader">
					    <div class="tbay-loader tbay-loader-six"> <span class=" spinner-cube-1 spinner-cube"></span> <span class=" spinner-cube-2 spinner-cube"></span> </div>
					</div>
	    			<?php
	    			break;

	    		case 'custom_image':
	    			?>
					<div class="tbay-page-loader loader-img">
						<?php if( isset($media['url']) && !empty($media['url']) ): ?>
					   		<img alt="<?php echo ( !empty($media['alt']) ) ? esc_attr( $media['alt'] ) : ''; ?>" src="<?php echo esc_url($media['url']); ?>">
						<?php endif; ?>
					</div>
	    			<?php
	    			break;
	    			
	    		default:
	    			?>
	    			<div class="tbay-page-loader">
					  	<div id="loader"></div>
					  	<div class="loader-section section-left"></div>
					  	<div class="loader-section section-right"></div>
					</div>
	    			<?php
	    			break;
	    	}
	    }
     	
    }
}

if ( !function_exists('zota_gallery_atts') ) {

	add_filter( 'shortcode_atts_gallery', 'zota_gallery_atts', 10, 3 );
	
	/* Change attributes of wp gallery to modify image sizes for your needs */
	function zota_gallery_atts( $output, $pairs, $atts ) {

			
		if ( isset($atts['columns']) && $atts['columns'] == 1 ) {
			//if gallery has one column, use large size
			$output['size'] = 'full';
		} else if ( isset($atts['columns']) && $atts['columns'] >= 2 && $atts['columns'] <= 4 ) {
			//if gallery has between two and four columns, use medium size
			$output['size'] = 'full';
		} else {
			//if gallery has more than four columns, use thumbnail size
			$output['size'] = 'full';
		}
	
		return $output;
	
	}
}

if ( !function_exists('zota_get_custom_menu') ) {

	
	/* Change attributes of wp gallery to modify image sizes for your needs */
	function zota_get_custom_menu( $menu_id ) {

		$_id = zota_tbay_random_key();

        $args = array(
            'menu'              => $menu_id,
            'container_class'   => 'nav',
            'menu_class'        => 'menu',
            'fallback_cb'       => '',
            'before'            => '',
            'after'             => '',
            'echo'              => true,
            'menu_id'           => 'menu-'.$menu_id.'-'.$_id
        );

        $output = wp_nav_menu($args);

	
		return $output;
	
	}
}

/*Set excerpt show enable default*/
if ( ! function_exists( 'zota_tbay_edit_post_show_excerpt' ) ) {
	function zota_tbay_edit_post_show_excerpt() {
	  $user = wp_get_current_user();
	  $unchecked = get_user_meta( $user->ID, 'metaboxhidden_post', true );
	  if( is_array($unchecked) ) {
		$key = array_search( 'postexcerpt', $unchecked );
		if ( FALSE !== $key ) {
		   array_splice( $unchecked, $key, 1 );
		   update_user_meta( $user->ID, 'metaboxhidden_post', $unchecked );
		}
	  }
	}
	add_action( 'admin_init', 'zota_tbay_edit_post_show_excerpt', 10 );
}

if( ! function_exists( 'zota_texttrim')) {
	function zota_texttrim( $str ) {
		return trim(preg_replace("/('|\"|\r?\n)/", '', $str)); 
	}
}

/*Get query*/
if ( !function_exists('zota_tbay_get_boolean_query_var') ) {
    function zota_tbay_get_boolean_query_var($config) {
        $active = zota_tbay_get_config($config,true);

        $active = (isset($_GET[$config])) ? $_GET[$config] : $active;

        return (boolean)$active;
    }
}

if ( !function_exists('zota_tbay_archive_blog_size_image') ) {
    function zota_tbay_archive_blog_size_image() {
        $blog_size = zota_tbay_get_config('blog_image_sizes', 'medium');

        $blog_size = (isset($_GET['blog_image_sizes'])) ? $_GET['blog_image_sizes'] : $blog_size;

        return $blog_size;
    }
}
add_filter( 'zota_archive_blog_size_image', 'zota_tbay_archive_blog_size_image' );


if ( !function_exists('zota_tbay_archive_layout_blog') ) {
    function zota_tbay_archive_layout_blog() {
		$layout_blog = zota_tbay_get_config('layout_blog', 'post-style-1');

        $layout_blog = (isset($_GET['layout_blog'])) ? $_GET['layout_blog'] : $layout_blog;

		return $layout_blog;
		
    }
}
add_filter( 'zota_archive_layout_blog', 'zota_tbay_archive_layout_blog' );

if ( !function_exists('zota_tbay_categories_blog_type') ) {
    function zota_tbay_categories_blog_type() {
        $type = zota_tbay_get_config('categories_type', 'type-1');

        $type = (isset($_GET['categories_type'])) ? $_GET['categories_type'] : $type;

        return $type;
    }
}

// cart Postion
if ( !function_exists('zota_tbay_header_mobile_position') ) {
    function zota_tbay_header_mobile_position() {
       
		$position = zota_tbay_get_config('header_mobile', 'v1');

        $position = ( isset($_GET['header_mobile']) ) ? $_GET['header_mobile'] : $position;

        return $position;

    }
    add_filter( 'zota_header_mobile_position', 'zota_tbay_header_mobile_position' ); 
}

if ( !function_exists('zota_tbay_offcanvas_smart_menu') ) {
    function zota_tbay_offcanvas_smart_menu() {
		zota_tbay_get_page_templates_parts('device/offcanvas-smartmenu');
	}
	add_action('zota_before_theme_header', 'zota_tbay_offcanvas_smart_menu', 10);
}

if ( !function_exists('zota_tbay_the_topbar_mobile') ) {
    function zota_tbay_the_topbar_mobile() {  
		if( !zota_tbay_get_config('mobile_header', true) ) return;

        $position = apply_filters( 'zota_header_mobile_position', 10,2 ); 

        zota_tbay_get_page_templates_parts('device/topbar-mobile', $position);

	}
	add_action('zota_before_theme_header', 'zota_tbay_the_topbar_mobile', 20);
}

if ( !function_exists('zota_tbay_footer_mobile') ) {
    function zota_tbay_footer_mobile() {
		if( zota_active_mobile_footer_icon() ) {
			zota_tbay_get_page_templates_parts('device/footer-mobile');
		}
	}
	add_action('zota_before_theme_header', 'zota_tbay_footer_mobile', 40);
}


if ( !function_exists( 'zota_tbay_autocomplete_suggestions' ) ) {
	add_action( 'wp_ajax_zota_autocomplete_search', 'zota_tbay_autocomplete_suggestions' );
	add_action( 'wp_ajax_nopriv_zota_autocomplete_search', 'zota_tbay_autocomplete_suggestions' );
    function zota_tbay_autocomplete_suggestions() {  
		$number = !empty($_REQUEST['number']) ? (int)$_REQUEST['number'] : 10;

		$args = array( 
			'post_status'         => 'publish',
			'orderby'         	  => 'relevance',
			'posts_per_page'      => $number,
			'ignore_sticky_posts' => 1,
			'suppress_filters'    => false,
		);

		if( ! empty( $_REQUEST['query'] ) ) {
			$search_keyword = $_REQUEST['query'];
			$args['s'] = sanitize_text_field( $search_keyword );
		}		

		if( ! empty( $_REQUEST['post_type'] ) ) {
			$post_type = strip_tags( $_REQUEST['post_type'] );
		}		

		if( isset($_REQUEST['post_type']) && $_REQUEST['post_type'] !== 'post' && class_exists( 'WooCommerce' ) ) {
			$args['meta_query'] = WC()->query->get_meta_query();
			$args['tax_query'] 	= WC()->query->get_tax_query();
		} 


		if ( isset($_REQUEST['post_type']) && $_REQUEST['post_type'] != 'all') {
        	$args['post_type'] = $_REQUEST['post_type'];
        } 

		if ( isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == 'product') {
			if ( apply_filters( 'zota_search_query_in', zota_tbay_get_global_config('search_query_in', 'title') === 'all' ) ) {
                add_filter( 'posts_search', 'zota_product_ajax_search_sku', 9 );
            } else {
                add_filter('posts_search', 'zota_product_search_title', 20, 2);
            }
        } 

		if ( isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == 'product' && zota_is_Woocommerce_activated() ) {

			$product_visibility_term_ids = wc_get_product_visibility_term_ids();
			$args['tax_query']['relation'] = 'AND';

			$args['tax_query'][] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => $product_visibility_term_ids['exclude-from-search'],
				'operator' => 'NOT IN',
			); 
			
            if ( ! empty( $_REQUEST['product_cat'] ) ) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => strip_tags( $_REQUEST['product_cat'] ),
                );
            }
		}


		$results = new WP_Query( $args );
 
        $suggestions = array();

        $count = $results->found_posts;

		$view_all = ( ($count - $number ) > 0 ) ? true : false;
        $index = 0;
        if( $results->have_posts() ) {

        	if( $post_type == 'product' ) {
				$factory = new WC_Product_Factory(); 
			}


	        while( $results->have_posts() ) {
	        	if( $index == $number ) {
					break;
				}

				$results->the_post();

				if( $count == 1 ) {
					$result_text = esc_html__('result found with', 'zota');
				} else {
					$result_text = esc_html__('results found with', 'zota');
				}

				if( $post_type == 'product' ) {
					$product = $factory->get_product( get_the_ID() );
					$suggestions[] = array(
						'value' => get_the_title(),
						'link' => get_the_permalink(),
						'price' => $product->get_price_html(),
						'sku' => ( zota_tbay_get_config('search_query_in', 'title') === 'all' && zota_tbay_get_config('search_sku_ajax', false) && $product->get_sku() ) ? esc_html__( 'SKU:', 'zota' ) . ' ' . $product->get_sku() : '',
						'image' => $product->get_image(),
						'result' => '<span class="count">'.$count.' </span> '. $result_text .' <span class="keywork">"'. esc_html( $search_keyword ).'"</span>',
						'view_all' => $view_all,
					);
				} else {
					$suggestions[] = array(
						'value' => get_the_title(),
						'link' => get_the_permalink(),
						'image' => get_the_post_thumbnail( get_the_ID(), 'medium', '' ),
						'result' => '<span class="count">'.$count.' </span> '. $result_text .' <span class="keywork">"'. esc_html( $search_keyword ).'"</span>',
						'view_all' => $view_all,
					);
				}

				$index++;

	        }

	        wp_reset_postdata();
	    } else {
	    	$suggestions[] = array(
				'value' => ( $post_type == 'product' ) ? esc_html__( 'No products found.', 'zota' ) : esc_html__( 'No posts...', 'zota' ),
				'no_found' => true,
				'link' => '',
				'view_all' => $view_all,
			);
	    }

		echo json_encode( array(
			'suggestions' => $suggestions
		) );

		die();
    }
}

if ( !function_exists( 'zota_add_cssclass' ) ) {
	function zota_add_cssclass($add, $class) {
	    $class = empty($class) ? $add : $class .= ' ' . $add;
	    return $class;
	}
}



/*Fix woocomce don't active*/
if ( !function_exists('zota_tbay_get_variation_swatchs') ) {
    function zota_tbay_get_variation_swatchs() {
        $swatchs = array( '' => esc_html__('None', 'zota'));

        if( !zota_is_Woocommerce_activated() ) return $swatchs;

        // Array of defined attribute taxonomies.
        $attribute_taxonomies = wc_get_attribute_taxonomies();

        if ( ! empty( $attribute_taxonomies ) ) {
          foreach ( $attribute_taxonomies as $key => $tax ) {
            $attribute_taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );
            $label                   = $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name;

            $swatchs[$attribute_taxonomy_name] = $label;
          }
        }

        return $swatchs;
    }
}

if (!function_exists('zota_tbay_get_custom_tab_layouts')) {
    function zota_tbay_get_custom_tab_layouts() {
        if (!zota_is_Woocommerce_activated()) {
            return array('' => 'None');
        }

        $tabs = get_transient('zota_custom_tab_layouts');
        if (false === $tabs) {
            $tabs = array('' => 'None');
            $args = array(
                'posts_per_page'   => apply_filters('zota_tbay_get_custom_tab_layouts_posts_per_page', 20),
                'offset'           => 0,
                'orderby'          => 'date',
                'order'            => 'DESC',
                'post_type'        => 'tbay_customtab',
                'post_status'      => 'publish',
                'suppress_filters' => true,
                'no_found_rows'    => true,
            );
            $posts = get_posts($args);
            foreach ($posts as $post) {
                $tabs[$post->post_name] = $post->post_title;
            }
            set_transient('zota_custom_tab_layouts', $tabs, 24 * HOUR_IN_SECONDS);
        }
        return $tabs;
    }
}

/*Get title mobile in top bar mobile*/
if ( ! function_exists( 'zota_tbay_get_title_mobile' ) ) {
    function zota_tbay_get_title_mobile( $title ) {
		$delimiter = ' / ';

        if ( is_search() ) {
            $title = esc_html__('Search results for','zota') . ' "' . get_search_query() . '"';
        } elseif ( is_tag() ) {
            $title = esc_html__('Posts tagged "', 'zota'). single_tag_title('', false) . '"';
        } elseif ( is_category() ) {
            $title = single_cat_title('', false);
        }  elseif ( is_archive() ) {
			$title = get_the_archive_title();
        } elseif ( is_404() ) {
            $title = esc_html__('Error 404', 'zota');
        } elseif (is_category()) {
            global $wp_query;
            $cat_obj = $wp_query->get_queried_object();
            $thisCat = $cat_obj->term_id;
            $thisCat = get_category($thisCat);
            $parentCat = get_category($thisCat->parent);
            if ($thisCat->parent != 0) echo(get_category_parents($parentCat, TRUE, ' ' . $delimiter . ' '));
            $title = single_cat_title('', false);
            
        } elseif (is_day()) {
            $title = get_the_time('d');
        } elseif (is_month()) {
            $title = get_the_time('F');
        } elseif (is_year()) {
            $title = get_the_time('Y');
        } elseif ( is_single()  && !is_attachment()) {
            $title = get_the_title();
        } else {
            $title = get_the_title();
        }

        return $title;
    }
    add_filter( 'zota_get_filter_title_mobile', 'zota_tbay_get_title_mobile' );
}


if ( ! function_exists( 'zota_tbay_get_cookie' ) ) { 
	function zota_tbay_get_cookie($name = '') {
		$check = ( isset($_COOKIE[$name]) && !empty($_COOKIE[$name]) ) ? (boolean)$_COOKIE[$name] : false;
		return $check;
	}
}

if ( ! function_exists( 'zota_tbay_active_newsletter_sidebar' ) ) { 
	function zota_tbay_active_newsletter_sidebar() {
		$active = false;

		$cookie = zota_tbay_get_cookie('hiddenmodal');

		if( !$cookie && is_active_sidebar( 'newsletter-popup' ) ) {
			$active = true;
		}

		return $active;
	}
}

if ( ! function_exists( 'zota_yith_compare_header' ) ) {
    function zota_yith_compare_header() {
        if( class_exists( 'YITH_Woocompare' ) ) { ?>
            <?php
                global $yith_woocompare;
            ?>
            <div class="yith-compare-header product">
                <a href="<?php echo esc_url($yith_woocompare->obj->view_table_url()); ?>" class="compare added">
					<i class="tb-icon tb-icon-sync"></i>
					<?php apply_filters( 'zota_get_text_compare', ''); ?>
                </a>
            </div>
    <?php }
    }
}

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
if ( ! function_exists( 'zota_pingback_header' ) ) {
	function zota_pingback_header() {
		if ( is_singular() && pings_open() ) {
			echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
		}
	}
	add_action( 'wp_head', 'zota_pingback_header', 30 );
}


if ( ! function_exists( 'zota_tbay_check_data_responsive' ) ) {
    function zota_tbay_check_data_responsive($columns, $desktop, $desktopsmall, $tablet, $landscape_mobile, $mobile) {
    	$data_array = array();

		$data_array['desktop']          =      isset($desktop) ? $desktop : $columns;
		$data_array['desktopsmall']     =      isset($desktopsmall) ? $desktopsmall : 3;
		$data_array['tablet']           =      isset($tablet) ? $tablet : 3;
		$data_array['landscape']        =      isset($landscape_mobile) ? $landscape_mobile : 3;
		$data_array['mobile']           =      isset($mobile) ? $mobile : 2;

        return $data_array; 
    }
}

if ( ! function_exists( 'zota_tbay_check_data_responsive_carousel' ) ) {
    function zota_tbay_check_data_responsive_carousel($columns, $desktop, $desktopsmall, $tablet, $landscape_mobile, $mobile) {
    	$data_responsive = zota_tbay_check_data_responsive($columns, $desktop, $desktopsmall, $tablet, $landscape_mobile, $mobile);

		$datas  = "";
		$datas .= " data-items=\"". esc_attr($columns) ."\"";
		$datas .= " data-desktopslick=\"". esc_attr($data_responsive['desktop']) ."\"";
		$datas .= " data-desktopsmallslick=\"". esc_attr($data_responsive['desktopsmall']) ."\"";
		$datas .= " data-tabletslick=\"". esc_attr($data_responsive['tablet']) ."\"";
		$datas .= " data-landscapeslick=\"". esc_attr($data_responsive['landscape']) ."\"";
		$datas .= " data-mobileslick=\"". esc_attr($data_responsive['mobile']) ."\"";

        return $datas;
    }
}


if ( ! function_exists( 'zota_tbay_check_data_responsive_grid' ) ) {
    function zota_tbay_check_data_responsive_grid($columns, $desktop, $desktopsmall, $tablet, $landscape_mobile, $mobile) {

    	$data_responsive = zota_tbay_check_data_responsive($columns, $desktop, $desktopsmall, $tablet, $landscape_mobile, $mobile);

		$datas  = "";
		$datas .= " data-xlgdesktop=\"" . esc_attr($columns) ."\"";
		$datas .= " data-desktop=\"" . esc_attr($data_responsive['desktop']) ."\"";
		$datas .= " data-desktopsmall=\"" . esc_attr($data_responsive['desktopsmall']) ."\"";
		$datas .= " data-tablet=\"" . esc_attr($data_responsive['tablet']) ."\"";
		$datas .= " data-landscape=\"" . esc_attr($data_responsive['landscape']) ."\"";
		$datas .= " data-mobile=\"" . esc_attr($data_responsive['mobile']) ."\"";

        return $datas;
    }
}

if ( ! function_exists( 'zota_tbay_check_data_carousel' ) ) {
    function zota_tbay_check_data_carousel($rows, $nav_type, $pagi_type, $loop_type, $auto_type, $autospeed_type, $disable_mobile) {
    	$data_array = array(); 

        $data_array['rows']				= isset($rows) ? $rows : 1;
        $data_array['nav'] 				= ($nav_type == 'yes') ? true : false;
        $data_array['pagination'] 		= ($pagi_type == 'yes') ? true : false;
        $data_array['loop'] 			= ($loop_type == 'yes') ? true : false;
        $data_array['auto'] 			= ($auto_type == 'yes') ? true : false;
        $data_array['autospeed'] 		= ( !empty($autospeed_type) ) ? $autospeed_type : 500;
        $data_array['disable_mobile'] 	= ($disable_mobile == 'yes') ? true : false;

        return $data_array;
    }
}

if ( ! function_exists( 'zota_tbay_data_carousel' ) ) {
    function zota_tbay_data_carousel($rows, $nav_type, $pagi_type, $loop_type, $auto_type, $autospeed_type, $disable_mobile) {

        $data_array = zota_tbay_check_data_carousel($rows, $nav_type, $pagi_type, $loop_type, $auto_type, $autospeed_type, $disable_mobile);

        $datas  = " data-carousel=\"owl\"";
        $datas .= " data-rows=\"" . esc_attr($data_array['rows']) ."\"";
        $datas .= " data-nav=\"" . esc_attr($data_array['nav']) ."\"";
        $datas .= " data-pagination=\"" . esc_attr($data_array['pagination']) ."\"";
        $datas .= " data-loop=\"" . esc_attr($data_array['loop']) ."\"";
        $datas .= " data-auto=\"" . esc_attr($data_array['auto']) ."\"";

        if($data_array['auto'] == 'yes') {
        	$datas .= " data-autospeed=\"" . esc_attr($data_array['autospeed']) ."\"";
        }

        $datas .= " data-unslick=\"" . esc_attr($data_array['disable_mobile']) ."\"";

        return $datas;
    }
}

if (!function_exists('zota_get_template_product')) {
	function zota_get_template_product() {

		$output = array(
			'inner' => esc_html__('Inner' ,'zota'), 
			'vertical' => esc_html__('Vertical' ,'zota'), 
		);

	    return $output;
	}
	add_filter( 'zota_get_template_product', 'zota_get_template_product', 10, 1 ); 
}

if (!function_exists('zota_elementor_is_activated')) {
    function zota_elementor_is_activated() {
        return class_exists( '\Elementor\Plugin' );
    }
}

if (!function_exists('zota_elementor_pro_activated')) {
    function zota_elementor_pro_activated()
    {
        return class_exists('\\ElementorPro\\Plugin');
    }
}

if (!function_exists('zota_wpml_is_activated')) {
    function zota_wpml_is_activated() {
        return class_exists('SitePress');
    }
}

if ( ! function_exists( 'zota_elementor_is_edit_mode' ) ) {
	function zota_elementor_is_edit_mode() {
		return Elementor\Plugin::$instance->editor->is_edit_mode();
	}
}

if ( ! function_exists( 'zota_elementor_is_preview_page' ) ) {
	function zota_elementor_is_preview_page() {
		return isset( $_GET['preview_id'] );
	}
}

if ( ! function_exists( 'zota_elementor_is_preview_mode' ) ) {
	function zota_elementor_is_preview_mode() {
		return Elementor\Plugin::$instance->preview->is_preview_mode();
	}
}

if (!function_exists('zota_is_Woocommerce_activated')) {
    function zota_is_Woocommerce_activated() {
        return class_exists('WooCommerce') ? true : false;
    }
}

if ( !function_exists('zota_is_woo_variation_swatches_pro') ) {
    function zota_is_woo_variation_swatches_pro() {
        return class_exists( 'Woo_Variation_Swatches_Pro' ) ? true : false;
    }
}

if ( !function_exists('zota_is_ajax_popup_quick') ) {
    function zota_is_ajax_popup_quick() {
		$active = true;

		if( zota_is_woo_variation_swatches_pro() ) {
			$active = false;
		}

        return $active;
    }
}

if (!function_exists('zota_is_cmb2')) {
    function zota_is_cmb2() {
        return defined( 'CMB2_LOADED' ) ? true : false;
    }
}

if(!function_exists('zota_switcher_to_boolean')) {
	 function zota_switcher_to_boolean($var) {
		if( $var === 'yes' ) {
			return true;
		} else {
			return false;
		}
	}
}

if(!function_exists('zota_sidebars_array')) {
	 function zota_sidebars_array() {
        global $wp_registered_sidebars;
        $sidebars = array();


        if ( !empty($wp_registered_sidebars) ) {
            foreach ($wp_registered_sidebars as $sidebar) {
                $sidebars[$sidebar['id']] = $sidebar['name'];
            }
        }

        return $sidebars;
	}
}

/**
 * Dont Update the Theme
 *
 * If there is a theme in the repo with the same name, this prevents WP from prompting an update.
 *
 * @since  1.0.0
 * @param  array $r Existing request arguments
 * @param  string $url Request URL
 * @return array Amended request arguments
 */
if(!function_exists('zota_dont_update_theme')) {
	function zota_dont_update_theme( $r, $url ) {
		if ( 0 !== strpos( $url, 'https://api.wordpress.org/themes/update-check/1.1/' ) )
			return $r; // Not a theme update request. Bail immediately.
		$themes = json_decode( $r['body']['themes'] );
		$child = get_option( 'stylesheet' );
		unset( $themes->themes->$child );
		$r['body']['themes'] = json_encode( $themes );
		return $r;
	}
	add_filter( 'http_request_args', 'zota_dont_update_theme', 5, 2 );
}

if(!function_exists('zota_elements_ready_slick')) {
	function zota_elements_ready_slick() {
	    $array = [
	        'brands', 
	        'products', 
	        'posts-grid',
	        'our-team', 
	        'product-category', 
	        'product-tabs', 
	        'testimonials',
	        'product-categories-tabs',
	        'list-categories-product',
	        'custom-image-list-categories',
	        'custom-image-list-tags',
	        'product-recently-viewed',
	        'product-flash-sales',
	        'product-list-tags',
	        'product-count-down'
	    ];
	 
	    return $array; 
	}
}


if(!function_exists('zota_tbay_footer_class')) {
	function zota_tbay_footer_class() {
		$classes = ['tbay-footer', apply_filters( 'zota_tbay_get_footer_layout', 'footer_default' )];
		
		if( zota_tbay_get_config('mobile_footer_collapse', false) ) {
			$classes[] = 'footer-mobile-collapse';
		}
		
		echo 'class="'. join( ' ', apply_filters( 'zota_tbay_footer_class', $classes) ) .'"';
	}
}

if(!function_exists('zota_elements_ready_countdown_timer')) {
	function zota_elements_ready_countdown_timer() {
	    $array = [
	        'product-flash-sales', 
	        'product-count-down'
	    ];

	    return $array;
	}
}

if (!function_exists('zota_elements_ajax_tabs')) {
    function zota_elements_ajax_tabs()
    { 
        $array = [
            'product-categories-tabs',  
            'product-tabs',
        ];

        return $array;
    }
}

if(!function_exists('zota_elements_ready_testimonials')) {
	function zota_elements_ready_testimonials() {
	    $array = [
	        'testimonials', 
	    ];

	    return $array;
	}
}

if(!function_exists('zota_elements_ready_nav_menu')) {
	function zota_elements_ready_nav_menu() {
	    $array = [
	        'nav-menu', 
	    ];

	    return $array;
	}
}

if(!function_exists('zota_elements_ready_autocomplete')) {
	function zota_elements_ready_autocomplete() {
	    $array = [
	        'search-form', 
	    ];

	    return $array;
	}
}

if(!function_exists('zota_elements_ready_customfonts')) {
	function zota_elements_ready_customfonts() {
	    $array = [
	        'list-custom-fonts', 
	    ];

	    return $array;
	}
}

if(!function_exists('zota_elements_ready_sumoselect')) {
	function zota_elements_ready_sumoselect() {
	    $array = [
	        'search-form', 
	        'custom-language', 
	        'currency',
	    ];

	    return $array;
	}
}



if(!function_exists('zota_localize_translate')) {
	function zota_localize_translate() { 
		$zota_hash_transient = get_transient( 'zota-hash-time' );
		if ( false === $zota_hash_transient ) {
			$zota_hash_transient = time();
			set_transient( 'zota-hash-time', $zota_hash_transient );
		}
  
		global $wp_query; 
	        
	    $config = array(
 			'storage_key'  		=> apply_filters( 'zota_storage_key', 'zota_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() . $zota_hash_transient ) ),
	        'quantity_minus'    => apply_filters( 'zota_quantity_minus', '<i class="tb-icon tb-icon-minus"></i>'),
	        'quantity_plus'     => apply_filters( 'zota_quantity_plus', '<i class="tb-icon tb-icon-plus"></i>'),
	        'ajaxurl'			=> admin_url( 'admin-ajax.php' ),
			'clear_megamenu_cache' 	=> false, 
	        'cancel'            => esc_html__('cancel', 'zota'),
	        'show_all_text'     => esc_html__('View all', 'zota'),
			'search'            => esc_html__('Search', 'zota'),
			'close'				=> esc_html__('Close','zota'),
	        'posts'             => json_encode( $wp_query->query_vars ), // everything about your loop is here
	        'max_page'          => $wp_query->max_num_pages,
	        'mobile'            => wp_is_mobile(),
			/*Element ready default callback*/
	        'elements_ready'  => array(
	            'slick'               => zota_elements_ready_slick(),
				'ajax_tabs'           => zota_elements_ajax_tabs(),
	            'countdowntimer'      => zota_elements_ready_countdown_timer(),
	            'testimonials'        => zota_elements_ready_testimonials(),
	            'navmenu'        	  => zota_elements_ready_nav_menu(),
	            'autocomplete'        => zota_elements_ready_autocomplete(),
	            'customfonts'         => zota_elements_ready_customfonts(),
	            'sumoselect'          => zota_elements_ready_sumoselect(),
	        ) 
	    );
		
		if (get_transient('zota_megamenu_cache_clear')) {
			$config['clear_megamenu_cache'] = true;
		}

		if( zota_elementor_is_activated() ) {    
            $config['is_edit']      = zota_elementor_is_edit_mode();
            $config['combined_css'] = zota_get_elementor_css_print_method();
        } 

	    if( zota_is_Woocommerce_activated() ) {  

	        $position                       = ( wp_is_mobile() ) ? 'right' : apply_filters( 'zota_cart_position', 10,2 );
			$woo_mode                       = zota_tbay_woocommerce_get_display_mode();
	        $quantity_mode                  = zota_woocommerce_quantity_mode_active();
	        // loader gif
	        $loader                         = apply_filters( 'zota_quick_view_loader_gif', ZOTA_IMAGES . '/ajax-loader.gif' );
	 
	        $config['current_page']         = get_query_var( 'paged' ) ? get_query_var('paged') : 1;

	        $config['popup_cart_icon']      = apply_filters( 'zota_popup_cart_icon', '<i class="tb-icon tb-icon-cross"></i>',2 );
	        $config['popup_cart_noti']      = esc_html__('was added to shopping cart.', 'zota');

	        $config['cart_position']        = $position;
	        $config['ajax_update_quantity'] = (bool) zota_tbay_get_config('ajax_update_quantity', false);

			$config['display_mode']         = $woo_mode;  
			$config['quantity_mode']        = $quantity_mode;
	        $config['loader']               = $loader;

			$config['wp_product_remove_nonce']   = wp_create_nonce('zota_product_remove_nonce');

	        $config['is_checkout']          =  is_checkout();

			$config['ajax_popup_quick']     =  apply_filters( 'zota_ajax_popup_quick', zota_is_ajax_popup_quick() );
			$config['wc_ajax_url']          =  WC_AJAX::get_endpoint('%%endpoint%%'); 
	        $config['checkout_url']         =  wc_get_checkout_url();
	        $config['i18n_checkout']        =  esc_html__('Checkout', 'zota');

	        $config['img_class_container']                  =  '.'.zota_get_gallery_item_class();
	        $config['thumbnail_gallery_class_element']      =  '.'.zota_get_thumbnail_gallery_item();
	    }
 
	    return apply_filters('zota_localize_translate', $config);
	}
}


if ( ! function_exists( 'zota_instagram_feed_row_class' ) ) {
	function zota_instagram_feed_row_class($array) {
		if (!is_array($array)) { 
			return false; 
		} 
		$result = ''; 
		foreach ($array as $key => $value) { 
			if( $key !== 'tb-atts' && $key !== 'user' ) {
				$result .= ' '.$key.'='."'$value'";
			}
		}

		echo trim($result);
	}
}

if(!function_exists('zota_register_widget_template_elementor')) {
	function zota_register_widget_template_elementor($widgets) { 
		array_push($widgets,'Tbay_Widget_Template_Elementor');
		return $widgets;
	}
	add_filter('tbay_elementor_register_widgets_theme', 'zota_register_widget_template_elementor', 10, 1);
}

if(!function_exists('zota_sb_instagram_get_user_account_data')) {
	function zota_sb_instagram_get_user_account_data( ) {
		$sbi_options = get_option( 'sb_instagram_settings', array() );
		$connected_accounts = $sbi_options['connected_accounts'];

		$users = array();

		if( empty( $connected_accounts ) ) return '';

		foreach ($connected_accounts as $key => $value) {
			array_push($users, $value['username']);
		}

		return implode(",",$users);
	}
}


if (!function_exists('zota_wc_get_custom_tab_options')) {
    /**
     * Retrieve a list of custom tab options for WooCommerce
     * @return array List of tabs with keys as post_name and values as post_title
     */
    function zota_wc_get_custom_tab_options() {
        // Check if WooCommerce is not activated
        if (!function_exists('zota_is_Woocommerce_activated') || !zota_is_Woocommerce_activated()) {
            return array('' => esc_html__('No Tab', 'zota'));
        }

        // Check transient for cached data
        $tabs = get_transient('zota_wc_custom_tab_options');
        if (false === $tabs) {
            // Initialize default array
            $tabs = array('' => esc_html__('No Tab', 'zota'));

            // Set up query arguments
            $args = array(
				'posts_per_page'   => apply_filters('zota_tbay_get_custom_tab_posts_per_page', 20),
                'offset'           => 0,
                'orderby'          => 'date',
                'order'            => 'DESC',
                'post_type'        => 'tbay_customtab',
                'post_status'      => 'publish',
                'suppress_filters' => true,
                'no_found_rows'    => true, // Disable pagination calculation for faster query
            );

            // Execute the query
            $posts = get_posts($args);

            // Process results and add to the array
            if ($posts) {
                foreach ($posts as $post) {
                    $tabs[$post->post_name] = $post->post_title;
                }
            }

            // Store results in transient, expires after 24 hours
            set_transient('zota_wc_custom_tab_options', $tabs, 24 * HOUR_IN_SECONDS);
        }

        return $tabs;
    }
}

if ( !function_exists('zota_register_custom_tab') ) {
	function zota_register_custom_tab($types) {

		array_push($types, 'customtab');

		return $types;
	}
	add_filter('tbay_elementor_register_post_types', 'zota_register_custom_tab', 10, 1);
}

if( !function_exists('zota_rocket_lazyload_exclude_class') ) {
	function zota_rocket_lazyload_exclude_class( $attributes ) {
        $attributes[] = 'class="attachment-yith-woocompare-image size-yith-woocompare-image"';
        $attributes[] = 'class="header-logo-img"';
        $attributes[] = 'class="logo-mobile-img"';
        $attributes[] = 'class="mobile-infor-img"';
        $attributes[] = 'class="wpml-ls-flag"';
        $attributes[] = 'class="review-images"';

		return $attributes;
	}
	add_filter( 'rocket_lazyload_excluded_attributes', 'zota_rocket_lazyload_exclude_class' );
}

if ( ! function_exists( 'zota_is_remove_scripts' ) ) {
    function zota_is_remove_scripts() {

        if ( function_exists( 'is_vendor_dashboard' ) && is_vendor_dashboard() && is_user_logged_in() && (is_user_wcmp_vendor(get_current_user_id()) || is_user_wcmp_pending_vendor(get_current_user_id()) || is_user_wcmp_rejected_vendor(get_current_user_id())) && apply_filters('wcmp_vendor_dashboard_exclude_header_footer', true) ) {
            return true;
        }

        return false;
    }
}

/**
 * Check is vendor active
 *
 * @return bool
 */
if ( ! function_exists( 'zota_woo_is_active_vendor' ) ) {
    function zota_woo_is_active_vendor() {

        if ( function_exists( 'dokan_is_store_page' ) ) {
            return true;
        }

        if ( class_exists( 'WCV_Vendors' ) ) {
            return true;
        }

        if ( class_exists( 'MVX' ) ) {
            return true;
        }

        if ( function_exists( 'wcfm_is_store_page' ) ) {
            return true;
        }

        return false;
    }
}

if(!function_exists('zota_catalog_mode_active')){
    function zota_catalog_mode_active( ) {
        $active = (isset($_GET['catalog_mode'])) ? $_GET['catalog_mode'] : zota_tbay_get_config('enable_woocommerce_catalog_mode', false);

       return $active;
    }
}

// header located on slider
if(!function_exists('zota_header_located_on_slider')) {
	function zota_header_located_on_slider() {
		$active  =   ( isset($_GET['header_located_on_slider']) ) ? $_GET['header_located_on_slider'] : zota_tbay_get_config('header_located_on_slider', false);
		
		$class = '';
		if($active) {
			$class = "header-on-slider";
		}
		
		return $class;
	}
}

if (! function_exists('zota_checkout_optimized')) {
    function zota_checkout_optimized()
    {
        if( !zota_is_Woocommerce_activated() ) return false;

        // Check cart has contents.
		if ( WC()->cart->is_empty() && ! is_customize_preview() && apply_filters( 'woocommerce_checkout_redirect_empty_cart', true ) ) {
			return false;
		}
       
        if( is_checkout() && zota_tbay_get_config('show_checkout_optimized', false) ) {
            return true; 
        } else {
            return false;
        }
    }
}

/**
 * ------------------------------------------------------------------------------------------------
 * The Logo Checkout
 * ------------------------------------------------------------------------------------------------
 */

if (! function_exists('zota_the_logo_checkout')) {
    function zota_the_logo_checkout()
    {
        if( !zota_checkout_optimized() ) return;

        $ouput = zota_tbay_get_logo_checkout();
        echo trim($ouput);
    }
    add_action('zota_theme_header_checkout', 'zota_the_logo_checkout', 10);
}

if (! function_exists('zota_tbay_get_logo_checkout')) {
    function zota_tbay_get_logo_checkout()
    {
        $logo 			= zota_tbay_get_config('checkout_logo');
        $active_theme 			= zota_tbay_get_theme();

        $output 	= '<div class="checkout-logo">';
        if (isset($logo['url']) && !empty($logo['url'])) {
            $url    	= $logo['url'];
            $output 	.= '<a href="'. esc_url(home_url('/')) .'">';

            if (isset($logo['width']) && !empty($logo['width'])) {
                $output 		.= '<img src="'. esc_url($url) .'" width="'. esc_attr($logo['width']) .'" height="'. esc_attr($logo['height']) .'" alt="'. get_bloginfo('name') .'">';
            } else {
                $output 		.= '<img class="logo-checkout-img" src="'. esc_url($url) .'" alt="'. get_bloginfo('name') .'">';
            } 

                
            $output 		.= '</a>';
        } else {
            $output 		.= '<div class="logo-theme">';
            $output 	.= '<a href="'. esc_url(home_url('/')) .'">';
            $output 	.= '<img class="logo-checkout-img" src="'. esc_url_raw(ZOTA_IMAGES.'/'.$active_theme.'/logo-checkout.svg') .'" alt="'. get_bloginfo('name') .'">';
            $output 	.= '</a>';
            $output 		.= '</div>';
        }
        $output 	.= '</div>';
        
        return apply_filters('zota_tbay_get_logo_checkout', $output, 10);
    }
}

if (!function_exists('zota_clean')) {
    function zota_clean($data) {
        if (!is_array($data) && !is_string($data)) {
            return '';
        }
        if (is_array($data)) {
            return array_map('zota_clean', array_filter($data));
        }
        return sanitize_text_field(wp_unslash($data));
    }
}

if ( ! function_exists( 'zota_clear_transient' ) ) {
	function zota_clear_transient() {
		delete_transient( 'zota-hash-time' );
	} 
	add_action( 'wp_update_nav_menu_item', 'zota_clear_transient', 11, 1 );
}

if (! function_exists('zota_nav_menu_get_menu_class')) {
    function zota_nav_menu_get_menu_class($layout)
    {
		$menu_class    = 'elementor-nav-menu menu nav navbar-nav megamenu';

		switch ($layout) {
			case 'vertical':
				$menu_class .= ' flex-column';
				break;

			case 'treeview':
				$menu_class = 'menu navbar-nav';
				break;
			
			default:
				$menu_class .= ' flex-row';
				break;
		}

		return  $menu_class;
    }
}

if (! function_exists('zota_get_mobile_menu_mmenu')) {
    function zota_get_mobile_menu_mmenu( $slug )
    {
        return wp_nav_menu(array(
            'echo'        => false,
            'theme_location' => '',
            'menu'           => $slug,
            'container_id'   => 'main-mobile-menu-mmenu',
            'menu_id'        => 'main-mobile-menu-mmenu-wrapper',
            'menu_class'     => 'menu', 
            'walker'         => new Zota_Tbay_mmenu_menu(),
        ));
    }
}


if (! function_exists('zota_get_transliterate')) {
    function zota_get_transliterate($slug) {
        $slug = urldecode($slug);

        if (function_exists('iconv') && defined('ICONV_IMPL') && @strcasecmp(ICONV_IMPL, 'unknown') !== 0) {
            $slug = iconv('UTF-8', 'UTF-8//TRANSLIT//IGNORE', $slug);
        }

        return $slug;
	}
}

 
if (! function_exists('zota_active_theme_setup')) {
    function zota_active_theme_setup()
    {
    	$active = zota_tbay_get_global_config('theme_setup', true);

    	return $active;
    }
}

if (! function_exists('zota_elementor_general_widgets')) {
    function zota_elementor_general_widgets() {
        $elements = array(
            'template',  
            'heading',  
            'features', 
            'brands',
            'banner',
            'posts-grid',
            'our-team', 
            'testimonials',
            'list-custom-fonts',
            'button',
            'list-menu',
            'menu-vertical',
        );

        if( class_exists('MC4WP_MailChimp') ) {
            array_push($elements, 'newsletter');
        }

        
        if( function_exists( 'sb_instagram_feed_init' ) ) {
            array_push($elements, 'instagram-feed');
        }

        return apply_filters('zota_general_elements_array', $elements );
    }
}

if (! function_exists('zota_elementor_woocommerce_widgets')) {
    function zota_elementor_woocommerce_widgets() {
		$elements = array(
            'products',
            'product-category',
            'product-tabs',
            'woocommerce-tags',
            'custom-image-list-tags',
            'product-categories-tabs',
            'list-categories-product',
            'product-recently-viewed',
            'custom-image-list-categories',
            'product-flash-sales',
            'product-count-down',
            'product-list-tags'
        );

        return apply_filters('zota_woocommerce_elements_array', $elements );
    }
}


if (! function_exists('zota_elementor_header_widgets')) {
    function zota_elementor_header_widgets() {
		$elements = array(
            'site-logo',
            'nav-menu',
            'search-form',
            'search-canvas',
            'banner-close',
            'canvas-menu-template',
        );

        if( zota_is_Woocommerce_activated() ) {
            array_push($elements, 'account');

            if( !zota_catalog_mode_active() ) {
                array_push($elements, 'mini-cart');
            }
        }

        if( class_exists('WOOCS_STARTER') ) {
            array_push($elements, 'currency');
        }

        if( class_exists( 'YITH_WCWL' ) ) {
            array_push($elements, 'wishlist');
        }

        if( class_exists( 'YITH_Woocompare' ) ) {
            array_push($elements, 'compare');
        } 

        if( defined('TBAY_ELEMENTOR_DEMO') ) {
            array_push($elements, 'custom-language');
        }

        return apply_filters('zota_header_elements_array', $elements );
    }
}

if ( ! function_exists( 'zota_wpml_object_id' ) ) {
	function zota_wpml_object_id( $element_id, $element_type = 'post', $return_original_if_missing = false, $language_code = null ) {
		if ( function_exists( 'wpml_object_id_filter' ) ) {
			return wpml_object_id_filter( $element_id, $element_type, $return_original_if_missing, $language_code );
		} elseif ( function_exists( 'icl_object_id' ) ) {
			return icl_object_id( $element_id, $element_type, $return_original_if_missing, $language_code );
		} else {
			return $element_id;
		}
	}
}