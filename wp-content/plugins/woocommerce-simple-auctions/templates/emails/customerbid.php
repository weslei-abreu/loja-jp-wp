<?php
/**
 * Customer bid notification email
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

$product_data = wc_get_product(  $product_id );

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php printf( wp_kses_post( __( "Hi there. You have placed bid for item <a href='%s'>%s</a>", 'wc_simple_auctions' ) ),get_permalink($product_id), $product_data->get_title(), wc_price($product_data->get_curent_bid())); ?></p>

<p><?php 

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( isset( $additional_content ) && $additional_content ) {
    echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

?></p>

<?php do_action('woocommerce_email_footer', $email); ?>