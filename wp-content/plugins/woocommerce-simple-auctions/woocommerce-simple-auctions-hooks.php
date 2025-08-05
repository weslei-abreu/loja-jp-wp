<?php
/**
 * WooCommerce Hooks
 *
 * Action / filter hooks used for WooCommerce functions/templates
 *
 * @author 		WooThemes
 * @category 	Core
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if ( ! is_admin() || defined('DOING_AJAX') ) {

	// Product Add to cart
	add_action( 'woocommerce_auction_add_to_cart', 'woocommerce_auction_add_to_cart', 30 );

	add_action( 'woocommerce_single_product_summary', 'woocommerce_auction_ajax_conteiner_start', 21 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_auction_condition', 23 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_auction_countdown', 24 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_auction_dates', 24 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_auction_reserve', 25 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_auction_sealed', 25 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_auction_max_bid', 25 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_auction_bid_form', 25 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_auction_ajax_conteiner_end', 27 );

	if ( get_option( 'simple_auctions_watchlists', 'yes' ) == 'yes' ) {

		add_action( 'woocommerce_single_product_summary', array( $this, 'add_watchlist_link' ), 26 );
		// this is only for watchlist shortcode ///
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'add_to_watchlist' ), 60 );

	}
	if ( is_user_logged_in() ) add_action( 'woocommerce_single_product_summary', 'woocommerce_auction_pay', 26 );
}
