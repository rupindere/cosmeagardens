<?php

//Begin Really Simple SSL session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple SSL
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
define( 'DB_NAME', 'cosmeagardens' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'newpassword' );

/** MySQL hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );
define( 'WP_DEBUG', 'true' );

define( 'SNAPSHOT4_CHUNKED_ZIPSTREAMING_LARGE', true );
define('SNAPSHOT4_FILE_ZIPSTREAM_LOG_VERBOSE', true);

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '%yE#F%2F?;j^dyc_^?n@t=l/8JFx_*a;DThgYP^^7dnaY?(zX_fNB:CjDoBZHzG9' );
define( 'SECURE_AUTH_KEY',   'H>z@&jFwJ PinY>]EUN5@*c!{R0l@!s}f[A@Ds{#hSG$NJ]&G?u#hnIjE>}vjAI?' );
define( 'LOGGED_IN_KEY',     'q+a$%$;GSpqT4jBE~CKplE N,L3FQ40Prlk.YRM?;:0b{fZ2$;PKWS4GR2*0zS#v' );
define( 'NONCE_KEY',         '&I..Vh0Kw8[UFrL(c7je*+fc+4WXV9ADQ&hp|c4#&T!&~z_Yg`e/Ey}KZphU,|:y' );
define( 'AUTH_SALT',         '}noKKjlOJwdzfsAr,J;e%Sj.a2v7)@kvge8Pv6e&o^`q_{>3r&j@@o.M2@N*<c0p' );
define( 'SECURE_AUTH_SALT',  '@Z(_H~*`Jn6%{L@(1%C:$1+$oED!^-51o6.2~lbZmT$f[kPDdqBIE7*N4>`!KxK8' );
define( 'LOGGED_IN_SALT',    'j~y_RgKL4KCX,rM87R:^){rM 9zJ%d/:7SJK>|795LEB&g=c^oopuljXQ5zd5 ;,' );
define( 'NONCE_SALT',        'lF+Oe,H,2VVcDagUb.etOVT^ &C!l:iA@8_Umqn8~lQ1B;l2Ig:rAR&N]R>h*o?+' );
define( 'WP_CACHE_KEY_SALT', 'LT}k)t#JW&i5rTpg`{vi6_9`/N)LBq>#-;Yd$}hu: .m1Z[tn_[F6>Pku*t=B/rX' );

define( 'SNAPSHOT4_CHUNKED_ZIPSTREAMING_LARGE', true );
define('SNAPSHOT4_FILE_ZIPSTREAM_LOG_VERBOSE', true);
define('SNAPSHOT4_ZIPSTREAM_FLUSH_BUFFER', true);

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpu1_';




/* That's all, stop editing! Happy publishing. */
define( 'WP_MEMORY_LIMIT', '1024M' );

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
@include_once('/var/lib/sec/wp-settings.php'); // Added by SiteGround WordPress management system
