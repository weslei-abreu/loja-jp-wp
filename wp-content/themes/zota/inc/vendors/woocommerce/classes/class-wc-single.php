<?php
if ( ! defined( 'ABSPATH' ) || !zota_is_Woocommerce_activated() ) {
	exit;
}

if ( ! class_exists( 'Zota_Single_WooCommerce' ) ) :


	class Zota_Single_WooCommerce  {

		static $instance;
		protected $counter;
		private static $config_cache = [];

		public static function getInstance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Zota_Single_WooCommerce ) ) {
				self::$instance = new Zota_Single_WooCommerce();
			}

			return self::$instance;
		}

		/**
		 * Setup class.
		 *
		 * @since 1.0
		 *
		 */
		public function __construct() {
			$this->counter = 0;

			add_action( 'after_setup_theme', array( $this, 'setup_support' ), 10 );

			/*Group Product*/
			add_action('woocommerce_after_add_to_cart_button', array( $this, 'product_group_buttons'), 20);

			/*Body Class*/
			add_filter( 'body_class', array( $this, 'body_class_single_one_page'), 10, 1 );
			add_filter( 'body_class', array( $this, 'body_class_gallery_lightbox'), 10, 1 );


			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'buy_now_html'), 10 ); 

			/*Add To Cart Redirect*/ 
			add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'buy_now_redirect'), 99, 1 );


    		add_action('woocommerce_before_single_product', array( $this, 'product_group_buttons_out_of_stock'), 10);

    		/*The list images review*/
    		add_action( 'woocommerce_before_single_product_summary', array( $this, 'the_list_images_review'), 100 );

			add_action( 'woocommerce_single_product_summary', array( $this, 'share_box_html'), 50 );

    		add_filter( 'zota_woo_class_single_product', array( $this, 'class_single_product'), 10, 1 );

    		add_filter( 'zota_woo_tabs_style_single_product', array( $this, 'get_tabs_style_product'), 10, 1 );

			add_action( 'zota_top_single_product', array( $this, 'the_product_single_time_countdown'), 5 );
			

    		if(!wp_is_mobile() ) {
			    add_action( 'woocommerce_before_single_product', array( $this, 'the_sticky_menu_bar'), 30 );

			    add_action( 'zota_sticky_menu_bar_product_summary', 'zota_woocommerce_single_title', 5 );
			    add_action( 'zota_sticky_menu_bar_product_summary', 'woocommerce_template_single_rating', 10 );
			    add_action( 'zota_sticky_menu_bar_product_summary', array( $this, 'the_product_single_one_page'), 15 );


			    add_action( 'zota_sticky_menu_bar_product_price_cart', 'woocommerce_template_single_price', 5 );
			    add_action( 'zota_sticky_menu_bar_product_price_cart', array( $this, 'the_sticky_menu_bar_custom_add_to_cart'), 10 );
			}


			add_action('woocommerce_before_add_to_cart_button', array( $this, 'before_add_to_cart_form'), 99);
			add_action('woocommerce_after_add_to_cart_button', array( $this, 'close_after_add_to_cart_form'), 99);


			add_filter( 'woocommerce_output_related_products_args', array( $this, 'get_related_products_args'), 10, 1 );

			/** Video **/
			add_action( 'woocommerce_product_thumbnails', array( $this, 'get_video_audio_content_last' ), 99 );
			add_action( 'zota_woocommerce_before_product_thumbnails', array($this,'get_video_audio_content_first'), 10, 2 );

  			add_action( 'woocommerce_before_single_product' , array( $this, 'product_size_guide_hook') , 100 );


			add_filter( 'woocommerce_single_product_zoom_enabled', array( $this, 'remove_support_zoom_image'), 10, 1 );

			// remove product related
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

			// add product related
			add_action( 'woocommerce_before_main_content', array( $this, 'add_related_products'), 10, 0 );

  			/*Add custom html before, after button add to cart*/
  			add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'html_before_add_to_cart_button'), 10, 0 ); 
  			add_action( 'woocommerce_after_add_to_cart_form', array( $this, 'html_after_add_to_cart_button'), 999, 0 ); 


  			add_action( 'zota_woo_after_single_rating', array( $this, 'the_total_sales_count'), 10 ); 

  			add_filter( 'woocommerce_photo_reviews_thumbnail_photo', array( $this, 'get_photo_reviews_thumbnail_size'), 10, 2 );
  			add_filter( 'woocommerce_photo_reviews_large_photo', array( $this, 'get_photo_reviews_large_size'), 10, 2 );

			/** Change size image photo reivew */
			add_filter('woocommerce_photo_reviews_reduce_array', array( $this, 'photo_reviews_reduce_array' ), 10 ,1);

			/** Remove Review Tab */
			add_filter( 'woocommerce_product_tabs', array( $this, 'remove_review_tab'), 100 );

			add_filter( 'woocommerce_product_tabs', array( $this, 'add_custom_product_tabs'), 100, 1 ); 


			add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'mobile_add_before_add_to_cart_button'), 10, 1 ); 
			add_action( 'woocommerce_before_variations_form', array( $this, 'mobile_add_before_variations_form'), 10, 1 ); 
			add_action( 'woocommerce_grouped_product_list_before', array( $this, 'mobile_before_grouped_product_list'), 10, 1 ); 
			add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'mobile_add_before_add_to_cart_form'), 20, 1 ); 
			add_action( 'woocommerce_after_add_to_cart_form', array( $this, 'mobile_add_btn_after_add_to_cart_form'), 10, 1 ); 

			add_action('update_post_meta', [$this, 'clear_video_transient_on_meta_update'], 10, 4);
		}

		public function clear_video_transient_on_meta_update($meta_id, $object_id, $meta_key, $meta_value) {
			if (get_post_type($object_id) !== 'product') {
				return;
			}
			if (in_array($meta_key, ['_zota_video_url', '_zota_video_image_url'])) {
				$transient_key = 'zota_video_' . $object_id;
				delete_transient($transient_key);
			}
		}
		

		public function add_custom_product_tabs( $tabs ) {
			$post_types = apply_filters( 'tbay_elementor_register_post_types', array());

			if( !in_array('customtab', $post_types) ) return $tabs;

			global $product;

			$product_id = method_exists( $product, 'get_id' ) === true ? $product->get_id() : $product->ID;

			$post_slug = maybe_unserialize( get_post_meta( $product_id, '_zota_custom_tab' , true ) ); 
			$priority = maybe_unserialize( get_post_meta( $product_id, '_zota_custom_tab_priority' , true ) );

			if( empty($post_slug) ) return $tabs;

			$args = array(
				'name'		 => $post_slug,
				'post_type'   => 'tbay_customtab',
				'post_status' => 'publish',
				'numberposts' => 1
			); 
			$posts = get_posts($args);

			if( empty($posts[0]->ID) || is_null($posts[0]->ID) ) return $tabs;

			$post	= $posts[0];

			if( zota_elementor_is_activated() && Elementor\Plugin::instance()->documents->get( $post->ID )->is_built_with_elementor() ) {
				$content = Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $post->ID, zota_get_elementor_css_print_method() );
			} else {
				$content = do_shortcode( $post->post_content );
			} 

			$tabs['zota-customtab'] = array(
				'title'		=> get_the_title( $post->ID ),
				'post_slug'	=> $post_slug,
				'priority'	=> $priority,
				'callback'	=> array( $this, 'custom_product_tab_panel_content' ),
				'content'   => $content,
			);

			return $tabs;

		}


		public function add_related_products() {
			if(! is_single() ) return;
			add_action( 'woocommerce_after_main_container', 'woocommerce_output_related_products', 20 );
		}
		public function custom_product_tab_panel_content($key, $tab) {
			echo trim($tab['content']);
		}

		public function setup_support() {
			add_theme_support( 'wc-product-gallery-zoom' );
			add_theme_support( 'wc-product-gallery-lightbox' );
			add_theme_support( 'wc-product-gallery-slider' );

			if( class_exists( 'YITH_Woocompare' ) ) {
				update_option('yith_woocompare_compare_button_in_products_list', 'no');
				update_option('yith_woocompare_compare_button_in_product_page', 'no');
				update_option('yith_woocompare_show_compare_button_in', 'shop');
				update_option('yith_woocompare_is_button', 'link');
	        }        

	        if( class_exists( 'YITH_WCWL' ) ) {
	            update_option( 'yith_wcwl_button_position', 'shortcode' ); 
	        }
	        if( class_exists( 'YITH_WCBR' ) ) {
	            update_option( 'yith_wcbr_single_product_brands_content', 'name' );
	        }

	        add_filter( 'woocommerce_get_image_size_gallery_thumbnail', array( $this, 'get_image_size_gallery_thumbnail'), 10, 1 );

	        $ptreviews = $this->photo_reviews_thumbnail_image();

	        add_image_size( 'zota_photo_reviews_thumbnail_image', $ptreviews['width'], $ptreviews['height'], $ptreviews['crop'] );
		}

		public function get_image_size_gallery_thumbnail() {
			$tbay_thumbnail_width       = get_option( 'tbay_woocommerce_thumbnail_image_width', 100);
            $tbay_thumbnail_height      = get_option( 'tbay_woocommerce_thumbnail_image_height', 100);
            $tbay_thumbnail_cropping    = get_option( 'tbay_woocommerce_thumbnail_cropping', 'yes');
            $tbay_thumbnail_cropping    = ($tbay_thumbnail_cropping == 'yes') ? true : false;

            return array( 
                'width'  => $tbay_thumbnail_width,
                'height' => $tbay_thumbnail_height,
                'crop'   => $tbay_thumbnail_cropping,
            );
		}

		private function photo_reviews_thumbnail_image() {
            $thumbnail_cropping    	= get_option( 'zota_photo_reviews_thumbnail_image_cropping', 'yes');
            $cropping    			= ($thumbnail_cropping == 'yes') ? true : false;

            return array(
                'width'  => get_option( 'zota_photo_reviews_thumbnail_image_width', 100),
                'height' => get_option( 'zota_photo_reviews_thumbnail_image_height', 100),
                'crop'   => $cropping,
            );		
		}

		public function product_group_buttons() {
            if (!class_exists('YITH_WCWL') && !class_exists('YITH_Woocompare')) {
                return;
            }
            ?>
            <div class="group-button">
                <?php if (class_exists('YITH_WCWL')) : ?>
                    <div class="tbay-wishlist"><?php zota_the_yith_wishlist(); ?></div>
                <?php endif; ?>
                <?php if (class_exists('YITH_Woocompare')) : ?>
                    <div class="tbay-compare"><?php zota_the_yith_compare(get_the_ID()); ?></div>
                <?php endif; ?>
            </div>
            <?php
        }

		public function buy_now_html() {
			if( zota_catalog_mode_active() ) return;
 
			global $product;
	        if ( ! intval( zota_tbay_get_config('enable_buy_now', false) ) ) {
	            return; 
	        }

	        if ( $product->get_type() == 'external' ) { 
	            return;
	        }

	        $class = 'tbay-buy-now button';

	        if( !empty($product) && $product->is_type( 'variable' ) ){
	            $default_attributes = zota_get_default_attributes( $product );
	            $variation_id = zota_find_matching_product_variation( $product, $default_attributes );
	            
	            if( empty($variation_id) ) {
	                $class .= ' disabled';
	            } 
	        }
	 
	        echo '<button class="'. esc_attr($class) .'">'. esc_html__('Buy Now', 'zota') .'</button>';
	        echo '<input type="hidden" value="0" name="zota_buy_now" />';
		}

		public function buy_now_redirect($url) {
            if (!isset($_REQUEST['zota_buy_now']) || !$_REQUEST['zota_buy_now'] || empty($_REQUEST['quantity'])) {
                return $url;
            }
            $redirect = self::get_config('redirect_buy_now', 'cart');
            return $redirect === 'checkout' ? wc_get_checkout_url() : wc_get_cart_url();
        }

		public function product_group_buttons_out_of_stock() {
	        global $product;

	        if( $product->is_in_stock() || $product->is_type( 'variable' ) ) return;

	        remove_action('woocommerce_after_add_to_cart_button', array( $this, 'product_group_buttons'), 10);
	        add_action('woocommerce_single_product_summary', array( $this, 'product_group_buttons'), 30);
		}


		public function the_list_images_review() {
			global $product;

	        if ( ! is_product() || ( ! class_exists( 'VI_Woo_Photo_Reviews' ) && ! class_exists( 'VI_WooCommerce_Photo_Reviews' ) ) ) {
	            return;
	        }

	        $product_title = $product->get_title();
	        $product_single_layout  =   ( isset($_GET['product_single_layout']) )   ?   $_GET['product_single_layout'] :  zota_get_single_select_layout();
	        $args     = array(
	            'post_type'    => 'product',
	            'type'         => 'review',
	            'status'       => 'approve',
	            'post_id'      => $product->get_id(),
	            'meta_key'     => 'reviews-images'
	        ); 

	        $comments = get_comments( $args );

	        if (is_array($comments) || is_object($comments)) {
	            $outputs = '<div id="list-review-images">';
	            $outputs .= '<h4>'. esc_html__('Images from customers:', 'zota') .'</h4>';
	            $outputs .= '<ul>'; 

	            $i = 0;
	            foreach ( $comments as $comment ) {

                    $comment_id     = $comment->comment_ID;
                    $image_post_ids = get_comment_meta($comment_id, 'reviews-images', true);
                    $content        = get_comment( $comment_id )->comment_content;
                    $author         = '<span class="author">'. get_comment( $comment_id )->comment_author .'</span>';
                    $rating         = intval( get_comment_meta( $comment_id, 'rating', true ) );

                    if ( $rating && wc_review_ratings_enabled() ) {
                        $rating_content = wc_get_rating_html( $rating );
                    } else {
                        $rating_content = '';
                    } 

                    $caption = '<span class="header-comment">' . $rating_content . $author . '</span><span class="title-comment">'. $content .'</span>';

                    if (is_array($image_post_ids) || is_object($image_post_ids)) {
                        foreach ($image_post_ids as $image_post_id) {
                            if (! wc_is_valid_url($image_post_id)) {
                                $image_data = wp_get_attachment_metadata( $image_post_id );
                                $alt        = get_post_meta($image_post_id, '_wp_attachment_image_alt', true);
                                $image_alt  = $alt ? $alt : $product_title;

                                $width 		= $image_data['width'];
								$height 	= $image_data['height'];

                                $img_src = apply_filters('woocommerce_photo_reviews_thumbnail_photo', wp_get_attachment_thumb_url($image_post_id), $image_post_id, $comment);

                                $img_src_open = apply_filters('woocommerce_photo_reviews_large_photo', wp_get_attachment_thumb_url($image_post_id), $image_post_id, $comment);

                                $outputs .= '<li class="review-item"><a class="review-link" href="'. esc_url($img_src_open) .'" data-width="'. esc_attr($width) .'" data-height="'. esc_attr($height) .'"><div class="caption">'. trim($caption) .'</div><img class="review-images"
	                                     src="' . esc_url($img_src) .'" alt="'. apply_filters('woocommerce_photo_reviews_image_alt', $image_alt, $image_post_id, $comment) .'"/></a></li>';
                                $i++;
                            }
                        }
                    }
	            } 

	            $more = '';

	            if ($i > 6) {
	            	if ( ($product_single_layout === 'left-main') || ($product_single_layout === 'main-right') ) {
	            		$i      = $i - 6;
		            	$more   = '<li class="more d-none d-xl-flex">';
		                $more  .= '<span>'. $i .'+</span>';
		                $more  .= '</li>';
		            } elseif ($i > 8) {
		            	$i      = $i - 8;
		                $more   = '<li class="more d-none d-xl-flex">';
		                $more  .= '<span>'. $i .'+</span>';
		                $more  .= '</li>';
			        }
	            }

	            $outputs .= $more;

	            $outputs .= '</ul></div>';
	        }

	        if( $i === 0 ) {
	            return;
	        }

	        echo trim($outputs);
		} 

		public function share_box_html() {
			if( !zota_tbay_get_config('enable_code_share',false) || !zota_tbay_get_config('enable_product_social_share', false) ) return;

	  		if( zota_tbay_get_config('select_share_type') === 'custom' ) {
				$image = get_the_post_thumbnail_url( get_the_ID(), 'full' );
				zota_custom_share_code( get_the_title(), get_permalink(), $image );
	  		} else {
	  			?>
	  			 <div class="tbay-woo-share">
	              	<div class="addthis_inline_share_toolbox"></div>
	            </div>
	  			<?php
	  		}
		}

		public function class_single_product( $styles ) {

			global $product;
	        $attachment_ids = $product->get_gallery_image_ids();
	        $count = count( $attachment_ids);

	        $sidebar_configs    = zota_tbay_get_woocommerce_layout_configs();
	        $images_layout      = $sidebar_configs['thumbnail'];

	        $styles = 'style-'.$images_layout;

	        $active_stick       = '';

	        if( isset($images_layout) ) {

	          if( isset($count) && $images_layout == 'stick' && ($count > 0) ) {
	            $active_stick = ' active-stick';
	          }
	       
	        }

	        $styles .= $active_stick;

	        $active = zota_tbay_get_config('enable_size_guide', false);

		    $attachment_id = get_post_meta( $product->get_id(), '_product_size_guide_image', true );

		    if ( $active && metadata_exists( 'post', $product->get_id(), '_product_size_guide_image' ) && !empty($attachment_id) ) {
		    	$styles .= ' has-size-guide';
			}

			$cart_style = zota_get_mobile_form_cart_style();

			if ( $product->get_type() == 'external' ) { 
	            $cart_style = 'default';
	        }

			$styles .= ' form-cart-'. $cart_style; 

	        return $styles;

		}

		public function get_tabs_style_product($tabs_layout)
        {
            if (is_singular('product')) {
                $tabs_style       = zota_tbay_get_config('style_single_tabs_style', 'tabs');

                if (isset($_GET['tabs_product'])) {
                    $allowed_tabs = ['fulltext', 'tabs', 'accordion'];
                    $tabs_style_layout = in_array($_GET['tabs_product'], $allowed_tabs) ? $_GET['tabs_product'] : 'tabs';
                } else {
                    $tabs_style_layout = $tabs_style;
                }

                return $tabs_style_layout;
            }
        }

		public function the_product_single_time_countdown() {
	   		global $product;

	        $style_countdown   = zota_tbay_get_config('enable_product_countdown',false);

	        if ( isset($_GET['countdown']) ) {
	            $countdown = $_GET['countdown'];
	        }else {
	            $countdown = $style_countdown;
	        }  

	        if(!$countdown || !$product->is_on_sale() ) {
	          return '';
	        }


	        wp_enqueue_script( 'jquery-countdowntimer' );
	        $time_sale = get_post_meta( $product->get_id(), '_sale_price_dates_to', true );
			$_id = zota_tbay_random_key();
			$stock_quantity     = $product->get_stock_quantity();
		
	        ?>
	        <?php if ( $time_sale ): ?>
	            <div class="tbay-time-wrapper">
	              <div class="time tbay-time">
	                  <div class="title"><?php printf( __( 'Deal will end soon! Only %s left', 'zota' ), $stock_quantity ); ?></div>
	                  <div class="tbay-countdown" data-id="<?php echo esc_attr($_id); ?>-<?php echo esc_attr($product->get_id()); ?>" id="countdown-<?php echo esc_attr($_id); ?>-<?php echo esc_attr($product->get_id()); ?>" data-countdown="countdown" data-date="<?php echo date('m', $time_sale).'-'.date('d', $time_sale).'-'.date('Y', $time_sale).'-'. date('H', $time_sale) . '-' . date('i', $time_sale) . '-' .  date('s', $time_sale) ; ?>">
	                  </div>
	              </div> 

	            </div> 
	        <?php endif; ?> 
	        <?php
		}

		public function render_product_nav( $post, $position ) {
			if($post){
	          $product = wc_get_product($post->ID);
	          $img = '';
	          if(has_post_thumbnail($post)){
	              $img = get_the_post_thumbnail($post, 'woocommerce_gallery_thumbnail');
	          }
	          $link = get_permalink($post);

	          $left_content = ($position == 'left') ? "<a class='img-link' href=". esc_url($link) .">". trim($img). "</a>" :'';
	          $right_content = ($position == 'right') ? "<a class='img-link' href=". esc_url($link) .">". trim($img). "</a>" :'';
	          echo "<div class='". esc_attr( $position ) ." psnav'>";

	          echo trim($left_content); 
	          echo "<div class='product_single_nav_inner single_nav'>
	                    <a href=". esc_url($link) .">
	                        <span class='name-pr'>". esc_html($post->post_title) ."</span>
	                    </a>
	                </div>";    
	            echo trim($right_content);   
	          echo "</div>";
	      	}
		}

		public function the_sticky_menu_bar() {
			global $post, $product;

		      $menu_bar   =  zota_get_product_menu_bar();

		      if(  !$menu_bar ) return;



		      $img = '';
		      if(has_post_thumbnail($post)){
		          $img = get_the_post_thumbnail($post, array(50, 50));
		      }

		      ?>

		      <div id="sticky-menu-bar">
		        <div class="container">
		          <div class="row">
		            <div class="menu-bar-left col-lg-7">
		                <div class="media">
		                  <div class="media-left media-top pull-left">
		                    <?php echo trim($img); ?>
		                  </div>
		                  <div class="media-body">
		                    <?php 
		                      do_action( 'zota_sticky_menu_bar_product_summary' );
		                    ?>
		                  </div>
		                </div>
		            </div>
		            <div class="menu-bar-right product col-lg-5">
		                <?php 
		                  do_action( 'zota_sticky_menu_bar_product_price_cart');
		                ?>
		            </div>
		          </div>
		        </div>
		      </div>

		      <?php
		}

		public function the_product_single_one_page() {
			$menu_bar   =  apply_filters( 'zota_woo_product_menu_bar', 10, 2 );

	        if( isset($menu_bar) && $menu_bar ) {
	          global $product;
	          $id = $product->get_id();
	          wp_enqueue_script( 'jquery-onepagenav' );
	          ?>
	          <?php
	          
	        }
		}

		public function the_sticky_menu_bar_custom_add_to_cart() {
			global $product; 

		    if( !$product->is_in_stock() ) {
		      echo wc_get_stock_html( $product );
		    } else {
		      ?> 
		        <a id="sticky-custom-add-to-cart" href="javascript:void(0);"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></a>
		    <?php 
		  }
		}

		public function body_class_single_one_page( $classes ) {
			$menu_bar   =  apply_filters( 'zota_woo_product_menu_bar', 10, 2 );

		    if( isset($menu_bar) && $menu_bar && is_product() ) {
		      $classes[] = 'tbay-body-menu-bar';
		    }
		    return $classes;
		}		

		public function body_class_gallery_lightbox( $classes ) {

		    if( current_theme_supports( 'wc-product-gallery-lightbox' ) && is_product() ) {
		      $classes[] = 'tbay-wc-gallery-lightbox';
		    }

		    return $classes;
		}

		public function before_add_to_cart_form() {

			global $product;
	      	$id = $product->get_id();

	      	$class = 'shop-now';

	      	if( intval( zota_tbay_get_config('enable_buy_now', false) ) && $product->get_type() !== 'external' ) {
	      		$class .= ' has-buy-now';
	      	}

	      	if( class_exists('YITH_WCWL') ) {
	      		$class .= ' has-wishlist';
	      	}
	      ?> 

	      <div id="shop-now" class="<?php echo esc_attr($class); ?>">

	      <?php

		}

		public function close_after_add_to_cart_form() {
			echo '</div>';
		}

		public function get_related_products_args( $args ) {
		    $args['posts_per_page'] = zota_tbay_get_config('number_product_releated', 5); // 4 related products

		    return $args;
		}

		public function get_featured_image_id( $product, $video_id, $host ) {

			$thumbnail_id = $product->get_meta( '_zota_video_image_url' );
			return $thumbnail_id;
		}

		/**
		 * @param WC_Product $product
		 */
		public function get_featured_video_args( $product ) {

			$video_url  = $product->get_meta( '_zota_video_url' );
			$video_args = array();

			if ( ! empty( $video_url ) ) {

				list( $host, $video_id ) = explode( ':', zota_video_type_by_url( $video_url ) );

				$video_args = array(
					'video_id'      => $video_id,
					'host'          => $host,
					'thumbnail_id' => $this->get_featured_image_id( $product, $video_id, $host )
				);
			}

			return $video_args;
		}

		public function get_video_audio_content_last( ) {
			if( zota_tbay_get_config('video_position', 'last') === 'first' ) return;

			$html = $this->get_video_audio_content();

			echo trim($html);
		}

		public function get_video_audio_content_first() {

			if( zota_tbay_get_config('video_position', 'last') === 'last' ) return;

			$html = $this->get_video_audio_content();

			echo trim($html);
		}

		public function get_video_audio_content() {
            global $product;
            $video_args = $this->get_featured_video_args($product);
            if (empty(array_filter($video_args))) {
                return '';
            }
            $transient_key = 'zota_video_' . $product->get_id();
            $html = get_transient($transient_key);
            if ($html === false) {
                ob_start();
                wc_get_template('single-product/template_video.php', $video_args);
                $html = ob_get_clean();
                set_transient($transient_key, $html, WEEK_IN_SECONDS);
                $this->counter++;
            }
            return $html;
        }

		public function product_size_guide_hook() {
			$active = zota_tbay_get_config('enable_size_guide', false);

		    global $post;

		    if ( !$active || !metadata_exists( 'post', $post->ID, '_product_size_guide_image' ) )  return;

		    $attachment_id = get_post_meta( $post->ID, '_product_size_guide_image', true );
		    if( empty($attachment_id) )  return;

		    add_filter( 'woocommerce_reset_variations_link', array( $this, 'the_product_size_guide'), 25 );
		    add_filter( 'woocommerce_before_single_variation', array( $this, 'the_product_size_guide_content'), 25 );
		}

		public function the_product_size_guide() {
			$active = zota_tbay_get_config('enable_size_guide', false);
		    $icon = zota_tbay_get_config('size_guide_icon', '');

		    global $post;

		    if ( !$active || !metadata_exists( 'post', $post->ID, '_product_size_guide_image' ) )  return;

		      $attachment_id = get_post_meta( $post->ID, '_product_size_guide_image', true );

		      $title = zota_tbay_get_config('size_guide_title', false);

		      $image = wp_get_attachment_image( $attachment_id, 'full' );
		    ?> 

		    <button type="button" class="btn-size-guide" data-toggle="modal" data-target="#product-size-guide">
		    	<?php echo trim($title); ?>
		        <?php 
		          if( !empty($icon) ) {
		            echo '<i class="'. esc_attr($icon) .'"></i>';
		          }
		        ?>
		      
		    </button>
		    <div class="reset-button">
		    	<a class="reset_variations" href="#"><?php esc_html_e( 'Clear all', 'zota' ) ?></a>
			</div>
		    <?php
		}

		public function the_product_size_guide_content() {
			$active = zota_tbay_get_config('enable_size_guide', false);
		    $icon = zota_tbay_get_config('size_guide_icon', '');

		    global $post;

		    if ( !$active || !metadata_exists( 'post', $post->ID, '_product_size_guide_image' ) )  return;

		      $attachment_id = get_post_meta( $post->ID, '_product_size_guide_image', true );

		      if( empty($attachment_id) ) return;

		      $title = zota_tbay_get_config('size_guide_title', false);

		      $image = wp_get_attachment_image( $attachment_id, 'full' );
		    ?> 



		    <!-- Modal -->
		    <div id="product-size-guide" class="modal fade" role="dialog">
		      <div class="modal-dialog">

		        <!-- Modal content-->
		        <div class="modal-content">
		          <div class="modal-header">
		            <button type="button" class="close" data-dismiss="modal"><i class="tb-icon tb-icon-close-01"></i></button>
		            <h4 class="modal-title"><?php echo trim($title); ?></h4>
		          </div>
		          <div class="modal-body">
		            <?php echo trim($image); ?>
		          </div>
		        </div>

		      </div>
		    </div>
		    <?php
		}

		public function remove_review_tab( $tabs ) {
	 		if ( !zota_tbay_get_config('enable_product_review_tab', true) && isset($tabs['reviews']) ) {
	            unset( $tabs['reviews'] ); 
	        }
	        return $tabs;
		}


		public function enable_zoom_image() {
	     	$active = zota_tbay_get_config('enable_zoom_image', true);

	        if ( isset($_GET['enable_zoom_image']) ) {
	          $active = $_GET['enable_zoom_image'];
	        }

	        return $active;
		}

		public function remove_support_zoom_image() {
			$active = $this->enable_zoom_image();

			return (bool) $active;
		}


		public function html_before_add_to_cart_button() {
			$content = zota_tbay_get_config('html_before_add_to_cart_btn');
     		echo trim($content);
		}

		public function html_after_add_to_cart_button() {
			$content = zota_tbay_get_config('html_after_add_to_cart_btn');
	      	echo trim($content); 
		}

		public function the_total_sales_count() {
		 	global $product;

		    if( !intval( zota_tbay_get_config('enable_total_sales', true) ) || $product->get_type() == 'external' ) return;

		    $count = (float) get_post_meta($product->get_id(),'total_sales', true); 

		    $text = sprintf( '<span class="rate-sold"><span class="sold-text">%s</span><span class="count">%s</span></span>',
		    	esc_html__('Sold: ', 'zota'),
		        number_format_i18n($count)
		    );

		    echo trim($text);
		}

		public function get_photo_reviews_thumbnail_size($image_src, $image_post_id) {
			$img_src     = wp_get_attachment_image_src($image_post_id, 'zota_photo_reviews_thumbnail_image');

			return $img_src[0];
		}

		public function get_photo_reviews_large_size($image_src, $image_post_id) {
			$img_src     = wp_get_attachment_image_src($image_post_id, 'full');

			return $img_src[0];
		}

		public function photo_reviews_reduce_array( $reduce ) {
			array_push($reduce,'zota_photo_reviews_thumbnail_image');
			return $reduce;
		}

		public function mobile_add_add_to_cart_button_content() {
			if( zota_catalog_mode_active() ) return;

			if( zota_get_mobile_form_cart_style() === 'default' ) return;

			global $product;
			?>
			<div id="mobile-close-infor"><i class="tb-icon tb-icon-close-01"></i></div>
			<div class="mobile-infor-wrapper">
				<div class="media">
					<div class="mr-3">
						<?php echo trim($product->get_image('woocommerce_gallery_thumbnail', array('class' => 'mobile-infor-img'))); ?>
					</div>
					<div class="media-body">
						<div class="infor-body">
							<?php $this->mobile_infor_body( $product ); ?>
						</div> 
					</div>
				</div>
			</div>
			<?php
		}	 
		
		public function mobile_infor_body( $product ) {
			if( $product->is_type( 'variable' ) ) return;

			$this->mobile_infor_body_content( $product );
		}

		public function mobile_infor_body_content( $product ) {
			echo '<p class="price">'. trim($product->get_price_html()) . '</p>'; 
			echo wc_get_stock_html( $product );
		}

		public function mobile_add_before_add_to_cart_button( ) {
			if( zota_catalog_mode_active() ) return;

			if( zota_get_mobile_form_cart_style() === 'default' ) return;
			
			global $product;
			 
	        if ( $product->get_type() !== 'simple' ) return;
 
			$this->mobile_add_add_to_cart_button_content();
		}

		public function mobile_add_before_variations_form( ) {
			if( zota_catalog_mode_active() ) return;

			if( zota_get_mobile_form_cart_style() === 'default' ) return;

			$this->mobile_add_add_to_cart_button_content();
		}
		
		public function mobile_before_grouped_product_list( ) {
			if( zota_catalog_mode_active() ) return;

			if( zota_get_mobile_form_cart_style() === 'default' ) return;

			global $product;
			 
	        if ( $product->get_type() !== 'grouped' ) return;

			$this->mobile_add_add_to_cart_button_content();
		}

		public function mobile_add_btn_after_add_to_cart_form() {
			if( zota_catalog_mode_active() ) return;

			if( zota_get_mobile_form_cart_style() === 'default' ) return;

			global $product;

	        if ( $product->get_type() == 'external' ) { 
	            return;
			}

			$class = '';
			if( zota_tbay_get_config('enable_buy_now', false) ) {
				$class .= ' has-buy-now';
			}  
			
			?>
			<div id="mobile-close-infor-wrapper"></div>
			<div class="mobile-btn-cart-click <?php echo esc_attr($class); ?>">
				<div id="tbay-click-addtocart"><?php esc_html_e('Add to cart', 'zota') ?></div>
				<?php if( zota_tbay_get_config('enable_buy_now', false) ) : ?>
					<div id="tbay-click-buy-now"><?php esc_html_e('Buy Now', 'zota') ?></div>
				<?php endif; ?> 
			</div>
			<?php
		}

		public function mobile_add_before_add_to_cart_form( ) {
			if( zota_catalog_mode_active() ) return;

			if( zota_get_mobile_form_cart_style() === 'default' ) return;

			global $product;
			if( !$product->is_type( 'variable' ) ) return; 

			$attributes = $product->get_variation_attributes();
			$selected_attributes 	= $product->get_default_attributes();
			if( sizeof( $attributes ) === 0 ) return;

			$default_attributes = $names = array();

			foreach ( $attributes as $key => $value ) {
				array_push($names, wc_attribute_label( $key ));

				if( isset($selected_attributes[$key]) && !empty($selected_attributes[$key]) )  {
					$default = get_term_by('slug', $selected_attributes[$key], $key)->name;
				} else {   
					$default = esc_html__('Choose an option ', 'zota');
				}

				array_push($default_attributes, $default);
			}
			
			?>
			<div class="mobile-attribute-list">
				<div class="list-wrapper">
					<div class="name">
						<?php echo esc_html(implode( ', ', $names )); ?>
					</div>
					<div class="value">
						<?php echo esc_html(implode( '/ ', $default_attributes )); ?>
					</div>
				</div>
				<div id="attribute-open"><i class="tb-icon tb-icon-angle-right"></i></div>
			</div>
			<?php
		}

		private static function get_config($key, $default = '') {
            if (!isset(self::$config_cache[$key])) {
                self::$config_cache[$key] = zota_tbay_get_config($key, $default);
            }
            return self::$config_cache[$key];
        }

	}
endif;

if ( !function_exists('zota_single_wooCommerce') ) {
	function zota_single_wooCommerce() { 
		return Zota_Single_WooCommerce::getInstance();
	}
	zota_single_wooCommerce();
}