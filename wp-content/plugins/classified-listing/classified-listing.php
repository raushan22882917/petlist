<?php
/**
 * @package CalssifiedListing
 * Plugin Name:       Classified Listing – AI-Powered Classified ads & Business Directory
 * Plugin URI:        https://radiustheme.com/demo/wordpress/classified
 * Description:       The Best Classified Listing and Business Directory Plugin for WordPress to create Classified ads website, job directory, local business directory and service directory.
 * Version:           5.5.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Business Directory Team by RadiusTheme
 * License:			  GPLv2 or later
 * License URI: 	  https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:        https://radiustheme.com
 * Text Domain:       classified-listing
 * Domain Path:       /i18n/languages
 */

defined( 'ABSPATH' ) || die( 'Keep Silent' );


define( 'RTCL_VERSION', '5.5.0' );
define( 'RTCL_PLUGIN_FILE', __FILE__ );
define( 'RTCL_PATH', plugin_dir_path( RTCL_PLUGIN_FILE ) );
define( 'RTCL_URL', plugins_url( '', RTCL_PLUGIN_FILE ) );

require_once 'app/Rtcl.php';    