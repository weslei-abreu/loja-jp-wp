<?php 

if(!class_exists('WooCommerce')) return;

if ( ! function_exists( 'zota_woocommerce_setup_size_image' ) ) {
    add_action( 'after_setup_theme', 'zota_woocommerce_setup_size_image' );
    function zota_woocommerce_setup_size_image() {
        $thumbnail_width = 300;
        $main_image_width = 580;
        $cropping_custom_width = 1;
        $cropping_custom_height = 1.37;


        // Image sizes
        update_option( 'woocommerce_thumbnail_image_width', $thumbnail_width );
        update_option( 'woocommerce_single_image_width', $main_image_width ); 

        update_option( 'woocommerce_thumbnail_cropping', 'custom' );
        update_option( 'woocommerce_thumbnail_cropping_custom_width', $cropping_custom_width );
        update_option( 'woocommerce_thumbnail_cropping_custom_height', $cropping_custom_height );
    }
}

if(zota_tbay_get_global_config('config_media',false)) {
    remove_action( 'after_setup_theme', 'zota_woocommerce_setup_size_image' );
}
