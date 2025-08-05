<?php

if( defined('TBAY_ELEMENTOR_ACTIVED') && !TBAY_ELEMENTOR_ACTIVED) return;

/**
 * Class zota_redux_framework'
 */
class zota_redux_framework {
    function __construct() {
        add_action( 'widgets_init', array( $this, 'widgets_init' ) );
    }

    /**
     * Register widget area.
     *
     * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
     */
    public function widgets_init() {

        register_sidebar( array(
            'name'          => esc_html__( 'Newsletter Popup', 'zota' ),
            'id'            => 'newsletter-popup',
            'description'   => esc_html__( 'Add widgets here to appear in your site.', 'zota' ),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ) );    


        register_sidebar( array(
            'name'          => esc_html__( 'Blog Archive Sidebar', 'zota' ),
            'id'            => 'blog-archive-sidebar',
            'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'zota' ),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ) );                
        register_sidebar( array(
            'name'          => esc_html__( 'Blog Single Sidebar', 'zota' ),
            'id'            => 'blog-single-sidebar',
            'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'zota' ),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ) );   

    }

}

return new zota_redux_framework();