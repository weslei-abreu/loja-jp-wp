<?php

// Remove default breadcrumb
add_filter( 'woocommerce_breadcrumb_defaults', 'zota_tbay_woocommerce_breadcrumb_defaults' );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
add_action( 'zota_woo_template_main_before', 'woocommerce_breadcrumb', 20 ); 

/**
 * Product Rating
 *
 * @see zota_woocommerce_loop_item_rating()
 */
add_action( 'zota_woocommerce_loop_item_rating', 'woocommerce_template_loop_rating', 10 );

// Remove Default Sidebars
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 ); 



/**
 * Product Add to cart.
 *
 * @see woocommerce_template_single_add_to_cart()
 * @see woocommerce_simple_add_to_cart()
 * @see woocommerce_grouped_add_to_cart()
 * @see woocommerce_variable_add_to_cart()
 * @see woocommerce_external_add_to_cart()
 * @see woocommerce_single_variation()
 * @see woocommerce_single_variation_add_to_cart_button()
 */
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
add_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
add_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );
add_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
add_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
add_action( 'woocommerce_single_variation', 'woocommerce_single_variation', 10 );
add_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );

/**Fix duppitor image on elementor pro **/
if ( ! function_exists( 'zota_remove_shop_loop_item_title' ) ) {
    add_action( 'zota_woocommerce_before_shop_list_item', 'zota_remove_shop_loop_item_title', 10 ); 
    add_action( 'zota_woocommerce_before_product_block_grid', 'zota_remove_shop_loop_item_title', 10 ); 
    function zota_remove_shop_loop_item_title() {
        remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 ); 
        remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
        remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
        remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );

        remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);

        remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
        remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
    }
}

/**
 * Product Vertical
 *
 * @see woocommerce_after_shop_loop_item_vertical_title()
 * @see woocommerce_after_shop_loop_item_vertical_price()
 */

add_action( 'woocommerce_after_shop_loop_item_vertical_title', 'woocommerce_template_loop_price', 15 );
add_action( 'woocommerce_after_shop_loop_item_vertical_price', 'woocommerce_template_loop_rating', 10 );


/**
 * Product Grid
 *
 */
add_action( 'zota_woo_before_shop_loop_item_caption', 'zota_woocommerce_quantity_mode_group_button', 5);
add_action( 'zota_woo_list_caption_right', 'zota_woocommerce_add_quantity_mode_list', 5);
/**
 * Group Button Grid
 *
 * @see zota_woocommerce_group_buttons hook
 */
add_action( 'zota_woocommerce_group_buttons', 'zota_the_quick_view', 10, 1);
add_action( 'zota_woocommerce_group_buttons', 'zota_the_yith_compare', 20, 1);
add_action( 'zota_woocommerce_group_buttons', 'zota_the_yith_wishlist', 30, 1);
add_action( 'zota_woocommerce_group_buttons', 'woocommerce_template_loop_add_to_cart', 40, 1);
add_action( 'zota_woocommerce_group_add_to_cart', 'woocommerce_template_loop_add_to_cart', 40, 1);

/**
 * Product List
 *
 */

add_action( 'zota_woo_list_caption_left', 'woocommerce_template_loop_rating', 5 );
add_action( 'zota_woo_list_caption_right', 'woocommerce_template_loop_price', 5 );
add_action( 'zota_woo_list_caption_right', 'woocommerce_template_loop_add_to_cart', 10 );

/**Page Cart**/
remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );


/**
 *
 * @see woocommerce_template_single_excerpt()
 */
add_action('zota_shop_list_sort_description','woocommerce_template_single_excerpt', 5);