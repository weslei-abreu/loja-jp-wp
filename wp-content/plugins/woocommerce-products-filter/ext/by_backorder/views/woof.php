<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');


$woof_ext_backorder_label = apply_filters('woof_ext_custom_title_by_backorder', __('Exclude: On backorder', 'woocommerce-products-filter'));
if (isset(woof()->settings['by_backorder']) AND woof()->settings['by_backorder']['show']) {
    ?>
    <div data-css-class="woof_checkbox_onbackorder_container" class="woof_checkbox_onbackorder_container woof_container woof_container_backorder <?php echo esc_attr(WOOF_HELPER::generate_container_css_classes('by_backorder')) ?>">
        <div class="woof_container_overlay_item"></div>
        <div class="woof_container_inner">
            <?php
            if (isset(woof()->settings['by_backorder']['view']) && woof()->settings['by_backorder']['view'] === 'switcher') {
                $unique_id = uniqid('woof_checkbox_by_backorder-');
                ?>
                <div class="switcher23-container">

                    <input type="checkbox" class="woof_checkbox_onbackorder_as_switcher switcher23" id="<?php echo esc_attr($unique_id) ?>" name="backorder" value="0" <?php checked('onbackorder', woof()->is_isset_in_request_data('backorder') ? 'onbackorder' : '', true) ?> />

                    <label for="<?php echo esc_attr($unique_id) ?>" class="switcher23-toggle">
                        <div class="switcher23-title2"><?php esc_html_e($woof_ext_backorder_label) ?></div>
                        <span></span>                    
                    </label>
                </div>
                <?php
            } else {
                ?>
                <input type="checkbox" class="woof_checkbox_onbackorder" id="woof_checkbox_onbackorder" name="backorder" value="0" <?php checked('onbackorder', woof()->is_isset_in_request_data('backorder') ? 'onbackorder' : '', true) ?> />&nbsp;&nbsp;<label for="woof_checkbox_onbackorder"><?php esc_html_e($woof_ext_backorder_label) ?></label><br />
                <?php
            }
            ?>
        </div>
    </div>
    <?php
}


