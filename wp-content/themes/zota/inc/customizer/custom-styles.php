<?php

//convert hex to rgb
if ( !function_exists ('zota_tbay_getbowtied_hex2rgb') ) {
	function zota_tbay_getbowtied_hex2rgb($hex) {
		$hex = str_replace("#", "", $hex);
		
		if(strlen($hex) == 3) {
			$r = hexdec(substr($hex,0,1).substr($hex,0,1));
			$g = hexdec(substr($hex,1,1).substr($hex,1,1));
			$b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} else {
			$r = hexdec(substr($hex,0,2));
			$g = hexdec(substr($hex,2,2));
			$b = hexdec(substr($hex,4,2));
		}
		$rgb = array($r, $g, $b);
		return implode(",", $rgb); // returns the rgb values separated by commas
		//return $rgb; // returns an array with the rgb values
	}
}


if ( !function_exists ('zota_tbay_color_lightens_darkens') ) {
	/**
	 * Lightens/darkens a given colour (hex format), returning the altered colour in hex format.7
	 * @param str $hex Colour as hexadecimal (with or without hash);
	 * @percent float $percent Decimal ( 0.2 = lighten by 20%(), -0.4 = darken by 40%() )
	 * @return str Lightened/Darkend colour as hexadecimal (with hash);
	 */
	function zota_tbay_color_lightens_darkens( $hex, $percent ) {
		
		if( empty($hex) ) return $hex;

		// validate hex string
		
		$hex = preg_replace( '/[^0-9a-f]/i', '', $hex );
		$new_hex = '#';
		
		if ( strlen( $hex ) < 6 ) {
			$hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
		}
		
		// convert to decimal and change luminosity
		for ($i = 0; $i < 3; $i++) {
			$dec = substr( $hex, $i*2, 2 );
			$dec = intval( $dec, 16 );
			$dec = min( max( 0, $dec + $dec * $percent ), 255 ); 
			$new_hex .= str_pad( sprintf("%02x", $dec) , 2, 0, STR_PAD_LEFT );
		}	
		
		return $new_hex;
	}
}

if ( !function_exists ('zota_tbay_default_theme_primary_color') ) {
	function zota_tbay_default_theme_primary_color() {

		$active_theme = zota_tbay_get_theme();

		$theme_variable = array();

		$theme_variable['header_mobile_bg'] 	= '#ffffff'; 

        $theme_variable['header_mobile_color'] 	= '#000000';

        $theme_variable['bg_buy_now'] 	= '#ffca3a';

        $theme_variable['color_buy_now'] 	= '#1d1d1d';

		switch ($active_theme) {
			case 'beauty':
				$theme_variable['main_color'] 			= '#D7A484';
				break;
			case 'electronics':
				$theme_variable['main_color'] 			= '#0D6DD7';
				break;
			case 'fashion':
				$theme_variable['main_color'] 			= '#1D1D1D';
				break;
			case 'furniture':
				$theme_variable['main_color'] 			= '#F38816';
				break;
			case 'hand-made':
				$theme_variable['main_color'] 			= '#5C9963';
				break;
			case 'auto-part':
					$theme_variable['main_color'] 			= '#DD2400';
					break;
			case 'organic':
				$theme_variable['main_color'] 			= '#5C9963';
				break;
			case 'bag':
					$theme_variable['main_color'] 			= '#fc153d';
					break;
		}


		return apply_filters( 'zota_get_default_theme_color', $theme_variable);
	}
}

if ( !function_exists ('zota_tbay_default_theme_primary_fonts') ) {
	function zota_tbay_default_theme_primary_fonts() {

		$active_theme = zota_tbay_get_theme();

		$theme_variable = array();

		switch ($active_theme) {
			case 'beauty':
				$theme_variable['main_font'] 			= 'Poppins, sans-serif';
				$theme_variable['main_font_second'] 	= 'Marcellus, sans-serif';
				$theme_variable['font_second_enable'] 	= true;
				break;
			case 'electronics':
				$theme_variable['main_font'] 			= 'Rubik, sans-serif';
				$theme_variable['font_second_enable'] 	= false;
				break;
			case 'fashion':
				$theme_variable['main_font'] 			= 'Poppins, sans-serif';
				$theme_variable['main_font_second'] 	= 'Marcellus, sans-serif';
				$theme_variable['font_second_enable'] 	= true;
				break;
			case 'furniture':
				$theme_variable['main_font'] 			= 'Poppins, sans-serif';
				$theme_variable['main_font_second'] 	= 'Jost, sans-serif';
				$theme_variable['font_second_enable'] 	= true;
				break;
			case 'hand-made':
				$theme_variable['main_font'] 			= 'Poppins, sans-serif';
				$theme_variable['main_font_second'] 	= 'Dancing Script, sans-serif';
				$theme_variable['font_second_enable'] 	= true;
				break;
			case 'auto-part':
				$theme_variable['main_font'] 			= 'Poppins, sans-serif';
				$theme_variable['main_font_second'] 	= 'Rubik, sans-serif';
				$theme_variable['font_second_enable'] 	= true;
				break;
			case 'organic':
				$theme_variable['main_font'] 			= 'Poppins, sans-serif';
				$theme_variable['main_font_second'] 	= 'Noto Serif, sans-serif';
				$theme_variable['font_second_enable'] 	= true;
				break;
			case 'bag':
				$theme_variable['main_font'] 			= 'Poppins, sans-serif';
				$theme_variable['main_font_second'] 	= 'Marcellus, sans-serif';
				$theme_variable['font_second_enable'] 	= true;
				break;
		}


		return apply_filters( 'zota_get_default_theme_fonts', $theme_variable);
	}
}

if (!function_exists('zota_tbay_check_empty_customize')) {
    function zota_check_empty_customize($option, $default){
		echo !empty($option) ? esc_html($option) : esc_html($default);
	}
}

if (!function_exists('zota_tbay_theme_primary_color')) {
    function zota_tbay_theme_primary_color()
    {
		$default 					= zota_tbay_default_theme_primary_color();

        $main_color   				= zota_tbay_get_config(('main_color'),$default['main_color']);
        $header_mobile_bg  	 		= zota_tbay_get_config( ('header_mobile_bg'),$default['header_mobile_bg']);
        $header_mobile_color  	 	= zota_tbay_get_config( ('header_mobile_color'),$default['header_mobile_color']);

        $bg_buy_now 		  		= zota_tbay_get_config( ('bg_buy_now' ), $default['bg_buy_now']);
		$color_buy_now 		  		= zota_tbay_get_config( ('color_buy_now'), $default['color_buy_now']);

		/*Theme Color*/
		?>
		:root {
			--tb-theme-color: <?php zota_check_empty_customize( $main_color, $default['main_color'] ); ?>;
			--tb-theme-color-hover: <?php zota_check_empty_customize( zota_tbay_color_lightens_darkens($main_color, -0.05), zota_tbay_color_lightens_darkens($default['main_color'], -0.05) ); ?>;
			--tb-header-mobile-bg: <?php zota_check_empty_customize( $header_mobile_bg, $default['header_mobile_bg'] ); ?>;
			--tb-header-mobile-color: <?php zota_check_empty_customize( $header_mobile_color, $default['header_mobile_color'] ) ?>;
			--tb-theme-color-buy-now: <?php zota_check_empty_customize( $color_buy_now, $default['color_buy_now'] ) ?>;
			--tb-theme-bg-buy-now: <?php zota_check_empty_customize( $bg_buy_now, $default['bg_buy_now'] ) ?>;
			--tb-theme-bg-buy-now-hover: <?php zota_check_empty_customize( zota_tbay_color_lightens_darkens($bg_buy_now, -0.05), zota_tbay_color_lightens_darkens($default['bg_buy_now'], -0.05) ) ?>;	
		} 
		<?php
    }
}


if ( !function_exists ('zota_tbay_custom_styles') ) {
	function zota_tbay_custom_styles()

	{

		ob_start();

		zota_tbay_theme_primary_color();

		$default_fonts 		= zota_tbay_default_theme_primary_fonts();


	/*End Theme Color*/
	if (defined('TBAY_ELEMENTOR_ACTIVED')) {
			$logo_img_width        		= zota_tbay_get_config('logo_img_width');
			$logo_padding        		= zota_tbay_get_config('logo_padding');

			$logo_img_width_mobile 		= zota_tbay_get_config('logo_img_width_mobile');
			$logo_mobile_padding 		= zota_tbay_get_config('logo_mobile_padding');

			$checkout_img_width 		= zota_tbay_get_config('checkout_img_width');

			$custom_css 			= zota_tbay_get_config('custom_css');
			$css_desktop 			= zota_tbay_get_config('css_desktop');
			$css_tablet 			= zota_tbay_get_config('css_tablet');
			$css_wide_mobile 		= zota_tbay_get_config('css_wide_mobile');
			$css_mobile         	= zota_tbay_get_config('css_mobile');

			$show_typography        = (bool) zota_tbay_get_config('show_typography', false);

            if ($show_typography) {
                $font_source 			= zota_tbay_get_config('font_source');
                $primary_font 			= zota_tbay_get_config('main_font')['font-family'];
                $main_google_font_face = zota_tbay_get_config('main_google_font_face');
                $main_custom_font_face = zota_tbay_get_config('main_custom_font_face');

                $second_font					= zota_tbay_get_config('main_font_second')['font-family'];
                $main_second_google_font_face 	= zota_tbay_get_config('main_second_google_font_face');
                $main_second_custom_font_face 	= zota_tbay_get_config('main_second_custom_font_face');

                if ($font_source  == "2" && $main_google_font_face) {
                    $primary_font = $main_google_font_face;
                    $second_font = $main_second_google_font_face;
                } elseif ($font_source  == "3" && $main_custom_font_face) {
                    $primary_font = $main_custom_font_face;
                    $second_font = $main_second_custom_font_face;
                } ?>
				:root {
					--tb-text-primary-font: <?php zota_check_empty_customize( $primary_font, $default_fonts['main_font'] ); ?>;

					<?php if ($default_fonts['font_second_enable']) : ?>
						--tb-text-second-font: <?php zota_check_empty_customize( $second_font, $default_fonts['main_font_second'] ); ?>;
					<?php endif; ?>
				}  
				<?php
            } else {
				?>
				:root { 
					--tb-text-primary-font: <?php echo esc_html($default_fonts['main_font']); ?>;

					<?php if ($default_fonts['font_second_enable']) : ?>
						--tb-text-second-font: <?php echo esc_html($default_fonts['main_font_second']); ?>;
					<?php endif; ?>
                }
				<?php
			}

			?>
			/* Theme Options Styles */
			

				<?php if ($logo_img_width != "") : ?>
				.site-header .logo img {
					max-width: <?php echo esc_html($logo_img_width); ?>px;
				} 
				<?php endif; ?>

				<?php if ($checkout_img_width != "") : ?>
				.checkout-logo img {
					max-width: <?php echo esc_html($checkout_img_width); ?>px;
				} 
				<?php endif; ?>

				<?php if ($logo_padding != "") : ?>
				.site-header .logo img {

					<?php if ( !empty($logo_padding['padding-top']) ) : ?>
						padding-top: <?php echo esc_html($logo_padding['padding-top']); ?>;
					<?php endif; ?>

					<?php if ( !empty($logo_padding['padding-right']) ) : ?>
						padding-right: <?php echo esc_html($logo_padding['padding-right']); ?>;
					<?php endif; ?>
					
					<?php if ( !empty($logo_padding['padding-bottom']) ) : ?>
						padding-bottom: <?php echo esc_html($logo_padding['padding-bottom']); ?>;
					<?php endif; ?>

					<?php if ( !empty($logo_padding['padding-left']) ) : ?>
						padding-left: <?php echo esc_html($logo_padding['padding-left']); ?>;
					<?php endif; ?>

				}
				<?php endif; ?> 

				@media (max-width: 1199px) {

					<?php if ( $logo_img_width_mobile != "" ) : ?>
					/* Limit logo image height for mobile according to mobile header height */
					.mobile-logo a img {
						width: <?php echo esc_html($logo_img_width_mobile); ?>px;
					}     
					<?php endif; ?>       

					<?php if ( $logo_mobile_padding['padding-top'] != "" || $logo_mobile_padding['padding-right'] || $logo_mobile_padding['padding-bottom'] || $logo_mobile_padding['padding-left'] ) : ?>
					.mobile-logo a img {

						<?php if ( !empty($logo_mobile_padding['padding-top']) ) : ?>
							padding-top: <?php echo esc_html($logo_mobile_padding['padding-top']); ?>;
						<?php endif; ?>

						<?php if ( !empty($logo_mobile_padding['padding-right']) ) : ?>
							padding-right: <?php echo esc_html($logo_mobile_padding['padding-right']); ?>;
						<?php endif; ?>

						<?php if ( !empty($logo_mobile_padding['padding-bottom']) ) : ?>
							padding-bottom: <?php echo esc_html($logo_mobile_padding['padding-bottom']); ?>;
						<?php endif; ?>

						<?php if ( !empty($logo_mobile_padding['padding-left']) ) : ?>
							padding-left: <?php echo esc_html($logo_mobile_padding['padding-left']); ?>;
						<?php endif; ?>
					
					}
					<?php endif; ?>
				}

				@media screen and (max-width: 782px) {
					html body.admin-bar{
						top: -46px !important;
						position: relative;
					}
				}

				/* Custom CSS */
				<?php
				if ($custom_css != '') {
					echo trim($custom_css);
				}
			if ($css_desktop != '') {
				echo '@media (min-width: 1024px) { ' . ($css_desktop) . ' }';
			}
			if ($css_tablet != '') {
				echo '@media (min-width: 768px) and (max-width: 1023px) {' . ($css_tablet) . ' }';
			}
			if ($css_wide_mobile != '') {
				echo '@media (min-width: 481px) and (max-width: 767px) { ' . ($css_wide_mobile) . ' }';
			}
			if ($css_mobile != '') {
				echo '@media (max-width: 480px) { ' . ($css_mobile) . ' }';
			}
	}

		$content = ob_get_clean();
		$content = str_replace(array("\r\n", "\r"), "\n", $content);
		$lines = explode("\n", $content);
		$new_lines = array();
		foreach ($lines as $i => $line) {
			if (!empty($line)) {
				$new_lines[] = trim($line);
			} 
		}

		$custom_css = implode($new_lines);

		wp_enqueue_style( 'zota-style', ZOTA_THEME_DIR . '/style.css', array(), '1.0' );

		wp_add_inline_style( 'zota-style', $custom_css );
	}

	add_action( 'wp_enqueue_scripts', 'zota_tbay_custom_styles', 200 ); 
}