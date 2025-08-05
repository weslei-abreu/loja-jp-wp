<?php

if (!zota_is_woo_variation_swatches_pro()) {
    return;
}

if (!function_exists('zota_quantity_swatches_pro_field_archive')) {
    function zota_quantity_swatches_pro_field_archive()
    {
        global $product;
        if (zota_is_quantity_field_archive()) {
            woocommerce_quantity_input(['min_value' => 1, 'max_value' => $product->backorders_allowed() ? '' : $product->get_stock_quantity()]);
        }
    }
}

if (!function_exists('zota_variation_swatches_pro_group_button')) {
    add_action('zota_woo_before_shop_loop_item_caption', 'zota_variation_swatches_pro_group_button', 5);
    function zota_variation_swatches_pro_group_button()
    {
        $class_active = '';

        if (zota_woocommerce_quantity_mode_active()) {
            $class_active .= 'quantity-group-btn';

            if (zota_is_quantity_field_archive()) {
                $class_active .= ' active';
            }
        } else {
            $class_active .= 'woo-swatches-pro-btn';
        }

        echo '<div class="'.esc_attr($class_active).'">';

        if (zota_woocommerce_quantity_mode_active()) {
            zota_quantity_swatches_pro_field_archive();
        }

        woocommerce_template_loop_add_to_cart();
        echo '</div>';
    }
}

if (class_exists('Woo_Variation_Swatches_Pro_Archive_Page')) {
    remove_action('woocommerce_init', [Woo_Variation_Swatches_Pro_Archive_Page::instance(), 'enable_swatches'], 1);
}

if (!function_exists('zota_swatches_pro_remove_hook')) {
    function zota_swatches_pro_remove_hook()
    {
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
        remove_action('zota_woocommerce_group_add_to_cart', 'woocommerce_template_loop_add_to_cart', 40);
    }
    add_action('woocommerce_before_shop_loop_item', 'zota_swatches_pro_remove_hook', 5);
}

if (!function_exists('zota_variation_enable_swatches')) {
    add_action('woocommerce_init', 'zota_variation_enable_swatches', 5);
    function zota_variation_enable_swatches()
    {
        $enable = wc_string_to_bool(woo_variation_swatches()->get_option('show_on_archive', 'yes'));
        $position = sanitize_text_field(woo_variation_swatches()->get_option('archive_swatches_position', 'after'));

        if (!$enable) {
            return;
        }

        if ('after' === $position) {
            add_action('zota_woo_after_shop_loop_item_caption', [Woo_Variation_Swatches_Pro_Archive_Page::instance(), 'after_shop_loop_item'], 30);
            add_action('zota_woo_list_caption_right', [Woo_Variation_Swatches_Pro_Archive_Page::instance(), 'after_shop_loop_item'], 30);
        } else {
            add_action('zota_woo_after_shop_loop_item_caption', [Woo_Variation_Swatches_Pro_Archive_Page::instance(), 'after_shop_loop_item'], 7);
            add_action('zota_woo_list_caption_right', [Woo_Variation_Swatches_Pro_Archive_Page::instance(), 'after_shop_loop_item'], 7);
        }
    }
}
