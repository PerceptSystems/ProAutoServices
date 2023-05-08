<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'proautoservices' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
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
define( 'AUTH_KEY',         'ZP7-Qw-#2l(>cg7r1.uT044(^66 *H8r/{eRrmL<3keT)42tir=7[INhgQ}%{JS#' );
define( 'SECURE_AUTH_KEY',  'ZOQb+l3&ZsGH_<N>ix,cRlyklyo9sivP,.N*XK`Trx:jD1AaEi8@$y_80KSfvBlS' );
define( 'LOGGED_IN_KEY',    'V-Eu>g]v71o>Ta wQ(6b!*TrK>w*pwc;(o?CX5{A#>vq C<KX Kj?#1D[nLYR~xp' );
define( 'NONCE_KEY',        '*d2%p#tvN2zFRBL,{EuI,zi{{hg+&8CZ +r+(8x%#bL{,X)@dv|lebXwS)3}_),E' );
define( 'AUTH_SALT',        'W@t~=~_FGy2/>-39m)rE<y,K$A/9H$=Ok^&&0eFdnd<V,.B+*|N7}6,by%Kt$ZLA' );
define( 'SECURE_AUTH_SALT', ',MHuhn^RDOVbFq`-ssIn4TBKGGt~ZV4o$^-{=:7e8#$3s1)grS-A3S:3qJWRmkf;' );
define( 'LOGGED_IN_SALT',   'H8jo.}2&@N:,_qS&W6u/Cb~$Nb_Xk9h@ QDg?,P|SQ?b%/qoEb%IHmlYx&1sOTWZ' );
define( 'NONCE_SALT',       '<S*KU|`RtM9t8>@_&>tC,yQqZ#l9>wP[RM#c#Ce_1e<-e i5Xvbjv7J9_f-YgND%' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
define( 'WP_DEBUG', false );

//increase memory limit
set_time_limit(300);
define ('WP_MEMORY_LIMIT', '256M');

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
