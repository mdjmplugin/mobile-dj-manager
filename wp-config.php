<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
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
define( 'DB_NAME', 'wp_5jza1' );

/** MySQL database username */
define( 'DB_USER', 'wp_bbfql' );

/** MySQL database password */
define( 'DB_PASSWORD', '8jPzU?Jwy_68_O1i' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost:3306' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'Xq/R7|fCS~T4h;9147;V(h+XqoxH;[uBtPur3u7E&]l4fWpu;:%ev0EQ+1vwIoZg');
define('SECURE_AUTH_KEY', 'CgHP7~Z2[2RD4AewKeH1j9@6fx60/6ah*WA-ThmO()veb1[1TZ_br5p1h(07wqr5');
define('LOGGED_IN_KEY', '(yr79M#nI%T/_1W8t3~//1Qyx8#(&)%lY#ZHVM)99Oki722HkcXJR|vYRPu5WM5]');
define('NONCE_KEY', 'dM#U2qUG5]YqL7hsOSvFO[w#g0gZj:w7W7eu6G@u7y98;#6~@yuqcO83I/t09%::');
define('AUTH_SALT', '7O[tM!zQwVX77K0A33*XFRz|%e8g5Ht&W!+V:[zsfG6i(N7W6X7]8f2Z&5(F[Y40');
define('SECURE_AUTH_SALT', 'T4G/+4%r9m1Nj2Qj4z*q(B8sz2wJ095qJx7bA:u)b;D2Id!*AR#3l*qA1!SgLA@4');
define('LOGGED_IN_SALT', '7#7K2|:15H344m84;hYt:1%4*Qk-5p4+tw2P4csWU6Q21)7&48TR@|c/~K%opbJl');
define('NONCE_SALT', ']&8tGO_@yMrk4V*2Fw41fd1DI1UF+lQ1ZZV7T2/TJ3Ztfj:Ou]q#(BD2O:*qa_z;');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'eZH3F6fK_';


define('WP_ALLOW_MULTISITE', true);
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
