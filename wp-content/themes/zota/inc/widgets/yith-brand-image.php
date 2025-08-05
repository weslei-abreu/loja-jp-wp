<?php
if( !defined('TBAY_ELEMENTOR_ACTIVED') ) return;

class Tbay_Widget_Yith_Brand_Images extends Tbay_Widget {
    public function __construct() {
        parent::__construct(
            'zota_product_brand',
            esc_html__('Zota Product Brand Images', 'zota'),
            array( 'description' => esc_html__( 'Show YITH product brand images(Only applicable to product single pages)', 'zota' ), )
        );
        $this->widgetName = 'zota_product_brand';
    }
 
    public function getTemplate() {
        $this->template = 'product-brand-image.php';
    }

    public function widget( $args, $instance ) {
        $this->display($args, $instance);
    }
    
    public function form( $instance ) {


    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();

        return $instance;

    }
}