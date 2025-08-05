<?php
/**
 * Customer buy now email
 *
 */

if (!defined('ABSPATH')) exit;  // Exit if accessed directly


$product_data = wc_get_product($product_id);


do_action(' woocommerce_email_header', $email_heading , $email); ?>

<p><?php printf(__("Hi there. Item that you are bidding for (%s) was sold for buy now price. Better luck next time! ", 'wc_simple_auctions'),  $product_data -> get_title()); ?></p>

<p><?php 

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( isset( $additional_content ) && $additional_content ) {
    echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

?></p>

<?php do_action('woocommerce_email_footer' , $email); ?>