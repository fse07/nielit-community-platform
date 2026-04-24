<?php
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
define( 'DB_NAME', 'wordpress' );
define('WP_HOME',    'https://nielit-community.centralindia.cloudapp.azure.com');
define('WP_SITEURL', 'https://nielit-community.centralindia.cloudapp.azure.com');

/** Database username */
define( 'DB_USER', 'wpuser' );

/** Database password */
define( 'DB_PASSWORD', 'ChangeMe_StrongPasswordHere123!' );

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
define( 'AUTH_KEY',         'QvY9pFv@Zo3*SusXRbOua,c@FmI0iaH)w&kP%[?$m3Am8Ay^JRBzP[`nRrcJ-fc]' );
define( 'SECURE_AUTH_KEY',  'b`)>Jq#*.NK!_Tf6ZnaKg{_`?mj8`_._rM_tDa~]`&e,_!65RERqIHf>]YSY4lb9' );
define( 'LOGGED_IN_KEY',    'RT*AL>l>]^f`}OX!Ym}B/55Wp4j(Q(kTvN1 qJ%pJom=F6$&wd!mET8zo=aEJn6.' );
define( 'NONCE_KEY',        'OEV1]O_u*MW3XD[%l;mjbFp}t>C.0T5wP{[wuNk9;v<z$Pf#e=QO}wn*?.R[%,m%' );
define( 'AUTH_SALT',        'R@Un~F<rv>sF:`_np6@rsO%~43^)O:(a02~OR48E(6tszSdMGr`&<l|p3K(yYFsO' );
define( 'SECURE_AUTH_SALT', '8_P6QMkNrGVpBtc5vb?LvmYIlP:Vp[1a.1#ez=]engbR,O/[yX*&V#?dr4o%E3F~' );
define( 'LOGGED_IN_SALT',   '8G*{-ltB/N-7qzB<gFoPu/7<>sA[Nh~lYaWV0.PY8)VkjA!d].NN=b!tVh97;D0Y' );
define( 'NONCE_SALT',       'de=EY5(P},^o5+mEPzXM@PEN+v;Z&]mRrRC&JX&&>iGo[QEP2=z`|@SvAixB%=!e' );

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
