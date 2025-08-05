<?php if ( ! defined('ZOTA_THEME_DIR')) exit('No direct script access allowed');
/**
 * Zota woocommerce Template Hooks
 *
 * Action/filter hooks used for Zota woocommerce functions/templates.
 *
 */


/**
 * Zota Header Mobile Content.
 *
 * @see zota_the_button_mobile_menu()
 * @see zota_the_logo_mobile()
 */
add_action( 'zota_header_mobile_content', 'zota_the_button_mobile_menu', 5 );
add_action( 'zota_header_mobile_content', 'zota_the_icon_home_page_mobile', 10 );
add_action( 'zota_header_mobile_content', 'zota_the_logo_mobile', 15 );
add_action( 'zota_header_mobile_content', 'zota_the_icon_mini_cart_header_mobile', 20 );


/**
 * Zota Header Mobile before content
 *
 * @see zota_the_hook_header_mobile_all_page
 */
add_action( 'zota_before_header_mobile', 'zota_the_hook_header_mobile_all_page', 5 );
add_action( 'zota_before_header_mobile', 'zota_the_hook_header_mobile_menu_all_page', 10 );
