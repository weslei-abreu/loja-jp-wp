<?php
if ( ! defined( 'ABSPATH' ) || !zota_is_Woocommerce_activated() ) {
	exit;
}

if ( ! class_exists( 'Zota_Shop_WooCommerce' ) ) :


	class Zota_Shop_WooCommerce  {

		static $instance;
		private static $config_cache = [];

		public static function getInstance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Zota_Shop_WooCommerce ) ) {
				self::$instance = new Zota_Shop_WooCommerce();
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

			add_action( 'woocommerce_archive_description', array( $this, 'shop_category_image'), 2 );
			add_action('woocommerce_before_main_content', array( $this, 'shop_remove_des_image'), 20);

			add_action( 'zota_before_sidebar_mobile', array( $this, 'zota_tbay_header_mobile_side_woocommerce_sidebar'), 20 );

			/*Shop page*/
			add_action( 'woocommerce_before_shop_loop', array( $this, 'shop_display_modes'), 16 );
			add_action( 'woocommerce_before_shop_loop', array( $this, 'shop_filter_before') , 3 );
			add_action( 'woocommerce_before_shop_loop', array( $this, 'content_shop_filter_before') , 15 );
			add_action( 'woocommerce_before_shop_loop', array( $this, 'content_shop_filter_after') , 70 );
			add_action( 'woocommerce_before_shop_loop', array( $this, 'shop_filter_after') , 70 );

			
			/*Filter Sidebar*/
			add_action( 'woocommerce_before_shop_loop', array( $this, 'button_filter_sidebar_html') , 15 );

			/*Vendor Dokan page*/
			if( class_exists('WeDevs_Dokan') ) {
				add_action( 'dokan_store_profile_frame_after', array( $this, 'shop_display_modes'), 40 );
				add_action( 'dokan_store_profile_frame_after', array( $this, 'shop_filter_before') , 3 );
				add_action( 'dokan_store_profile_frame_after', array( $this, 'content_shop_filter_before') , 15 );
				add_action( 'dokan_store_profile_frame_after', array( $this, 'content_shop_filter_after') , 70 );
				add_action( 'dokan_store_profile_frame_after', array( $this, 'shop_filter_after') , 70 );
				add_action( 'dokan_store_profile_frame_after', array( $this, 'button_filter_sidebar_html') , 20 );
			}

			add_action( 'zota_woo_template_main_before', array( $this, 'shop_product_top_sidebar'), 50 );

			add_filter( 'loop_shop_per_page', array( $this, 'shop_per_page'), 10 );
			add_filter( 'loop_shop_columns', array( $this, 'shop_columns'), 10 );

			/*display image mode*/
			add_filter( 'zota_woo_display_image_mode', array( $this, 'get_display_image_mode'), 10, 1 );

			add_filter( 'zota_woo_config_product_layout', array( $this, 'product_layout_style') );

			add_action('woocommerce_before_shop_loop_item_title', array( $this, 'the_display_image_mode'), 10, 2);

			/*swatches*/
			add_action( 'zota_tbay_variable_product', array( $this, 'the_woocommerce_variable'), 20 );
			add_action( 'zota_woo_list_after_short_description', array( $this, 'the_woocommerce_variable'), 5 );

			/* sold */
			add_action( 'zota_woo_list_after_short_description', array( $this, 'zota_tbay_total_sales'), 15 );

			add_action('zota_woocommerce_before_shop_list_item', array( $this, 'remove_variable_on_list'), 10);

			/*Shop load more*/
			add_action('wp_ajax_nopriv_tbay_more_post_ajax', array( $this, 'shop_load_more'), 10);
			add_action('wp_ajax_tbay_more_post_ajax', array( $this, 'shop_load_more'), 10);

			/*Shop Query*/
			add_action( 'woocommerce_product_query', array( $this, 'product_per_page_query'), 10, 2 );

			/*Load more shop pagination*/
			add_action('wp_ajax_nopriv_tbay_pagination_more_post_ajax', array( $this, 'pagination_more_post_ajax'), 10);
			add_action('wp_ajax_tbay_pagination_more_post_ajax', array( $this, 'pagination_more_post_ajax'), 10);
 
			/*Load more shop grid*/
			add_action('wp_ajax_nopriv_tbay_grid_post_ajax', array( $this, 'ajax_load_more_grid_product'), 10);
			add_action('wp_ajax_tbay_grid_post_ajax', array( $this, 'ajax_load_more_grid_product'), 10);

			/*Load more shop list*/
			add_action('wp_ajax_nopriv_tbay_list_post_ajax', array( $this, 'ajax_load_more_list_product'), 10);
			add_action('wp_ajax_tbay_list_post_ajax', array( $this, 'ajax_load_more_list_product'), 10);

			/*Product Archive Sidebar Top*/
			add_action('zota_woo_template_main_before', array( $this, 'shop_product_top_archive'), 30);

			/*Product Archive Sidebar Bottom*/
			add_action('woocommerce_after_main_container', array( $this, 'shop_product_bottom_archive'), 10);
			

			add_filter( 'zota_woocommerce_sub_categories', array( $this, 'show_product_subcategories'), 10, 1 );


			add_filter( 'woocommerce_show_page_title' , array( $this, 'remove_title_product_archives_active'), 10, 1 );

			add_filter( 'zota_woo_config_display_mode', array( $this, 'display_modes_active'), 10, 1 );

			/*The YITH BRAND*/
			add_action('zota_woo_before_shop_loop_item_caption', array( $this, 'the_brands_the_name') , 5);
			add_action('zota_woo_before_shop_loop_item_caption_vertical', array( $this, 'the_brands_the_name') , 5);
			add_action('zota_woo_before_shop_list_caption', array( $this, 'the_brands_the_name') , 10);
			
			// add_action('woocommerce_before_single_product_summary', array( $this, 'excerpt_product_variable') , 10);
			
		}

		public function remove_variable_on_list() {
			remove_action( 'zota_tbay_after_shop_loop_item_title', array( $this, 'the_woocommerce_variable'), 20 );
		}

		public function shop_display_modes() {
            if (!apply_filters('zota_woo_config_display_mode', true) || !wc_get_loop_prop('is_paginated') || (!woocommerce_products_will_display() && !zota_woo_is_vendor_page())) {
                return;
            }
            $woo_mode = zota_tbay_woocommerce_get_display_mode();
            $grid = $woo_mode === 'grid' ? 'active' : '';
            $list = $woo_mode === 'list' ? 'active' : '';
            ?>
            <div class="display-mode-warpper">
                <a href="javascript:void(0);" id="display-mode-grid" class="display-mode-btn <?php echo esc_attr($grid); ?>" title="<?php esc_attr_e('Grid', 'zota'); ?>"><i class="tb-icon tb-icon-grid"></i></a>
                <a href="javascript:void(0);" id="display-mode-list" class="display-mode-btn list <?php echo esc_attr($list); ?>" title="<?php esc_attr_e('List', 'zota'); ?>"><i class="tb-icon tb-icon-list"></i></a>
            </div>
            <?php
        }

		
		public function is_check_woocommerce_show_sidebar() {
			$active = false;

			if( zota_woo_is_vendor_page() ) {
				$active = true;
			} else {
				if( is_product_category() || is_product_tag() || is_product_taxonomy() || is_shop() ) {
					$page = 'product_archive_sidebar'; 
		
					$sidebar = zota_tbay_get_config($page);
					if( is_active_sidebar( $sidebar ) ) {
						$active = true;
					}
				}

			}
	
			if( is_product() ) $active = false;
	
	
			return $active;
		}

		public function button_filter_sidebar_html() {


			if( !$this->is_check_woocommerce_show_sidebar() ) return; 

			$product_archive_layout  =   ( isset($_GET['product_archive_layout']) ) ? $_GET['product_archive_layout'] : zota_tbay_get_config('product_archive_layout', 'shop-left');

			$filter_class = ( $product_archive_layout !== 'full-width' ) ? ' d-xl-none' : '';
 
			echo '<div class="filter-btn-wrapper'. esc_attr($filter_class) .'"><button id="button-filter-btn" class="button-filter-btn hidden-lg hidden-md" type="submit"><i class="tb-icon tb-icon-filter" aria-hidden="true"></i></button></div>';
			echo '<div id="filter-close"></div>';
		}
		
		public function shop_filter_before() {
			$notproducts =  ( zota_is_check_hidden_filter() ) ? ' hidden' : '';

	        echo '<div class="tbay-filter'. esc_attr( $notproducts ) . '">';
		}

		public function shop_filter_after() {	
			echo '</div>';
		}
		
		public function content_shop_filter_before() {
			$class = ( $this->is_check_woocommerce_show_sidebar() ) ? 'filter-vendor' : '';

	        echo '<div class="main-filter d-flex justify-content-between '. esc_attr($class) .'">';
		}

		public function content_shop_filter_after() {	
			echo '</div>';
		}

		public function shop_product_top_sidebar() {

			$sidebar_configs = zota_tbay_get_woocommerce_layout_configs();

	        if( !is_product()  && isset($sidebar_configs['product_top_sidebar']) && $sidebar_configs['product_top_sidebar'] ) {
	            ?>

	            <?php if(is_active_sidebar('product-top-sidebar')) : ?>
	                <div class="product-top-sidebar">
	                    <div class="container">
	                        <div class="content">
	                            <?php dynamic_sidebar('product-top-sidebar'); ?>
	                        </div>
	                    </div>
	                </div>
	            <?php endif;
	        } 

		}

		public function shop_per_page() {
            $value = isset($_GET['product_per_page']) && is_numeric($_GET['product_per_page']) ? (int)$_GET['product_per_page'] : self::get_config('number_products_per_page', 12);
            return max(1, $value); // Ensure it's not less than 1
        }

		public function shop_columns() {

			if( isset($_GET['product_columns']) && is_numeric($_GET['product_columns']) ) {
	            $value = $_GET['product_columns']; 
	        } else {
	          $value = zota_tbay_get_config('product_columns', 5);          
	        }

	        if ( in_array( $value, array(1, 2, 3, 4, 5, 6) ) ) {
	            $number = $value;
	        }

	        return $number;

		}

		// add close sidebar
		public function zota_tbay_header_mobile_side_woocommerce_sidebar(){
		
			$active =  $this-> is_check_woocommerce_show_sidebar();
			$close = esc_html__('Close','zota');
			$product_archive_layout  =   ( isset($_GET['product_archive_layout']) ) ? $_GET['product_archive_layout'] : zota_tbay_get_config('product_archive_layout', 'shop-left');
			$filter_class = ( $product_archive_layout !== 'full-width' ) ? ' d-xl-none' : '';
			if ( $active ) :
			?>
				<div class="widget-mobile-heading <?php echo esc_attr($filter_class) ?>"> <a href="javascript:void(0);" class="close-side-widget"><?php echo trim($close); ?><i class="tb-icon tb-icon-minus"></i></a></div>
				<?php 
			endif;
			
		}

		public function get_display_image_mode( $mode ) {
			$mode = zota_tbay_get_config('product_display_image_mode', 'one');

			$mode = (isset($_GET['display_image_mode'])) ? $_GET['display_image_mode'] : $mode;

			if( wp_is_mobile() ) $mode = 'one';

			return $mode;
		}

		public function the_display_image_mode( $size, $only_image ) {
		 	$images_mode   = apply_filters( 'zota_woo_display_image_mode', 10,2 );

			if( $only_image ) $images_mode = 'one';

	        if( wp_is_mobile() ) $images_mode = 'one';

	        switch ($images_mode) {
	            case 'one':
	                echo woocommerce_get_product_thumbnail( $size );
	                break;        

	            case 'two':
	                echo zota_tbay_woocommerce_get_two_product_thumbnail( $size );
	                break;
	            
	            default:
	                echo woocommerce_get_product_thumbnail( $size );
	                break;
	        }

		}
		public function excerpt_product_variable() {
			global $product;
			if( $product->is_type('variable') ) {
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
				add_action( 'woocommerce_before_single_variation', 'woocommerce_template_single_excerpt', 10 );
			}
		}

		public function woo_remove_product_tabs( $tabs ) {

			unset( $tabs['description'] );      	
			unset( $tabs['additional_information'] ); 

			return $tabs;
		}

		
		

		/*stock*/
		public function zota_tbay_total_sales() {
			global $product;
			$total_sales        = $product->get_total_sales();
			?>
				<div class="sold"><?php esc_html_e(' Sold: ', 'zota'); ?><?php echo esc_html($total_sales) ?></div>
			<?php
				
		}
		public function the_woocommerce_variable() {
			global $product;

	        $active = apply_filters( 'zota_enable_variation_selector', 10,2 );

	        if( $product->is_type( 'variable' ) && class_exists( 'Woo_Variation_Swatches' ) && $active  ) {
	            ?>
	            	<?php echo zota_swatches_list(); ?>
	            <?php

	        }
		}

		public function shop_load_more() {
            $params = [
                'columns' => (int)($_POST['columns'] ?? 4),
                'layout' => $_POST['layout'] ?? '',
                'number' => (int)($_POST['number'] ?? 8),
                'type' => $_POST['type'] ?? 'featured_product',
                'paged' => (int)($_POST['paged'] ?? 1),
                'category' => $_POST['category'] ?? ''
            ];
            $transient_key = 'zota_shop_load_more_' . md5(json_encode($params));
            $cached = get_transient($transient_key);
            if ($cached !== false) {
                wp_send_json($cached);
            }

            $offset = $params['number'] * 3;
            $number_load = $params['columns'] * 3;
            $loop = $this->get_product_query($params['category'], $params['type'], $params['paged'], $number_load, $offset);

            ob_start();
            while ($loop->have_posts()) {
                $loop->the_post();
                wc_get_template('content-products.php', ['product_item' => 'inner', 'columns' => $params['columns']]);
            }
            wp_reset_postdata();
            $posts = ob_get_clean();

            $result = [
                'check' => $params['paged'] < $loop->max_num_pages && $number_load <= $loop->post_count,
                'posts' => $posts
            ];
            set_transient($transient_key, $result, HOUR_IN_SECONDS);
            wp_send_json($result);
        }

		private function get_product_query($category, $type, $paged, $number_load, $offset) {
            $args = [
                'post_type' => 'product',
                'posts_per_page' => $number_load,
                'paged' => $paged,
                'offset' => $offset
            ];
            if ($category === '-1') {
                return zota_tbay_get_products('', $type, $paged, $number_load, '', '', $offset);
            }
            $categories = strpos($category, ',') !== false ? explode(',', $category) : [$category];
            return zota_tbay_get_products($categories, $type, $paged, $number_load, '', '', $offset);
        }
		
		public function shop_category_image() {
			$active = apply_filters( 'zota_woo_pro_des_image', 10,2 );

			if( !$active ) return;

			if ( is_product_category() && !is_search()  ){
				global $wp_query;
				$cat = $wp_query->get_queried_object();
				$thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
				$image = wp_get_attachment_url( $thumbnail_id );
				if ( $image ) {
					echo '<img src="' . esc_url($image) . '" alt="' . esc_attr( $cat->name) . '" />';
				}
			}
		}

		function shop_remove_des_image() { 
			$active = apply_filters( 'zota_woo_pro_des_image', 10,2 );
			
	
		    if ( !$active ) {
				remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
				remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
		   }
		}

		public function product_per_page_query( $q ) {
			$default            = zota_tbay_get_config('number_products_per_page');
			$product_per_page   = zota_woocommerce_get_fillter('product_per_page',$default);
			if ( function_exists( 'woocommerce_products_will_display' ) && $q->is_main_query() ) :
				$q->set( 'posts_per_page', $product_per_page );
			endif;
		}

		public function pagination_more_post_ajax() {
			// prepare our arguments for the query
	        $args = json_decode( stripslashes( $_POST['query'] ), true );
	        $args['paged'] = $_POST['page'] + 1; // we need next page to be loaded
	        $args['post_status'] = 'publish';

	        $shown_des = true;
	     
	        // it is always better to use WP_Query but not here
	        query_posts( $args );
	      
	        if( have_posts() ) :
	     
	            while( have_posts() ): the_post();
	     
	                wc_get_template( 'content-product.php', array('shown_des' => $shown_des));

	     
	            endwhile;

	            wp_reset_postdata();
	     
	        endif;
	        die;
		}

		public function ajax_load_more_grid_product() {
			// prepare our arguments for the query
			$args = json_decode( stripslashes( $_POST['query'] ), true );
			
			$this->ajax_order_by_query( $args['orderby'], $args['order'] ); 
	     
	        // it is always better to use WP_Query but not here
	        query_posts( $args );

	        $list = 'grid';
	      
	        if( have_posts() ) :
	     
	            while( have_posts() ): the_post();
	     
	                wc_get_template( 'content-product.php', array('list' => $list));

	     
	            endwhile;

	            wp_reset_postdata();
	     
	        endif;
	        die;
		}

		public function shop_product_top_archive() {
			if( is_shop() ){
	            $sidebar_id = 'product-top-archive';

	            if( is_active_sidebar($sidebar_id) ) { ?> 
	                <aside id="sidebar-top-archive" class="sidebar top-archive-content">
	                	<?php dynamic_sidebar($sidebar_id); ?>
	            	</aside>
	            <?php }
	        }
		}

		public function shop_product_bottom_archive() {
			if( !is_product() && !is_search() ){
	            $sidebar_id = 'product-bottom-archive';

	            if( is_active_sidebar($sidebar_id) ) { ?> 
	                <aside id="sidebar-bottom-archive" class="sidebar bottom-archive-content">
	                	<?php dynamic_sidebar($sidebar_id); ?>
	            	</aside>
	            <?php }
	        }
		}

		public function product_layout_style() {
			$type = 'inner';

	        return $type;
		}

		public function ajax_load_more_list_product() {
			 // prepare our arguments for the query
			$args = json_decode( stripslashes( $_POST['query'] ), true );

			$this->ajax_order_by_query( $args['orderby'], $args['order'] ); 

            if (isset($_GET['paged'])) {
                $args['paged'] = intval($_GET['paged']);
			}
			
	        query_posts( $args );

	        $list = 'list'; 
	     
	        if( have_posts() ) :
	     
	            while( have_posts() ): the_post();
	     
	                wc_get_template( 'content-product.php', array('list' => $list));

	     
	            endwhile;

	            wp_reset_postdata();
	     
	        endif;
	        die;
		}

		public function ajax_order_by_query( $orderby, $order ) {
			// it is always better to use WP_Query but not here
			$WC_Query_class = new WC_Query(); 

			switch ( $orderby ) {
				case 'id':
					$args['orderby'] = 'ID';
					break;
				case 'menu_order':
					$args['orderby'] = 'menu_order title';
					break;
				case 'title':
					$args['orderby'] = 'title';
					$args['order']   = ( 'DESC' === $order ) ? 'DESC' : 'ASC';
					break;
				case 'relevance':
					$args['orderby'] = 'relevance';
					$args['order']   = 'DESC';
					break;
				case 'rand':
					$args['orderby'] = 'rand'; // @codingStandardsIgnoreLine
					break;
				case 'date':
					$args['orderby'] = 'date ID';
					$args['order']   = ( 'ASC' === $order ) ? 'ASC' : 'DESC';
					break;
				case 'price':
				case 'price-desc':
					$callback = 'DESC' === $order ? 'order_by_price_desc_post_clauses' : 'order_by_price_asc_post_clauses';
					add_filter( 'posts_clauses', array( $WC_Query_class, $callback ) );
					break;
				case 'popularity':
					add_filter( 'posts_clauses', array( $WC_Query_class, 'order_by_popularity_post_clauses' ) );
					break;
				case 'rating':
					add_filter( 'posts_clauses', array( $WC_Query_class, 'order_by_rating_post_clauses' ) );
					break;
			}

		}

		public function show_product_subcategories( $loop_html = '' ) {
			if ( wc_get_loop_prop( 'is_shortcode' ) && ! WC_Template_Loader::in_content_filter() ) {
	            return $loop_html;
	        }

	        $display_type = woocommerce_get_loop_display_mode();

	        // If displaying categories, append to the loop.
	        if ( 'subcategories' === $display_type || 'both' === $display_type ) {
	            ob_start();
	            woocommerce_output_product_categories( array(
	                'parent_id' => is_product_category() ? get_queried_object_id() : 0,
	            ) );
	            $loop_html .= ob_get_clean();

	            if ( 'subcategories' === $display_type ) {
	                wc_set_loop_prop( 'total', 0 );

	                // This removes pagination and products from display for themes not using wc_get_loop_prop in their product loops.  @todo Remove in future major version.
	                global $wp_query;

	                if ( $wp_query->is_main_query() ) {
	                    $wp_query->post_count    = 0;
	                    $wp_query->max_num_pages = 0;
	                }
	            }
	        }

	        return $loop_html;
		}

		public function title_product_archives_active( ) {
	 		$active = zota_tbay_get_config('title_product_archives', false);

	        $active = (isset($_GET['title_product_archives'])) ? (boolean)$_GET['title_product_archives'] : (boolean)$active;

	        return $active;
		}

		public function remove_title_product_archives_active() {
			$active = $this->title_product_archives_active();

	        $active = ( is_search() ) ? true : $active; 

	        return $active;
		}


		public function display_modes_active() {
	 		$active = zota_tbay_get_config('enable_display_mode', true);

	        $active = (isset($_GET['enable_display_mode'])) ? (boolean)$_GET['enable_display_mode'] : (boolean)$active;

	        return $active;
		}


		public function the_brands_the_name() {
			if( !zota_tbay_get_config('enable_brand', false) ) return;

	        $brand = '';
	        if( class_exists( 'YITH_WCBR' ) ) {

	            global $product;

	            $terms = wp_get_post_terms($product->get_id(),'yith_product_brand');

	            if($terms && defined( 'YITH_WCBR' ) && YITH_WCBR) {

	                $brand  .= '<ul class="show-brand">';
					$space = ',';
					$numItems = count($terms);
					$i = 0;
					
	                foreach ($terms as $term) {
	                    if(++$i === $numItems) {
							$space ='';
						};
	                    $name = $term->name;
	                    $url = get_term_link( $term->slug, 'yith_product_brand' );

	                    $brand  .= '<li><a href="'. esc_url($url) .'">'. esc_html($name).$space.'</a></li>';

	                }

	                $brand  .= '</ul>';
	            }
	        
	        }

	        echo  trim($brand);
		}

		private static function get_config($key, $default = '') {
            if (!isset(self::$config_cache[$key])) {
                self::$config_cache[$key] = zota_tbay_get_config($key, $default);
            }
            return self::$config_cache[$key];
        }


	}
endif;


if ( !function_exists('zota_shop_wooCommerce') ) {
	function zota_shop_wooCommerce() { 
		return Zota_Shop_WooCommerce::getInstance();
	}
	zota_shop_wooCommerce();
}