<?php
/*
  Copyright (c) 2016 - 2025, Hide My WP Ghost
  The copyrights to the software code in this file are licensed under the (revised) BSD open source license.

  Plugin Name: WP Ghost Lite
  Plugin URI: https://wordpress.org/plugins/hide-my-wp/
  Description: #1 Hack Prevention Security Solution: Hide WP CMS, 7G/8G Firewall, Brute Force Protection, 2FA, GEO Security, Temporary Logins, Alerts & more.
  Version: 5.4.05
  Author: Hide My WP Ghost
  Author URI: https://hidemywpghost.com
  License: GPLv2 or later
  License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
  Text Domain: hide-my-wp
  Domain Path: /languages
  Network: true
  Requires at least: 5.3
  Requires PHP: 7.0
 */

if ( defined( 'ABSPATH' ) && ! defined( 'HMW_VERSION' ) ) {

	// Set current plugin version
	define( 'HMWP_VERSION', '5.4.05' );

	// Set the last stable version of the plugin
	define( 'HMWP_STABLE_VERSION', '5.4.03' );

	// Set the plugin basename
	define( 'HMWP_BASENAME', plugin_basename( __FILE__ ) );

	// Set the PHP version ID for later use
	defined( 'PHP_VERSION_ID' ) || define( 'PHP_VERSION_ID', (int) str_replace( '.', '', PHP_VERSION ) );

	// Set the HMWP id for later verification
	defined( 'HMWP_VERSION_ID' ) || define( 'HMWP_VERSION_ID', (int) str_replace( '.', '', HMWP_VERSION ) );

	try {

		// Call config files
		require( dirname( __FILE__ ) . '/config/config.php' );

		// Import main classes
		require_once( _HMWP_CLASSES_DIR_ . 'ObjController.php' );

		if ( class_exists( 'HMWP_Classes_ObjController' ) ) {

			// Load Exception, Error and Tools class
			HMWP_Classes_ObjController::getClass( 'HMWP_Classes_Tools' );
			HMWP_Classes_ObjController::getClass( 'HMWP_Classes_Error' );

			// Load Front Controller
			HMWP_Classes_ObjController::getClass( 'HMWP_Classes_FrontController' );

			// If the disable signal is on, return
			// Don't run cron hooks and update if there are installs
			if ( defined( 'HMWP_DISABLE' ) && HMWP_DISABLE ) {
				return;
			} elseif ( ! is_multisite() && defined( 'WP_INSTALLING' ) && WP_INSTALLING ) {
				return;
			} elseif ( is_multisite() && defined( 'WP_INSTALLING_NETWORK' ) && WP_INSTALLING_NETWORK ) {
				return;
			}

			if ( ! HMWP_Classes_Tools::isCron() ) {
				// If Brute Force is activated
				if ( HMWP_Classes_Tools::getOption( 'hmwp_bruteforce' ) ) {
					HMWP_Classes_ObjController::getClass( 'HMWP_Controllers_Brute' );
				}
			}

			if ( is_admin() || is_network_admin() ) {

				// Check the user roles
				HMWP_Classes_ObjController::getClass( 'HMWP_Models_RoleManager' );

				// Make sure to write the rewrites with other plugins
				add_action( 'rewrite_rules_array', array(
					HMWP_Classes_ObjController::getClass( 'HMWP_Classes_Tools' ),
					'checkRewriteUpdate'
				), 11, 1 );

				// Hook activation and deactivation
				register_activation_hook( __FILE__, array(
					HMWP_Classes_ObjController::getClass( 'HMWP_Classes_Tools' ),
					'hmwp_activate'
				) );
				register_deactivation_hook( __FILE__, array(
					HMWP_Classes_ObjController::getClass( 'HMWP_Classes_Tools' ),
					'hmwp_deactivate'
				) );

				// Verify if there are updated and all plugins and themes are in the right list
				add_action( 'activated_plugin', array(
					HMWP_Classes_ObjController::getClass( 'HMWP_Classes_Tools' ),
					'checkPluginsThemesUpdates'
				), 11, 0 );

				// When a theme is changed
				add_action( 'after_switch_theme', array(
					HMWP_Classes_ObjController::getClass( 'HMWP_Classes_Tools' ),
					'checkPluginsThemesUpdates'
				), 11, 0 );

			}

			// Check if the cron is loaded in advanced settings
			if ( ( HMWP_Classes_Tools::getOption( 'hmwp_mode' ) <> 'default' ) ) {

				// Update rules in .htaccess on other plugins update to avoid rule deletion
				if ( ! HMWP_Classes_Tools::isApache() || HMWP_Classes_Tools::isLitespeed() ) {

					add_action( 'automatic_updates_complete', function ( $options ) {
						if ( isset( $options['action'] ) && $options['action'] == 'update' ) {
							set_transient( 'hmwp_update', 1 );
						}
					}, 10, 1 );

					// On plugins are update
					add_action( 'upgrader_process_complete', function ( $upgrader_object, $options ) {
						$our_plugin = plugin_basename( __FILE__ );

						if ( isset( $options['action'] ) && $options['action'] == 'update' ) {
							if ( $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
								foreach ( $options['plugins'] as $plugin ) {
									if ( $plugin <> $our_plugin ) {
										set_transient( 'hmwp_update', 1 );
									}
								}
							}
						}
					}, 10, 2 );

				}

				if ( HMWP_Classes_Tools::getOption( 'hmwp_change_in_cache' ) || HMWP_Classes_Tools::getOption( 'hmwp_mapping_file' ) ) {
					// Run the HMWP cron
					HMWP_Classes_ObjController::getClass( 'HMWP_Controllers_Cron' );

					add_action( HMWP_CRON, array(
						HMWP_Classes_ObjController::getClass( 'HMWP_Controllers_Cron' ),
						'processCron'
					) );
				}
			}

		}

	} catch ( Exception $e ) {

	}

}