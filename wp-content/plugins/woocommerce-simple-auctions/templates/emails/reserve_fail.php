<?php
/**
 * Admin auction failed email
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

$product_data = wc_get_product(  $product_id );

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php printf( wp_kses_post( __( "Sorry. The auction for <a href='%s'>%s</a> has failed because it did not make the reserve price.", 'wc_simple_auctions' ) ),get_permalink($product_id), $product_data->get_title()); ?></p>

<p><?php 

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
    echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

?></p>

<?php do_action('woocommerce_email_footer', $email); ?>