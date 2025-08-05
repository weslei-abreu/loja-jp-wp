<?php   
	$class_top_bar 	=  '';

	$always_display_logo 			= zota_tbay_get_config('always_display_logo', false);
	if( !$always_display_logo && !apply_filters( 'zota_catalog_mode', 10,2 ) && zota_is_Woocommerce_activated() && (is_product() || is_cart() || is_checkout()) ) {
		$class_top_bar .= ' active-home-icon';
	}
?>
<div class="topbar-device-mobile d-xl-none clearfix <?php echo esc_attr( $class_top_bar ); ?>">

	<?php
		/**
		* zota_before_header_mobile hook
		*/
		do_action( 'zota_before_header_mobile' );

		/**
		* Hook: zota_header_mobile_content.
		*
		* @hooked zota_the_button_mobile_menu - 5
		* @hooked zota_the_logo_mobile - 10
		* @hooked zota_the_title_page_mobile - 10
		*/

		do_action( 'zota_header_mobile_content' );

		/**
		* zota_after_header_mobile hook
		
		* @hooked zota_the_search_header_mobile - 5
		*/		
		
		do_action( 'zota_after_header_mobile' );
	?>
</div>