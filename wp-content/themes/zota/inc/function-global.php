<?php

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * @since Zota 1.0
 */
define( 'ZOTA_THEME_VERSION', '1.0' );

/**
 * ------------------------------------------------------------------------------------------------
 * Define constants.
 * ------------------------------------------------------------------------------------------------
 */
define( 'ZOTA_THEME_DIR', 		get_template_directory_uri() );
define( 'ZOTA_THEMEROOT', 		get_template_directory() );
define( 'ZOTA_IMAGES', 			ZOTA_THEME_DIR . '/images' );
define( 'ZOTA_SCRIPTS', 		ZOTA_THEME_DIR . '/js' );

define( 'ZOTA_SCRIPTS_SKINS', 	ZOTA_SCRIPTS . '/skins' );
define( 'ZOTA_STYLES', 			ZOTA_THEME_DIR . '/css' );
define( 'ZOTA_STYLES_SKINS', 	ZOTA_STYLES . '/skins' );


define( 'ZOTA_INC', 				     'inc' );
define( 'ZOTA_MERLIN', 				     ZOTA_INC . '/merlin' );
define( 'ZOTA_CLASSES', 			     ZOTA_INC . '/classes' );
define( 'ZOTA_VENDORS', 			     ZOTA_INC . '/vendors' );
define( 'ZOTA_ELEMENTOR', 		         ZOTA_THEMEROOT . '/inc/vendors/elementor' );
define( 'ZOTA_ELEMENTOR_TEMPLATES',     ZOTA_THEMEROOT . '/elementor_templates' );
define( 'ZOTA_PAGE_TEMPLATES',          ZOTA_THEMEROOT . '/page-templates' );
define( 'ZOTA_WIDGETS', 			     ZOTA_INC . '/widgets' );

define( 'ZOTA_ASSETS', 			         ZOTA_THEME_DIR . '/inc/assets' );
define( 'ZOTA_ASSETS_IMAGES', 	         ZOTA_ASSETS    . '/images' );

define( 'ZOTA_MIN_JS', 	'' );

if ( ! isset( $content_width ) ) {
	$content_width = 660;
}

function zota_tbay_get_config($name, $default = '') {
	global $zota_options;
    if ( isset($zota_options[$name]) ) {
        return $zota_options[$name];
    }
    return $default;
}

function zota_tbay_get_global_config($name, $default = '') {
	$options = get_option( 'zota_tbay_theme_options', array() );
	if ( isset($options[$name]) ) {
        return $options[$name];
    }
    return $default;
}
