<?php

/**
 * Class zota_setup_theme'
 */
class zota_setup_theme {
    function __construct() {
        add_action( 'after_setup_theme', array( $this, 'setup' ), 10 );

        add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ), 100 );
        add_action('wp_footer', array( $this, 'footer_scripts' ), 20 );
        add_action( 'widgets_init', array( $this, 'widgets_init' ) );
        add_filter( 'frontpage_template', array( $this, 'front_page_template' ) );

        /**Remove fonts scripts**/
        add_action('wp_enqueue_scripts', array( $this, 'remove_fonts_redux_url' ), 1000 );

        add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_styles' ), 1000 );
        add_action( 'login_enqueue_scripts', array( $this, 'load_admin_login_styles' ), 1000 );


        add_action( 'after_switch_theme', array( $this, 'add_cpt_support'), 10 );

        add_filter('sbi_use_theme_templates', array( $this, 'instagram_use_theme_templates'), 10, 1 );

        add_action('mvx_frontend_enqueue_scripts', array( $this, 'add_mvx_frontend_enqueue_scripts' ), 10);

        add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'add_editor_scripts'), 10 );
    }

    /**
     * Enqueue scripts and styles.
     */
    public function add_scripts() {

        if( zota_is_remove_scripts() ) return;
       
        $suffix = (zota_tbay_get_config('minified_js', false)) ? '.min' : ZOTA_MIN_JS;


        // load bootstrap style 
        if( is_rtl() ){
            wp_enqueue_style( 'bootstrap', ZOTA_STYLES . '/bootstrap.rtl.css', array(), '4.3.1' );
        }else{
            wp_enqueue_style( 'bootstrap', ZOTA_STYLES . '/bootstrap.css', array(), '4.3.1' );
        }

        $skin = zota_tbay_get_theme();
        // Load our main stylesheet.
        if( is_rtl() ){
            $css_path =  ZOTA_STYLES . '/template.rtl.css';
            $css_skin =  ZOTA_STYLES_SKINS . '/'.$skin.'/type.rtl.css';
        }
        else{
            $css_path =  ZOTA_STYLES . '/template.css';
            $css_skin =  ZOTA_STYLES_SKINS . '/'.$skin.'/type.css';
        }

		$css_array = array();

        if( zota_elementor_is_activated() ) {
            array_push($css_array, 'elementor-frontend'); 
        } 
        wp_enqueue_style( 'zota-template', $css_path, $css_array, ZOTA_THEME_VERSION );

        wp_enqueue_style( 'zota-skin', $css_skin, array(), ZOTA_THEME_VERSION );
        wp_enqueue_style( 'zota-style', ZOTA_THEME_DIR . '/style.css', array(), ZOTA_THEME_VERSION );

        /*Put CSS elementor post to header*/
        zota_get_elementor_post_scripts();

        //load font awesome
        
        wp_enqueue_style( 'font-awesome', ZOTA_STYLES . '/font-awesome.css', array(), '5.10.2' );

        //load font custom icon tbay
        wp_enqueue_style( 'zota-font-tbay-custom', ZOTA_STYLES . '/font-tbay-custom.css', array(), '1.0.0' );

        //load simple-line-icons
        wp_enqueue_style( 'simple-line-icons', ZOTA_STYLES . '/simple-line-icons.css', array(), '2.4.0' );

        //load material font icons
        wp_enqueue_style( 'material-design-iconic-font', ZOTA_STYLES . '/material-design-iconic-font.css', array(), '2.2.0' );

        // load animate version 3.5.0
        wp_enqueue_style( 'animate', ZOTA_STYLES . '/animate.css', array(), '3.5.0' );

        
        wp_enqueue_script( 'zota-skip-link-fix', ZOTA_SCRIPTS . '/skip-link-fix' . $suffix . '.js', array(), ZOTA_THEME_VERSION, true );

        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
            wp_enqueue_script( 'comment-reply' );
        }


        /*mmenu menu*/ 
        wp_register_script( 'jquery-mmenu', ZOTA_SCRIPTS . '/jquery.mmenu' . $suffix . '.js', array( 'underscore' ),'7.0.5', true );
     
        /*Treeview menu*/
        wp_enqueue_style( 'jquery-treeview',  ZOTA_STYLES . '/jquery.treeview.css', array(), '1.0.0' );
        
        wp_register_script( 'popper', ZOTA_SCRIPTS . '/popper' . $suffix . '.js', array(), '1.12.9', true );        

        if( class_exists('WeDevs_Dokan') ) { 
            wp_dequeue_script( 'dokan-tooltip' );  
        }
         
        wp_enqueue_script( 'bootstrap', ZOTA_SCRIPTS . '/bootstrap' . $suffix . '.js', array('popper'), '4.3.1', true );          

        wp_register_script( 'js-cookie', ZOTA_SCRIPTS . '/js.cookie' . $suffix . '.js', array(), '2.1.4', true );  
  
        wp_enqueue_script('waypoints', ZOTA_SCRIPTS . '/jquery.waypoints' . $suffix . '.js', array(), '4.0.0', true);

        /*slick jquery*/
        wp_register_script( 'slick', ZOTA_SCRIPTS . '/slick' . $suffix . '.js', array(), '1.0.0', true );
        wp_register_script( 'zota-custom-slick', ZOTA_SCRIPTS . '/custom-slick' . $suffix . '.js', array(), ZOTA_THEME_VERSION, true ); 
  
        // Add before after image
        wp_register_script( 'before-after-image', ZOTA_SCRIPTS . '/cndk.beforeafter' . $suffix . '.js', array('zota-script' ), '0.0.2', true ); 
        wp_register_style( 'before-after-image', ZOTA_STYLES . '/cndk.beforeafter.css', array(), '0.0.2' );
        
        // Add js Sumoselect version 3.0.2
        wp_register_style('sumoselect', ZOTA_STYLES . '/sumoselect.css', array(), '1.0.0', 'all');
        wp_register_script('jquery-sumoselect', ZOTA_SCRIPTS . '/jquery.sumoselect' . $suffix . '.js', array( ), '3.0.2', TRUE);   

        wp_register_script( 'jquery-autocomplete', ZOTA_SCRIPTS . '/jquery.autocomplete' . $suffix . '.js', array('zota-script' ), '1.0.0', true );     
        wp_enqueue_script('jquery-autocomplete'); 

        wp_register_style( 'magnific-popup', ZOTA_STYLES . '/magnific-popup.css', array(), '1.0.0' );
        wp_enqueue_style('magnific-popup');
      
        wp_register_script( 'jquery-countdowntimer', ZOTA_SCRIPTS . '/jquery.countdowntimer' . $suffix . '.js', array( ), '20150315', true );
 
        wp_enqueue_script('jquery-countdowntimer');  

        wp_enqueue_script( 'zota-script',  ZOTA_SCRIPTS . '/functions' . $suffix . '.js', array('jquery-core', 'js-cookie'),  ZOTA_THEME_VERSION,  true );


        wp_enqueue_script( 'detectmobilebrowser', ZOTA_SCRIPTS . '/detectmobilebrowser' . $suffix . '.js', array(), '1.0.6', true );
       
        wp_enqueue_script( 'jquery-fastclick', ZOTA_SCRIPTS . '/jquery.fastclick' . $suffix . '.js', array(), '1.0.6', true );

        if ( zota_tbay_get_config('header_js') != "" ) {
            wp_add_inline_script( 'zota-script', zota_tbay_get_config('header_js') );
        }
  
        $config = zota_localize_translate();

        wp_localize_script( 'zota-script', 'zota_settings', $config );
        
    }
    
    public function add_editor_scripts() {
        wp_enqueue_style( 'font-awesome' );
    }

    public function footer_scripts() {
        if ( zota_tbay_get_config('footer_js') != "" ) {
            $footer_js = zota_tbay_get_config('footer_js');
            echo trim($footer_js);
        }
    }

    public function remove_fonts_redux_url() {
        $show_typography  = zota_tbay_get_config('show_typography', false);
        if( !$show_typography ) {
            wp_dequeue_style( 'redux-google-fonts-zota_tbay_theme_options' );
        } 
    }

    public function load_admin_login_styles() {
        wp_enqueue_style( 'zota-login-admin', ZOTA_STYLES . '/admin/login-admin.css', array(), '1.0.0' );
    }
 
    public function load_admin_styles() {
        wp_enqueue_style( 'material-design-iconic-font', ZOTA_STYLES . '/material-design-iconic-font.css', array(), '2.2.0' ); 
        wp_enqueue_style( 'zota-custom-admin', ZOTA_STYLES . '/admin/custom-admin.css', array(), '1.0.0'  );

        $suffix = (zota_tbay_get_config('minified_js', false)) ? '.min' : ZOTA_MIN_JS;
        wp_enqueue_script( 'zota-admin', ZOTA_SCRIPTS . '/admin/admin' . $suffix . '.js', array(), ZOTA_THEME_VERSION );
    }

    public function add_mvx_frontend_enqueue_scripts( $is_vendor_dashboard ) {
        if( !zota_is_remove_scripts() ) return;

        wp_enqueue_style( 'zota-vendor', ZOTA_STYLES . '/admin/custom-vendor.css', array(), '1.0' );
    }

    /**
     * Register widget area.
     *
     * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
     */
    public function widgets_init() {
        register_sidebar( array(
            'name'          => esc_html__( 'Sidebar Default', 'zota' ),
            'id'            => 'sidebar-default',
            'description'   => esc_html__( 'Add widgets here to appear in your Sidebar.', 'zota' ),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ) );
        

        /* Check WPML */
        if ( zota_wpml_is_activated() ) {
            register_sidebar( array(
                'name'          => esc_html__( 'WPML Sidebar', 'zota' ),
                'id'            => 'wpml-sidebar',
                'description'   => esc_html__( 'Add widgets here to appear.', 'zota' ),
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget'  => '</aside>',
                'before_title'  => '<h2 class="widget-title">',
                'after_title'   => '</h2>',
            ) );
        }
        /* End check WPML */

        register_sidebar( array(
            'name'          => esc_html__( 'Footer', 'zota' ),
            'id'            => 'footer',
            'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'zota' ),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ) ); 

    }

    public function add_cpt_support() {
        $cpt_support = ['tbay_megamenu', 'tbay_footer', 'tbay_header', 'post', 'page', 'product']; 
        update_option( 'elementor_cpt_support', $cpt_support);

        update_option( 'elementor_disable_color_schemes', 'yes'); 
        update_option( 'elementor_disable_typography_schemes', 'yes');
        update_option( 'elementor_container_width', '1200');
        update_option( 'elementor_viewport_lg', '1200');  
        update_option( 'elementor_space_between_widgets', '0');
        update_option( 'elementor_load_fa4_shim', 'yes');

        // update_option('sb_instagram_custom_template', 'yes');
    }

    public function edit_post_show_excerpt( $user_login, $user ) {
        update_user_meta( $user->ID, 'metaboxhidden_post', true );
    }

    public function instagram_use_theme_templates() {
        if( apply_filters('zota_sb_instagram_custom_template', true) ) {
            $active = true;
        } else {
            $active = false;
        }

        return $active;
    }
    

    /**
     * Use front-page.php when Front page displays is set to a static page.
     *
     * @param string $template front-page.php.
     *
     * @return string The template to be used: blank if is_home() is true (defaults to index.php), else $template.
     */
    public function front_page_template( $template ) {
        return is_home() ? '' : $template;
    }

    public function setup() {
        /*
         * Make theme available for translation.
         * Translations can be filed in the /languages/ directory.
         * If you're building a theme based on zota, use a find and replace
         * to change 'zota' to the name of your theme in all the template files
         */
        load_theme_textdomain( 'zota', ZOTA_THEMEROOT . '/languages' );

        // Add default posts and comments RSS feed links to head.
        add_theme_support( 'automatic-feed-links' );

        add_theme_support( "post-thumbnails" );

        add_image_size('zota_avatar_post_carousel', 120, 120, true);

        // This theme styles the visual editor with editor-style.css to match the theme style.
        $font_source = zota_tbay_get_config('show_typography', false);
        if( !$font_source ) {
            add_editor_style( array( 'css/editor-style.css', zota_fonts_url() ) );
        }

        /*
         * Let WordPress manage the document title.
         * By adding theme support, we declare that this theme does not use a
         * hard-coded <title> tag in the document head, and expect WordPress to
         * provide it for us.
         */
        add_theme_support( 'title-tag' );


        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support( 'html5', array(
            'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
        ) );

        
        /*
         * Enable support for Post Formats.
         *
         * See: https://codex.wordpress.org/Post_Formats
         */
        add_theme_support( 'post-formats', array(
            'aside', 'image', 'video', 'gallery', 'audio' 
        ) );

        $color_scheme  = zota_tbay_get_color_scheme();
        $default_color = trim( $color_scheme[0], '#' );

        // Setup the WordPress core custom background feature.
        add_theme_support( 'custom-background', apply_filters( 'zota_custom_background_args', array(
            'default-color'      => $default_color,
            'default-attachment' => 'fixed',
        ) ) );

        if( apply_filters('zota_remove_widgets_block_editor', true) ) {
            remove_theme_support( 'block-templates' );
            remove_theme_support( 'widgets-block-editor' );

            /*Remove extendify--spacing--larg CSS*/
            update_option('use_extendify_templates', '');
        }

        add_action( 'wp_login', array( $this, 'edit_post_show_excerpt'), 10, 2 );


        // This theme uses wp_nav_menu() in two locations.
        register_nav_menus( array(
            'primary'           => esc_html__( 'Primary Menu', 'zota' ),
            'mobile-menu'       => esc_html__( 'Mobile Menu','zota' ),
            'nav-category-menu'  => esc_html__( 'Nav Category Menu', 'zota' ),
            'track-order'  => esc_html__( 'Tracking Order Menu', 'zota' ),
        ) );

        update_option( 'page_template', 'elementor_header_footer'); 
    }
}

return new zota_setup_theme();