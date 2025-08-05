<?php
if ( ! defined( 'ABSPATH' ) || !zota_is_Woocommerce_activated() ) {
	exit;
}

if ( ! class_exists( 'Zota_Cart' ) ) :


	class Zota_Cart  {

		static $instance;
        private static $config_cache = [];

		public static function getInstance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Zota_Cart ) ) {
				self::$instance = new Zota_Cart();
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

			/*Cart modal*/
			add_action( 'wp_ajax_zota_add_to_cart_product', array( $this, 'woocommerce_cart_modal'), 10 );
			add_action( 'wp_ajax_nopriv_zota_add_to_cart_product', array( $this, 'woocommerce_cart_modal'), 10 );
			add_action( 'wp_footer', array( $this, 'add_to_cart_modal_html'), 20 );

			add_filter( 'zota_cart_position', array( $this, 'woocommerce_cart_position'), 10 ,1 );  

			add_filter( 'body_class', array( $this, 'body_classes_cart_postion' ), 40, 1 );

			/*Mobile add to cart message html*/
			add_filter( 'wc_add_to_cart_message_html', array( $this, 'add_to_cart_message_html_mobile'), 10, 1 );

			/*Show Add to Cart on mobile*/
			add_filter( 'zota_show_cart_mobile', array( $this, 'show_cart_mobile'), 10, 1 );
			add_filter( 'body_class', array( $this, 'body_classes_show_cart_mobile'), 10, 1 );
		}

		public function add_to_cart_modal_html() {
			if( is_account_page() || is_checkout() || ( function_exists('is_vendor_dashboard') && is_vendor_dashboard() ) ) return;        
		    ?>
		    <div id="tbay-cart-modal" tabindex="-1" role="dialog" aria-hidden="true">
		        <div class="modal-dialog modal-lg">
		            <div class="modal-content">
		                <div class="modal-body">
		                    <div class="modal-body-content"></div>
		                </div>
		            </div>
		        </div>
		    </div>
		    <?php    
		}


		public function woocommerce_cart_modal() {
            if (!isset($_GET['product_id']) || !isset($_GET['product_qty'])) {
                wp_send_json_error('Invalid request');
            }
            $product_id = (int)$_GET['product_id'];
            $product_qty = (int)$_GET['product_qty'];
            wc_get_template('content-product-cart-modal.php', ['product_id' => $product_id, 'product_qty' => $product_qty]);
            wp_die();
        }

		public function woocommerce_cart_position() {
            if (apply_filters('zota_check_cart_position_is_mobile', wp_is_mobile())) {
                return 'right';
            }

            $position = self::get_config('woo_mini_cart_position', 'popup');
            $position = isset($_GET['ajax_cart']) ? $_GET['ajax_cart'] : $position;

            $valid_positions = ['popup', 'left', 'right', 'no-popup'];
            return in_array($position, $valid_positions) ? $position : 'popup';
        }


		public function body_classes_cart_postion( $classes ) {
			$position = apply_filters( 'zota_cart_position', 10,2 ); 

	        $class = ( isset($_GET['ajax_cart']) ) ? 'ajax_cart_'.$_GET['ajax_cart'] : 'ajax_cart_'.$position;

	        $classes[] = trim($class);

	        return $classes;
		}

		public function add_to_cart_message_html_mobile($message) {
            if (isset($_REQUEST['zota_buy_now']) && $_REQUEST['zota_buy_now']) {
                return '';
            }
            return (wp_is_mobile() && !self::get_config('enable_buy_now', false)) ? '' : $message;
        }

		public function show_cart_mobile() {
            $active = self::get_config('enable_add_cart_mobile', false);
            return isset($_GET['add_cart_mobile']) ? $_GET['add_cart_mobile'] : $active;
        }

		public function body_classes_show_cart_mobile( $classes ) {
	 		$class = '';
	        $active = apply_filters( 'zota_show_cart_mobile', 10,2 );
	        if( isset($active) && $active ) {  
	            $class = 'tbay-show-cart-mobile';
	        }

	        $classes[] = trim($class);

	        return $classes;
		}

		private static function get_config($key, $default = '') {
            if (!isset(self::$config_cache[$key])) {
                self::$config_cache[$key] = zota_tbay_get_config($key, $default);
            }
            return self::$config_cache[$key];
        }

	}
endif;


if ( !function_exists('zota_cart') ) {
	function zota_cart() { 
		return Zota_Cart::getInstance();
	}
	zota_cart();
}