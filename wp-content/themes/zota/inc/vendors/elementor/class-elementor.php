<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Zota_Elementor_Addons {
	public function __construct() {
        $this->include_control_customize_widgets();
        $this->include_render_customize_widgets();

		add_action( 'elementor/elements/categories_registered', array( $this, 'add_category' ) );

		add_action( 'elementor/widgets/register', array( $this, 'include_widgets' ) );

		add_action( 'wp', [ $this, 'regeister_scripts_frontend' ] );

        // frontend
        // Register widget scripts
        add_action('elementor/frontend/after_register_scripts', [ $this, 'frontend_after_register_scripts' ]);
        add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'frontend_after_enqueue_scripts' ] );

        add_action('elementor/editor/after_enqueue_styles', [$this, 'enqueue_editor_icons'], 99);

        // editor 
        add_action('elementor/editor/after_register_scripts', [ $this, 'editor_after_register_scripts' ]);
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'editor_after_enqueue_scripts'] );

    
        add_action( 'widgets_init', array( $this, 'register_wp_widgets' ) );

        add_action('elementor/widgets/register', array( $this, 'unregister_elementor_widgets' ), 15 );
    }  
  
    public function editor_after_register_scripts() {
        if( zota_is_remove_scripts() ) return;

        $suffix = (zota_tbay_get_config('minified_js', false)) ? '.min' : ZOTA_MIN_JS;
        // /*slick jquery*/
        wp_register_script( 'slick', ZOTA_SCRIPTS . '/slick' . $suffix . '.js', array(), '1.0.0', true );
        wp_register_script( 'zota-custom-slick', ZOTA_SCRIPTS . '/custom-slick' . $suffix . '.js', array( ), ZOTA_THEME_VERSION, true ); 

        wp_register_script( 'zota-script',  ZOTA_SCRIPTS . '/functions' . $suffix . '.js', array(),  ZOTA_THEME_VERSION,  true );


        wp_register_script( 'popper', ZOTA_SCRIPTS . '/popper' . $suffix . '.js', array( ), '1.12.9', true );       
        wp_register_script( 'bootstrap', ZOTA_SCRIPTS . '/bootstrap' . $suffix . '.js', array( 'popper' ), '4.0.0', true );
  
        wp_register_script( 'before-after-image', ZOTA_SCRIPTS . '/cndk.beforeafter' . $suffix . '.js', array('zota-script'), '0.0.2', true );     
        wp_register_style( 'before-after-image', ZOTA_STYLES . '/cndk.beforeafter.css', array(), '0.0.2' );

        /*Treeview menu*/
        wp_register_script( 'jquery-treeview', ZOTA_SCRIPTS . '/jquery.treeview' . $suffix . '.js', array( ), '1.4.0', true ); 
       
        // Add js Sumoselect version 3.0.2
        wp_register_style('sumoselect', ZOTA_STYLES . '/sumoselect.css', array(), '1.0.0', 'all');
        wp_register_script('jquery-sumoselect', ZOTA_SCRIPTS . '/jquery.sumoselect' . $suffix . '.js', array(), '3.0.2', TRUE); 
 
    }    

    public function frontend_after_enqueue_scripts() {
    }  

    public function editor_after_enqueue_scripts() { 

    } 

    public function enqueue_editor_icons() {

        wp_enqueue_style( 'simple-line-icons', ZOTA_STYLES . '/simple-line-icons.css', array(), '2.4.0' );
        wp_enqueue_style( 'font-awesome', ZOTA_STYLES . '/font-awesome.css', array(), '5.10.2' );
        wp_enqueue_style( 'zota-font-tbay-custom', ZOTA_STYLES . '/font-tbay-custom.css', array(), '1.0.0' );
        wp_enqueue_style( 'material-design-iconic-font', ZOTA_STYLES . '/material-design-iconic-font.css', array(), '2.2.0' ); 

        if ( zota_elementor_is_edit_mode() || zota_elementor_is_preview_page() || zota_elementor_is_preview_mode() ) {
            wp_enqueue_style( 'zota-elementor-editor', ZOTA_STYLES . '/elementor-editor.css', array(), ZOTA_THEME_VERSION );
        }
    }


    /**
     * @internal Used as a callback
     */
    public function frontend_after_register_scripts() {
        $this->editor_after_register_scripts();
    }


	public function register_wp_widgets() {

	}

	function regeister_scripts_frontend() {
		
    }


    public function add_category( $elements_manager ) {
        $elements_manager->add_category(
            'zota-elements',
            array(
                'title' => esc_html__('Zota Elements', 'zota'),
                'icon'  => 'fa fa-plug',
            )
        );
    }

    /**
     * @param $widgets_manager Elementor\Widgets_Manager
     */
    public function include_widgets($widgets_manager) {
        $this->include_abstract_widgets($widgets_manager);
        $this->include_general_widgets($widgets_manager);
        $this->include_header_widgets($widgets_manager);
        $this->include_woocommerce_widgets($widgets_manager);
	} 


    /**
     * Widgets General Theme
     */
    public function include_general_widgets($widgets_manager) {
        $elements = zota_elementor_general_widgets();

        foreach ( $elements as $file ) {
            $path   = ZOTA_ELEMENTOR .'/elements/general/' . $file . '.php';
            if (file_exists($path)) {
                require_once $path;
            }
        }

    }    

    /**
     * Widgets WooComerce Theme
     */
    public function include_woocommerce_widgets($widgets_manager) {
        if( !zota_is_Woocommerce_activated() ) return;

        $woo_elements = zota_elementor_woocommerce_widgets();

        foreach ( $woo_elements as $file ) {
            $path   = ZOTA_ELEMENTOR .'/elements/woocommerce/' . $file . '.php';
            if( file_exists( $path ) ) {
                require_once $path;
            }
        }

    }    

    /**
     * Widgets Header Theme
     */
    public function include_header_widgets($widgets_manager) {
        $elements = zota_elementor_header_widgets();

        foreach ( $elements as $file ) {
            $path   = ZOTA_ELEMENTOR .'/elements/header/' . $file . '.php';
            if( file_exists( $path ) ) {
                require_once $path;
            }
        }


    }


    /**
     * Widgets Abstract Theme
     */
    public function include_abstract_widgets($widgets_manager) {
        $abstracts = array(
            'image',
            'base',
            'responsive',
            'carousel',
        );

        $abstracts = apply_filters( 'zota_abstract_elements_array', $abstracts );

        foreach ( $abstracts as $file ) {
            $path   = ZOTA_ELEMENTOR .'/abstract/' . $file . '.php';
            if( file_exists( $path ) ) {
                require_once $path;
            }
        } 
    }

    public function include_control_customize_widgets() {
        $widgets = array(
            'sticky-header',
            'column',
            'column-border', 
            'section-stretch-row',
            'settings-layout',
            'global-typography',
        );

        $widgets = apply_filters( 'zota_customize_elements_array', $widgets );
 
        foreach ( $widgets as $file ) {
            $control   = ZOTA_ELEMENTOR .'/elements/customize/controls/' . $file . '.php';
            if( file_exists( $control ) ) {
                require_once $control;
            }            
        } 
    }    

    public function include_render_customize_widgets() {
        $widgets = array(
            'sticky-header',
            'column-border',
        );

        $widgets = apply_filters( 'zota_customize_elements_array', $widgets );
 
        foreach ( $widgets as $file ) {
            $render    = ZOTA_ELEMENTOR .'/elements/customize/render/' . $file . '.php';         
            if( file_exists( $render ) ) {
                require_once $render;
            }
        } 
    }

    public function unregister_elementor_widgets($widgets_manager){
 
        $elementor_widget_blacklist = array(
            'zota_custom_menu',
            'zota_list_categories',
            'zota_popular_post',
            'zota_popup_newsletter',
            'zota_posts',
            'zota_recent_comment',
            'zota_recent_post',
            'zota_single_image',
            'zota_socials_widget',
            'zota_featured_video_widget',
            'zota_template_elementor',
            'zota_top_rate_widget',
            'zota_woo_carousel',
            'zota_product_brand'
        );

        foreach($elementor_widget_blacklist as $widget_name){
            $widgets_manager->unregister('wp-widget-'. $widget_name);
        }

    }
}

new Zota_Elementor_Addons();

