<?php 
/**
 * Templates Name: Elementor
 * Widget: Instagram Feed
 */
extract($settings);

$_id = zota_tbay_random_key();
$this->settings_layout();

$this->add_render_attribute('item', 'class', 'item');

$this->add_render_attribute('row', 'data-layout', $layout_type);

$row = $this->get_render_attribute_string('row');
?>

<div <?php echo $this->get_render_attribute_string('wrapper'); ?>>
    <?php $this->render_element_heading(); ?>

    <?php echo do_shortcode( '[instagram-feed feed="'. $select_feeds. '" tb-atts="yes" '. $row .' ]' ); ?>
</div>