<?php
/**
 * WordPress Configuration File
 * Auto-generated for local development with petslist theme.
 */

// ** Database settings ** //
define( 'DB_NAME', 'petslist_db' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', '127.0.0.1' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 */
define( 'AUTH_KEY',         'Xk9!mP2#vL5@nQ8$wR1^tY4&uI7*oA0-bD3=eF6+gH9|jK' );
define( 'SECURE_AUTH_KEY',  'cM4$nP7!qS0#uV3@xY6^zA9&bC2*dE5-fG8=hJ1+kL4|mN7' );
define( 'LOGGED_IN_KEY',    'pQ2#rT5$sW8!uX1@vY4^wZ7&xA0*yB3-zC6=aD9+bE2|cF5' );
define( 'NONCE_KEY',        'eH8!fI1#gJ4$hK7@iL0^jM3&kN6*lO9-mP2=nQ5+oR8|pS1' );
define( 'AUTH_SALT',        'qT4$rU7!sV0#tW3@uX6^vY9&wZ2*xA5-yB8=zC1+aD4|bE7' );
define( 'SECURE_AUTH_SALT', 'cF0#dG3$eH6!fI9@gJ2^hK5&iL8*jM1-kN4=lO7+mP0|nQ3' );
define( 'LOGGED_IN_SALT',   'oR6$pS9!qT2#rU5@sV8^tW1&uX4*vY7-wZ0=xA3+yB6|zC9' );
define( 'NONCE_SALT',       'aD2#bE5$cF8!dG1@eH4^fI7&gJ0*hK3-iL6=jM9+kN2|lO5' );
/**#@-*/

/**
 * WordPress database table prefix.
 */
$table_prefix = 'wp_';

/**
 * Developer mode settings
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

/* Site URL configuration for local development */
define( 'WP_HOME', 'http://localhost:8080' );
define( 'WP_SITEURL', 'http://localhost:8080' );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
