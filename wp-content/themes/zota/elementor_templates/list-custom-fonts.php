<?php 
/**
 * Templates Name: List Custom Fonts
 * Widget: List Custom Fonts
 */

extract($settings);

$this->settings_layout();
?>

<div <?php echo $this->get_render_attribute_string('wrapper'); ?>>

    <?php $this->render_element_heading(); ?>

    <?php $this->render_element_content(); ?>
</div>