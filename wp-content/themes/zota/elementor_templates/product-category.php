<?php 
/**
 * Templates Name: Elementor
 * Widget: Products Category
 */
$category =  $cat_operator = $product_type = $limit = $orderby = $order = '';
extract( $settings );

if (empty($settings['category'])) {
    return;
}

$layout_type = $settings['layout_type'];
$this->settings_layout();

$transient_name = 'zota_product_category_' . md5($this->get_id()) . '_' . md5(serialize($settings));
$loop = get_transient($transient_name);

if( !$loop ) {
    /** Get Query Products */
    $loop = zota_get_query_products($category,  $cat_operator, $product_type, $limit, $orderby, $order);
    set_transient($transient_name, $loop, DAY_IN_SECONDS);
}

$this->add_render_attribute('row', 'class', ['products']);

$attr_row = $this->get_render_attribute_string('row');
?>

<div <?php echo $this->get_render_attribute_string('wrapper'); ?>>
    <?php $this->render_element_heading(); ?>

    <?php if( !empty($feature_image['id']) ) : ?>

    	<div class="product-category-content row">

    		<div class="col-md-3 d-md-block d-sm-none d-xs-none">
    			<?php $this->render_item_image($settings) ?>
    		</div>    		

    		<div class="col-md-9">
    			    <?php wc_get_template( 'layout-products/layout-products.php' , array( 'loop' => $loop, 'product_style' => $product_style, 'attr_row' => $attr_row, 'size_image' => $product_image_size) ); ?>
    		</div>

    	</div>
 
	<?php  else : ?>

	<?php wc_get_template( 'layout-products/layout-products.php' , array( 'loop' => $loop, 'product_style' => $product_style, 'attr_row' => $attr_row, 'size_image' => $product_image_size) ); ?>

	<?php endif; ?>



    <?php $this->render_item_button($settings)?>
</div>