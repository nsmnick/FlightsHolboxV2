<?php

define('VITE_DEV_SERVER_URL', 'http://localhost:5274');

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //

// JACOB
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

// Nick
//define('DB_NAME', 'flightsholbox');
//define('DB_USER', 'root');
//define('DB_PASSWORD', 'root');
//define('DB_HOST', 'localhost');

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          '3z}AD$8+GI#S}EB4aOa.5f>:JaWZWH,1^0k_hmqyM`v/MOq~hu-Huv2NYK)%gCm#' );
define( 'SECURE_AUTH_KEY',   'X<:9JN5tm:)$If@?@nwi7d J}>sEA$?tg<_~}^gZ<*Yd4[X Yz7RM`Byn[tE@TwX' );
define( 'LOGGED_IN_KEY',     ')JvHz&21EzR|Ita$C`ifb|R4#0b6^_s@m/o(_zKh%Fd?->:-Q> EjWX:]LH4l_d;' );
define( 'NONCE_KEY',         '=QW[L$/0E4,rg6yxJ=Y1m!QY26(DbEVfb1b 7U9WcUf=>>oci2[*Bl%nXe[$qIrA' );
define( 'AUTH_SALT',         'OKXO2Qxm)#by(t+!% l{pp>%$T3UX>^B)F~!Ryx^-@)36q{V(^dv`^OQPy2oi`}1' );
define( 'SECURE_AUTH_SALT',  'qd?vsl?&3_xxepy<XT{6q##7#;r){bd,4h.sv|4?g: m#n~K;i1=F54$<]$idj Z' );
define( 'LOGGED_IN_SALT',    'yfpqi~Eg-e]-$Y!|MU}X2]kG|qQ]Dd?T&q<;bdp;nbAs$Av<u{{~^2T{4(<?.S|E' );
define( 'NONCE_SALT',        'jGGtSXxK(FK;_-a54YS^/;Xg^k>d[OayhEC;`JsmCiB:G6Bv=/3T{$m3)a6CN7d,' );
define( 'WP_CACHE_KEY_SALT', '3F8<qE`C[D}jDLC2{{Etg;jlnzacv5A8A,;=;j_V8br/$VZJLBq{bwNr`r_[k0.c' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}
define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_DEBUG_LOG', true );

define( 'WP_ENVIRONMENT_TYPE', 'local' );

define( 'WP_POST_REVISIONS', 3 );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
