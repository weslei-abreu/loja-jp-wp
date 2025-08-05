<?php 
/**
 * Templates Name: Elementor
 * Widget: Products
 */

extract( $settings );

if( isset($limit) && !((bool) $limit) ) return;

$this->settings_layout();

/** Get Query Products with Transient */
$transient_name = 'zota_products_loop_' . md5($this->get_id()) . '_' . md5(serialize($settings));
$loop = get_transient($transient_name);

if (false === $loop) {
    $loop = zota_get_query_products($categories, $cat_operator, $product_type, $limit, $orderby, $order);
    set_transient($transient_name, $loop, DAY_IN_SECONDS);
}

$this->add_render_attribute('row', 'class', ['products', 'product-style-'. $product_style]);

$attr_row = $this->get_render_attribute_string('row');
?>

<div <?php echo $this->get_render_attribute_string('wrapper'); ?>>

    <?php $this->render_element_heading(); ?>

    <?php wc_get_template( 'layout-products/layout-products.php' , array( 'loop' => $loop, 'product_style' => $product_style, 'attr_row' => $attr_row, 'size_image' => $product_image_size) ); ?>
    <?php $this->render_item_button(); ?>
</div>