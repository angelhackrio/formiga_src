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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'u792589004_for');

/** MySQL database username */
define('DB_USER', 'u792589004_miga');

/** MySQL database password */
define('DB_PASSWORD', 'f0rm1g@');

/** MySQL hostname */
define('DB_HOST', 'mysql.hostinger.com.br');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '5TCW3#sDi8tr^HIdWPhvmr91{4G3I4;c,RU)S7Y>]Y*>^z]){hLk7=dnwq)ir:lu');
define('SECURE_AUTH_KEY',  'fZwH):uKmsnsC>TO$5>I;w|SQB|f.gsn1~le@BAGB4xt[|puW0rSUzr#CTU4a#%K');
define('LOGGED_IN_KEY',    'Xbh[YONi|fQpkFq42VJx=6YFdG-?pWDG!QMl5v-@VDv;qG6h^Th5%EZ3fU=$CCMi');
define('NONCE_KEY',        '3s~Ft{Vv-x%bW;2GY%?vOIj^|iw2a?N)yH]zy[wx#@YO_K58#G^?+@s7aT;7nO62');
define('AUTH_SALT',        '8&?rkAHXQ]eb5)iKt@1mPYDI}m&5{(9*3-X`-Cf6j&xrVrW},Pt.A4BSOY|)wYVl');
define('SECURE_AUTH_SALT', '5qP35<4dEVB7g oR~k$z;ija2Y|?A|,.X&gi[qN8~ML>PnHfIq0U)KHs4VtWOEt:');
define('LOGGED_IN_SALT',   'lKBy1$:X _aKGbQYCYM1zD~iQ$B#>J] 7A{n{m^ga@ahI)6gC}YA~O6oU`zvM!>;');
define('NONCE_SALT',       'Crmp<zR+mPf<@yk^n(G]S9^tetU5s8Pu:Jwy}66Z9Fnni()GcEGQ@C|OcHJmeA=M');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
