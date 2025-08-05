<?php 

if ( !function_exists('zota_tbay_private_size_image_setup') ) {
	function zota_tbay_private_size_image_setup() {
		if( zota_tbay_get_global_config('config_media',false) ) return;

		// Post Thumbnails Size
		set_post_thumbnail_size(530	, 355, true); // Unlimited height, soft crop
		update_option('thumbnail_size_w', 530);
		update_option('thumbnail_size_h', 355);						

		update_option('medium_size_w', 600);
		update_option('medium_size_h', 400);

		update_option('large_size_w', 900);
		update_option('large_size_h', 600);

	}
	add_action( 'after_setup_theme', 'zota_tbay_private_size_image_setup' );
}

/**
 *  Include Load Google Front
 */

if ( !function_exists('zota_fonts_url') ) {
	function zota_fonts_url() {
 		/**
         * Load Google Front
         */

        $fonts_url = '';

        /* Translators: If there are characters in your language that are not
        * supported by Montserrat, translate this to 'off'. Do not translate
        * into your own language.
        */
        $Rubik       = _x( 'on', 'Rubik font: on or off', 'zota' );
     
        if ( 'off' !== $Rubik) {
            $font_families = array(); 
     
            if ( 'off' !== $Rubik ) {
                $font_families[] = 'Rubik:400,500,600,700';
            }           
     
            $query_args = array(
                'family' => rawurlencode( implode( '|', $font_families ) ),
                'subset' => urlencode( 'latin,latin-ext' ),
                'display' => urlencode( 'swap' ),
            ); 
            
            $protocol = is_ssl() ? 'https:' : 'http:';
            $fonts_url = add_query_arg( $query_args, $protocol .'//fonts.googleapis.com/css' );
        }
     
        return esc_url_raw( $fonts_url );
	}
}

if ( !function_exists('zota_tbay_fonts_url') ) {
	function zota_tbay_fonts_url() {  
		$protocol 		  = is_ssl() ? 'https:' : 'http:';
		$show_typography  = zota_tbay_get_config('show_typography', false);
		$font_source 	  = zota_tbay_get_config('font_source', "1");
		$font_google_code = zota_tbay_get_config('font_google_code');
		if( !$show_typography ) {
			wp_enqueue_style( 'zota-theme-fonts', zota_fonts_url(), array(), false );
		} else if ( $font_source == "2" && !empty($font_google_code) ) {
			wp_enqueue_style( 'zota-theme-fonts', $font_google_code, array(), false );
		}
	}
	add_action('wp_enqueue_scripts', 'zota_tbay_fonts_url');
}
