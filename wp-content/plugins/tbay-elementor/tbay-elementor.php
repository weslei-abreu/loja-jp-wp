<?php
/**
 * Thembay Elementor Plugin
 *
 * A simple, truly extensible and fully responsive options framework
 * for WordPress themes and plugins. Developed with WordPress coding
 * standards and PHP best practices in mind.
 *
 * Plugin Name:     Thembay Elementor
 * Plugin URI:      https://thembay.com
 * Description:     Thembay Elementor. A plugin required to activate the functionality in the themes.
 * Author:          Team Thembay
 * Author URI:      https://thembay.com/
 * Version:         1.1.11
 * Text Domain:     tbay-elementor
 * License:         GPL3+
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path:     languages
 * Requires at least: 6.2
 * Requires PHP: 7.4
 */

define( 'TBAY_ELEMENTOR_VERSION', '1.1.11');
define( 'TBAY_ELEMENTOR_URL', plugin_dir_url( __FILE__ ) ); 
define( 'TBAY_ELEMENTOR_DIR', plugin_dir_path( __FILE__ ) );

define( 'TBAY_ELEMENTOR_ACTIVED', true );

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

require_once( TBAY_ELEMENTOR_DIR . 'plugin-update-checker/plugin-update-checker.php' );
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://plugins.thembay.com/update/tbay-elementor/plugin.json',
	__FILE__, //Full path to the main plugin file or functions.php.
	'tbay-elementor'
);

/**
 * Custom Post type
 *
 */
add_action( 'init', 'tbay_elementor_register_post_types', 1 );

/**
 * functions
 *
 */
require TBAY_ELEMENTOR_DIR . 'functions.php';
require TBAY_ELEMENTOR_DIR . 'functions-preset.php';
/**
 * Widgets Core
 *
 */
require TBAY_ELEMENTOR_DIR . 'classes/class-tbay-widgets.php';
add_action( 'widgets_init',  'tbay_elementor_widget_init' );

require TBAY_ELEMENTOR_DIR . 'classes/class-tbay-megamenu.php';
/**
 * Init
 *
 */
function tbay_elementor_init() {
	$demo_mode = apply_filters( 'tbay_elementor_register_demo_mode', false );
	if ( $demo_mode ) {
		tbay_elementor_init_redux();
	}
	$enable_tax_fields = apply_filters( 'tbay_elementor_enable_tax_fields', false );
	if ( $enable_tax_fields ) {
		if ( !class_exists( 'Taxonomy_MetaData_CMB2' ) ) {
			require_once TBAY_ELEMENTOR_DIR . 'libs/cmb2/taxonomy/Taxonomy_MetaData_CMB2.php';
		}
	}
}
add_action( 'init', 'tbay_elementor_init', 100 );