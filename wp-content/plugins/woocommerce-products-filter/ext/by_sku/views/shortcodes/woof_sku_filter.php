<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<div data-css-class="woof_sku_search_container" class="woof_sku_search_container woof_container woof_container_woof_sku <?php echo esc_attr(WOOF_HELPER::generate_container_css_classes('by_sku')) ?>">
    <div class="woof_container_overlay_item"></div>
    <div class="woof_container_inner">
        <?php
        $woof_sku = '';
        $request = $this->get_request_data();

        if (isset($request['woof_sku'])) {
            $woof_sku = $request['woof_sku'];
        }
        //+++
        if (!isset($placeholder)) {
            $p = esc_html__('enter a product sku here ...', 'woocommerce-products-filter');
        }


        if (isset($this->settings['by_sku']['placeholder']) AND !isset($placeholder)) {
            if (!empty($this->settings['by_sku']['placeholder'])) {
                $p = $this->settings['by_sku']['placeholder'];
                $p = WOOF_HELPER::wpml_translate(null, $p);
                $p = esc_html__($p, 'woocommerce-products-filter');
            }


            if ($this->settings['by_sku']['placeholder'] == 'none') {
                $p = '';
            }
        }
        //***
        $unique_id = uniqid('woof_sku_search_');

        //***

        $autocomplete = isset(woof()->settings['by_sku']['autocomplete']) ? intval(woof()->settings['by_sku']['autocomplete']) : 0;
        $autocomplete_items = isset(woof()->settings['by_sku']['autocomplete_items']) ? intval(woof()->settings['by_sku']['autocomplete_items']) : 10;
        $reset_behavior = isset(woof()->settings['by_sku']['reset_behavior']) ? intval(woof()->settings['by_sku']['reset_behavior']) : 1;
        ?>

        <div class="woof_show_sku_search_container" style="<?php
        if (isset(woof()->settings['by_sku']['image']) && !empty(woof()->settings['by_sku']['image'])) {
            echo '--woof_sku_search_go_bg: url(' . esc_url(woof()->settings['by_sku']['image']) . ')';
        }
        ?>" data-autocomplete="<?php echo intval($autocomplete) ?>" data-autocomplete_items="<?php echo intval($autocomplete_items) ?>">
            <a href="javascript:void(0);" data-uid="<?php echo esc_attr($unique_id) ?>" class="woof_sku_search_go <?php echo esc_attr($unique_id) ?>"></a>
            <a href="javascript:void(0);" data-uid="<?php echo esc_attr($unique_id) ?>" class="woof_sku_search_reset <?php echo esc_attr($unique_id) ?>" data-reset_behavior="<?php echo intval($reset_behavior) ?>"><span class="dashicons dashicons-dismiss"></span></a>
            <input type="search" class="woof_show_sku_search <?php echo esc_attr($unique_id) ?>" id="<?php echo esc_attr($unique_id) ?>" data-uid="<?php echo esc_attr($unique_id) ?>" placeholder="<?php echo esc_attr(isset($placeholder) ? $placeholder : $p) ?>" name="woof_sku" value="<?php echo esc_attr($woof_sku) ?>" />
            <?php if (isset($this->settings['by_sku']['notes_for_customer']) AND !empty($this->settings['by_sku']['notes_for_customer'])): ?>
                <span class="woof_sku_notes_for_customer"><?php echo wp_kses_post(wp_unslash($this->settings['by_sku']['notes_for_customer'])); ?></span>
            <?php endif; ?>
			<input type="hidden" class="woof_sku_search_nonce" value="<?php echo esc_attr(wp_create_nonce('sku_search_nonce'))?>">
        </div>

    </div>
</div>