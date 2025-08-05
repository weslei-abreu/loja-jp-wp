<?php

if (!function_exists('zota_clear_header_transient')) {
	add_action('save_post_tbay_header', 'zota_clear_header_transient');
	function zota_clear_header_transient($post_id) {
		delete_transient('zota_header_layouts');
	}
}

if (!function_exists('zota_clear_footer_transient')) {
	add_action('save_post_tbay_footer', 'zota_clear_footer_transient');
	function zota_clear_footer_transient($post_id) {
		delete_transient('zota_footer_layouts');
	}
}


if ( !function_exists('zota_clear_custom_tab_transient') ) {
	add_action('save_post_tbay_customtab', 'zota_clear_custom_tab_transient');
	function zota_clear_custom_tab_transient($post_id) {
		delete_transient('zota_wc_custom_tab_options');
	}
}

if (! function_exists('zota_clear_megamenu_cache')) {
    function zota_clear_megamenu_cache($post_id) {
        set_transient('zota_megamenu_cache_clear', true, 60);
    }
    add_action('save_post_tbay_megamenu', 'zota_clear_megamenu_cache');
    add_action('delete_post_tbay_megamenu', 'zota_clear_megamenu_cache');
    add_action('wp_trash_post_tbay_megamenu', 'zota_clear_megamenu_cache');
}

if (! function_exists('zota_clear_woocommerce_tags_transient')) {
    function zota_clear_woocommerce_tags_transient($term_id, $tt_id = '', $taxonomy = '') {
        delete_transient('zota_woocommerce_tags');
    }
    add_action('created_product_tag', 'zota_clear_woocommerce_tags_transient', 10, 3);
    add_action('edited_product_tag', 'zota_clear_woocommerce_tags_transient', 10, 3);
    add_action('delete_product_tag', 'zota_clear_woocommerce_tags_transient', 10, 2);
}

if ( ! function_exists( 'zota_clear_available_pages_transient' ) ) {
    function zota_clear_available_pages_transient($post_id) {
        delete_transient('zota_available_pages');
    }
    add_action('save_post_page', 'zota_clear_available_pages_transient');
    add_action('delete_post_page', 'zota_clear_available_pages_transient');
}

if (!function_exists('zota_clear_on_sale_products_transient')) {
    /**
     * Clear on-sale products transient when product stock, post, or sale price changes.
     *
     * @param int|WC_Product $product Product ID or object (depending on hook)
     */
    function zota_clear_on_sale_products_transient() {
        delete_transient('zota_on_sale_products');
    }

    // Hook into product post save
    add_action('save_post_product', 'zota_clear_on_sale_products_transient');
    add_action('delete_post_product', 'zota_clear_on_sale_products_transient');
    add_action('edit_post_product', 'zota_clear_on_sale_products_transient');
}

if (! function_exists('zota_clear_available_menus_transient')) {
    function zota_clear_available_menus_transient() {
        delete_transient('zota_available_menus');
        global $sitepress;
        $current_lang = apply_filters('wpml_current_language', null);
        delete_transient('zota_menus_wpml_' . $current_lang);
    }
    // Hook into menu creation
    add_action('wp_create_nav_menu', 'zota_clear_available_menus_transient');
    // Hook into menu deletion
    add_action('wp_delete_nav_menu', 'zota_clear_available_menus_transient');
}

if (! function_exists('zota_clear_product_categories_transient')) {
    // Function to delete transients for product categories
    function zota_clear_product_categories_transient($term_id, $tt_id, $taxonomy) {
        delete_transient('zota_product_categories_all');
    }
    add_action('created_product_cat', 'zota_clear_product_categories_transient', 10, 3);
    add_action('edited_product_cat', 'zota_clear_product_categories_transient', 10, 3);
    add_action('delete_product_cat', 'zota_clear_product_categories_transient', 10, 2);
}

if (! function_exists('zota_clear_menu_account_transients')) {
    function zota_clear_menu_account_transients($menu_id) {
        $menus = wp_get_nav_menus();
        foreach ($menus as $menu) {
            if ($menu->term_id == $menu_id) {
                $transient_key = 'zota_menu_account_' . md5($menu->slug); // Cần widget ID cụ thể
                delete_transient($transient_key);
            }
        }
    }
    add_action('wp_update_nav_menu', 'zota_clear_menu_account_transients');
}