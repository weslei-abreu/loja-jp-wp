<?php 
/**
 * Templates Name: Elementor
 * Widget: Products Tabs
 */

extract( $settings );

$this->settings_layout();
 
$_id = zota_tbay_random_key();

$navigation = apply_filters('zota_navigation', 'style-nav-2');
$this->add_render_attribute('wrapper', 'class', $navigation); 


if( $ajax_tabs === 'yes' ) {
    $this->add_render_attribute('row', 'class', ['products']);
    $attr_row = $this->get_render_attribute_string('row'); 

    $json = array(
        'categories'                    => $categories,
        'cat_operator'                  => $cat_operator,
        'limit'                         => $limit,
        'orderby'                       => $orderby,
        'order'                         => $order,
        'product_style'                 => $product_style,
        'attr_row'                      => $attr_row,
        'product_image_size'            => $product_image_size,
    );

    $encoded_settings  = wp_json_encode( $json );
    $tabs_data = 'data-atts="'. esc_attr( $encoded_settings ) .'"';

    $this->add_render_attribute('wrapper', 'class', 'ajax-active'); 
} else {
    $tabs_data = ''; 
}

if(  isset($show_banner_image) && $show_banner_image === 'yes' && !empty($media_image_01['id']) || !empty($media_image_02['id']) ) {
    $this->add_render_attribute('wrapper', 'class', 'tbay-element-banner'); 
}

?>
<div <?php echo $this->get_render_attribute_string('wrapper'); ?>>
    
    <div class="wrapper-heading-tab">
        <?php $this->render_element_heading(); ?>
        <ul class="product-tabs-title tabs-list nav nav-tabs" <?php echo trim($tabs_data); ?>>
            <?php $_count = 0;?>
            <?php foreach ($list_product_tabs as $key) {
                $active = ($_count==0)? 'active':'';

                $product_tabs = $key['product_tabs'];
                $title = $this->get_title_product_type($product_tabs);
                if(!empty($key['product_tabs_title']) ) {
                    $title = $key['product_tabs_title'];
                }
                $this->render_product_tabs($product_tabs,$_id,$title,$active); 
                $_count++;   
            }
            ?>
        </ul>
    </div>
   
    
    <div class="content-product-category-tab">
        <?php 
            if( zota_tbay_get_theme() === 'auto-part' ) {
                $this->render_content_banner();
            }
        
        ?>
        
        <div class="tbay-addon-content tab-content woocommerce">
            <?php $_count = 0;?>
            <?php foreach ($list_product_tabs as $key) {
                    $tab_active = ($_count==0)? 'active active-content current':'';
                    $product_tabs = $key['product_tabs'];
                    ?>
                    <div class="tab-pane <?php echo esc_attr($tab_active); ?>" id="<?php echo esc_attr($product_tabs).'-'.$_id; ?>">
                    <?php
                    if( $_count === 0 || $ajax_tabs !== 'yes' ) {
                        $this->render_content_tab($product_tabs, $tab_active, $_id);
                    }
                    $_count++;
                    ?>
                    </div>
                    <?php
                }
            ?>
        </div>
    </div>

</div>