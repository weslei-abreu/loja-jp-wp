<?php 

if ( !function_exists('zota_tbay_private_size_image_setup') ) {
	function zota_tbay_private_size_image_setup() {
		if( zota_tbay_get_global_config('config_media',false) ) return;

		// Post Thumbnails Size
		set_post_thumbnail_size(371	, 247, true); // Unlimited height, soft crop
		update_option('thumbnail_size_w', 370);
		update_option('thumbnail_size_h', 247);						

		update_option('medium_size_w', 540);
		update_option('medium_size_h', 360);

		update_option('large_size_w', 770);
		update_option('large_size_h', 514);

	}
	add_action( 'after_setup_theme', 'zota_tbay_private_size_image_setup' );
}