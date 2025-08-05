<?php

// BEGIN iThemes Security - Do not modify or remove this line
// iThemes Security Config Details: 2
define( 'DISALLOW_FILE_EDIT', true ); // Disable File Editor - Security > Settings > WordPress Tweaks > File Editor
// END iThemes Security - Do not modify or remove this line

define( 'ITSEC_ENCRYPTION_KEY', 'I2Y7W182ZG5maXF6I1VHcFM3NT1AX198PEZ5NzkrdixfUHVaZGQoLS59MWNlRVpSMWVbenpmTndbSkhMMUFyTA==' );

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'loja_jp_wp' );

/** Database username */
define( 'DB_USER', 'lojajp' );

/** Database password */
define( 'DB_PASSWORD', 'Lojajp2024#' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '81R$#@L 659;bho;k-v1)jYdQ)qjxOq4~[3>t blVr9nQi0^G]~Rz+n}BwbL(M=t' );
define( 'SECURE_AUTH_KEY',  'fR1:U6ZIYTz3jPY-Homi!(`+Aljab5a,i#cvsC!zi^?g*891^A+wGIquq)}JaN,m' );
define( 'LOGGED_IN_KEY',    '0M)#da$35o$84}A7Q(deZh;+Iik @{Qt8sCFcXvD8WQ@-;5e5dhGo:7jT]5#e%Q>' );
define( 'NONCE_KEY',        'y>D|3tq$MSq0dm@E#np~(FmBB?P}OMft;RF9mFg,2J{jxEi0OLxU#e$)3f0nq9aS' );
define( 'AUTH_SALT',        '}94o4- n%@# i=h!Ac{6rdC.AjRIPCD0&m,JW1z-b_^*fUy{}oni{}>*Z1MQ^:[r' );
define( 'SECURE_AUTH_SALT', 'gcyz|Q/f~J@qLI!:n.Nc=:}ZmM$eZ{W`^?9!u;X5Kr8Lw7-J* OxR69v-sXC TMo' );
define( 'LOGGED_IN_SALT',   'Qg 5ev/B<dhC.mHlfUEYqv-wMi:_mg-P8$1@d} 0=:rn<$/6Fh)Rp/gBTbj}#K^u' );

define('DISALLOW_FILE_MODS', false);
define('DISALLOW_PLUGIN_INSTALL', false);
define( 'NONCE_SALT',       '0Zq#Ct7ff5=:G95y.@!4J$JmX%H22A|M1C*.V7<toVtg|6oqsCc:r4lR_@@E^osV' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wpio_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */


/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );
@ini_set( 'display_errors', 1 );


define('DISALLOW_FILE_EDIT', true);
define('DISALLOW_FILE_MODS', true);
