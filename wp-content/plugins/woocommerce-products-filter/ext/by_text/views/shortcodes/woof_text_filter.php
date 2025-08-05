<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');
global $WOOCS;
$search_id = uniqid('woof_txt_search');
?>
<div data-css-class="woof_text_search_container" class="woof_text_search_container woof_container woof_container_woof_text <?php echo esc_attr(WOOF_HELPER::generate_container_css_classes('by_text')) ?>">
    <div class="woof_container_overlay_item"></div>
    <div class="woof_container_inner">
        <a href="javascript:void(0);"  class="woof_text_search_go" rel=nofollow ></a>
        <label class="woof_wcga_label_hide" for="<?php echo esc_attr($search_id) ?>"><?php esc_html_e('Text search', 'woocommerce-products-filter') ?></label>
        <?php
        $woof_text = '';
        $request = woof()->get_request_data();

        if (isset($request['woof_text'])) {			
            $woof_text = stripslashes($request['woof_text']);
			$woof_text = str_replace('&amp;', '&', $woof_text);
        }
        ?>
        <input type="search" class="woof_husky_txt-input" id="<?php echo esc_attr($search_id) ?>"
        <?php
        foreach ($data as $key => $value) {
            if ($key === 'placeholder') {
                ?> placeholder =" <?php esc_html_e($value) ?>"
                       <?php
                   } elseif (!in_array($key, array('notes_for_customer'))) {
                       echo esc_attr(" data-" . $key) . '="' . esc_html($value) . '"';
                   }
               }
               ?>			   
               value="<?php echo esc_html($woof_text) ?>" autocomplete="off" />

        <?php if (isset($data['notes_for_customer']) AND!empty($data['notes_for_customer'])): ?>
            <span class="woof_text_notes_for_customer"><?php echo wp_kses_post(wp_unslash($data['notes_for_customer'])); ?></span>
        <?php endif; ?>   		
		<input type="hidden" class="woof_text_search_nonce" value="<?php echo esc_attr(wp_create_nonce('text_search_nonce'))?>">
	</div>
</div>