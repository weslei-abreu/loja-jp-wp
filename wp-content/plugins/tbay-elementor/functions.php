<?php
/**
 * functions for Thembay Elementor
 *
 * @package    tbay-elementor
 * @author     Team Thembays <thembayteam@gmail.com >
 * @license    GNU General Public License, version 3
 * @copyright  2015-2016 Thembay Elementor
 */

if ( !function_exists('tbay_elementor_register_widgets_theme') ) {
    function tbay_elementor_register_widgets_theme() {
          
        $widgets = array(
                        'Tbay_Widget_Twitter', 
                        'Tbay_Widget_Facebook_Like_Box', 
                        'Tbay_Widget_Widget_Instagram', 
                        'Tbay_Widget_Banner_Image',
                        'Tbay_Widget_Custom_Menu',
                        'Tbay_Widget_List_Categories',
                        'Tbay_Widget_Popular_Post',
                        'Tbay_Widget_Popup_Newsletter',
                        'Tbay_Widget_Posts',
                        'Tbay_Widget_Recent_Comment',
                        'Tbay_Widget_Recent_Post',
                        'Tbay_Widget_Single_Image',
                        'Tbay_Widget_Socials',
                        'Tbay_Widget_Top_Rate',
                        'Tbay_Widget_Featured_Video'
                    );

        if( defined( 'YITH_WCBR' ) && YITH_WCBR ) {
          array_push($widgets,'Tbay_Widget_Yith_Brand_Images');
        }        

        if ( class_exists( 'WooCommerce' ) ) {
          array_push($widgets,'Tbay_Widget_Woo_Carousel');
        }

        $widgets = apply_filters( 'tbay_elementor_register_widgets_theme', $widgets);


        foreach ($widgets as $widget) {
            if(class_exists($widget)) {
                register_widget( $widget );
            }   
        }
                    
    }

    add_action( 'widgets_init', 'tbay_elementor_register_widgets_theme', 30 );
}

if( ! function_exists( 'tbay_elementor_register_post_types' ) ) {
    function tbay_elementor_register_post_types() {

        $types = array('footer', 'megamenu', 'header');

        $post_types = apply_filters( 'tbay_elementor_register_post_types', $types);
        if ( !empty($post_types) ) {
            foreach ($post_types as $post_type) {
                if ( file_exists( TBAY_ELEMENTOR_DIR . 'classes/post-types/'.$post_type.'.php' ) ) {
                    require TBAY_ELEMENTOR_DIR . 'classes/post-types/'.$post_type.'.php';
                }
            }
        }
    }
}

if( ! function_exists( 'tbay_elementor_widget_init' ) ) {
    function tbay_elementor_widget_init() {
    	$widgets = apply_filters( 'tbay_elementor_register_widgets', array() );
    	if ( !empty($widgets) ) {
    		foreach ($widgets as $widget) {
    			if ( file_exists( TBAY_ELEMENTOR_DIR . 'classes/widgets/'.$widget.'.php' ) ) {
    				require TBAY_ELEMENTOR_DIR . 'classes/widgets/'.$widget.'.php';
    			}
    		}
    	}
    }
}

if( ! function_exists( 'tbay_elementor_get_widget_locate' ) ) {
    function tbay_elementor_get_widget_locate( $name, $plugin_dir = TBAY_ELEMENTOR_DIR ) {
    	$template = '';
    	
    	// Child theme
    	if ( ! $template && ! empty( $name ) && file_exists( get_stylesheet_directory() . "/widgets/{$name}" ) ) {
    		$template = get_stylesheet_directory() . "/widgets/{$name}";
    	}

    	// Original theme
    	if ( ! $template && ! empty( $name ) && file_exists( get_template_directory() . "/widgets/{$name}" ) ) {
    		$template = get_template_directory() . "/widgets/{$name}";
    	}

    	// Plugin
    	if ( ! $template && ! empty( $name ) && file_exists( $plugin_dir . "/templates/widgets/{$name}" ) ) {
    		$template = $plugin_dir . "/templates/widgets/{$name}";
    	}

    	// Nothing found
    	if ( empty( $template ) ) {
    		throw new Exception( "Template /templates/widgets/{$name} in plugin dir {$plugin_dir} not found." );
    	}

    	return $template;
    }
}

if( ! function_exists( 'tbay_elementor_display_svg_image' ) ) {
    function tbay_elementor_display_svg_image( $url, $class = '', $wrap_as_img = true, $attachment_id = null ) {
        if ( ! empty( $url ) && is_string( $url ) ) {

            // we try to inline svgs
            if ( substr( $url, - 4 ) === '.svg' ) {

                //first let's see if we have an attachment and inline it in the safest way - with readfile
                //include is a little dangerous because if one has short_open_tags active, the svg header that starts with <? will be seen as PHP code
                if ( ! empty( $attachment_id ) && false !== @readfile( get_attached_file( $attachment_id ) ) ) {
                    //all good
                } elseif ( false !== ( $svg_code = get_transient( md5( $url ) ) ) ) {
                    //now try to get the svg code from cache
                    echo $svg_code;
                } else {

                    //if not let's get the file contents using WP_Filesystem
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );

                    WP_Filesystem();

                    global $wp_filesystem;
                    
                    $svg_code = $wp_filesystem->get_contents( $url );

                    if ( ! empty( $svg_code ) ) {
                        set_transient( md5( $url ), $svg_code, 12 * HOUR_IN_SECONDS );

                        echo $svg_code;
                    }
                }

            } elseif ( $wrap_as_img ) {

                if ( ! empty( $class ) ) {
                    $class = ' class="' . $class . '"';
                }

                echo '<img src="' . $url . '"' . $class . ' alt="" />';

            } else {
                echo $url;
            }
        }
    }
}


if( ! function_exists( 'tbay_elementor_get_file_contents' ) ) {
    function tbay_elementor_get_file_contents($url, $use_include_path, $context) {
    	return @file_get_contents($url, false, $context);
    }
}

if( ! function_exists( 'tbay_elementor_remove_image_srcset' ) ) {
    function tbay_elementor_remove_image_srcset( $media_item ) {
        add_filter( 'wp_calculate_image_srcset', '__return_false' );
    }
    add_action( 'init', 'tbay_elementor_remove_image_srcset', 10 );
}


if( ! function_exists( 'tbay_elementor_product_add_metaboxes' ) ) {
    add_action( 'add_meta_boxes', 'tbay_elementor_product_add_metaboxes', 50 );
    function tbay_elementor_product_add_metaboxes() {

        if( function_exists( 'tbay_size_guide_metabox_output' ) ) {
            //Add metaboxes size guide to product
            add_meta_box( 'woocommerce-product-size-guide-images', esc_html__( 'Product Size Guide (Only Variable product)', 'tbay-elementor' ), 'tbay_size_guide_metabox_output', 'product', 'side', 'low' );
        }       

        if( function_exists( 'tbay_swatch_attribute_template' ) ) {
            add_meta_box( 'woocommerce-product-swatch-attribute', esc_html__( 'Swatch attribute to display', 'tbay-elementor' ), 'tbay_swatch_attribute_template', 'product', 'side' );    
        }    

        if( function_exists( 'tbay_single_select_single_layout_template' ) ) {
            add_meta_box( 'woocommerce-product-single-layout', esc_html__( 'Select Single Product Layout', 'tbay-elementor' ), 'tbay_single_select_single_layout_template', 'product', 'side' );  
        }

    }
}

if ( !function_exists( 'tbay_elementor_fix_customize_image_wvs_support' ) ) {
    function tbay_elementor_fix_customize_image_wvs_support(){
        remove_filter( 'pre_update_option_woocommerce_thumbnail_image_width', 'wvs_clear_transient' );
        remove_filter( 'pre_update_option_woocommerce_thumbnail_cropping', 'wvs_clear_transient' );
    }
    add_action('admin_init', 'tbay_elementor_fix_customize_image_wvs_support', 10);
}