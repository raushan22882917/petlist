<?php

/**
 * @wordpress-plugin
 * Plugin Name:             Classified Listing Pro – Classified ads & Business Directory Plugin
 * Plugin URI:              https://radiustheme.com/demo/wordpress/classifiedpro
 * Description:             This is the Add-on plugin for Classified Listing – Classified ads & Business Directory Plugin. By using this Addon you can get pro feature of all others Add-ons.
 * Version:                 2.2.1
 * Requires PHP:            7.4
 * Requires at least:       5.0
 * Tested up to:            6.4
 * Author:                  RadiusTheme
 * Author URI:              https://radiustheme.com
 * Text Domain:             classified-listing-pro
 * Domain Path:             /languages
 */

defined( 'ABSPATH' ) || die( 'Keep Silent' );

define( 'RTCL_PRO_VERSION', '2.2.1' );
define( 'RTCL_PRO_PLUGIN_FILE', __FILE__ );
define( 'RTCL_PRO_PATH', plugin_dir_path( RTCL_PRO_PLUGIN_FILE ) );
define( 'RTCL_PRO_URL', plugins_url( '', RTCL_PRO_PLUGIN_FILE ) );

require_once 'app/RtclPro.php';