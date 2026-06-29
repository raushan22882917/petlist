<?php
/*
Plugin Name: Petslist Core
Plugin URI: https://www.radiustheme.com
Description: Petslist Core Plugin for Petslist Theme
Version: 1.2.0
Author: RadiusTheme
Author URI: https://www.radiustheme.com
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RadiusTheme\Petslist\Options;

if ( ! defined( 'PETSLIST_CORE' ) ) {
	$plugin_data = get_file_data( __FILE__, [ 'version' => 'Version' ] );
	define( 'PETSLIST_CORE', $plugin_data['version'] );
	define( 'PETSLIST_CORE_THEME_PREFIX', 'petslist' );
	define( 'PETSLIST_CORE_BASE_URL', plugin_dir_url( __FILE__ ) );
	define( 'PETSLIST_CORE_BASE_DIR', plugin_dir_path( __FILE__ ) );
}

require_once PETSLIST_CORE_BASE_DIR . 'demo-users/user-importer.php';

class Petslist_Core {

	public $plugin = 'petslist-core';
	public $action = 'petslist_theme_init';
	protected static $instance;

	public function __construct() {
		add_filter( 'body_class', [ $this, 'petslist_core_body_classes' ] );
		add_action( 'plugins_loaded', [ $this, 'demo_importer' ], 17 );
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ], 20 );
		add_action( $this->action, [ $this, 'after_theme_loaded' ] );
		add_action( 'admin_enqueue_scripts', array( $this, 'petslist_core_admin_enqueue_scripts' ), 20 );
		add_action( 'wp_enqueue_scripts', array( $this, 'petslist_core_enqueue_scripts' ), 20 );
		// Restrict Admin Area
		add_action( 'after_setup_theme', [ $this, 'restrict_admin_area' ] );

		if ( isset( $_GET['export_user'] ) && $_GET['export_user'] == 1 ) {
			Petslist_Core_Demo_User_Import::export_users();
		}
	}

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function petslist_core_admin_enqueue_scripts() {
		//CSS
		wp_enqueue_style( 'petslist-admin', PETSLIST_CORE_BASE_URL . 'assets/css/admin.css' );
		if ( is_rtl() ) {
			wp_enqueue_style( 'petslist-elementor', PETSLIST_CORE_BASE_URL . 'assets/css/rtl-petslist-elementor.css' );
		} else {
			wp_enqueue_style( 'petslist-elementor', PETSLIST_CORE_BASE_URL . 'assets/css/petslist-elementor.css' );
		}
		//JS
		wp_enqueue_script( 'petslist-admin', PETSLIST_CORE_BASE_URL . 'assets/js/admin.js', array( 'jquery' ), '', true );
		wp_enqueue_script( 'headroom', PETSLIST_CORE_BASE_URL . 'assets/js/headroom.js', array( 'jquery' ), '', true );
	}
	public function petslist_core_enqueue_scripts() {
		// CSS
		
		if ( is_rtl() ) {
			wp_enqueue_style( 'petslist-elementor', PETSLIST_CORE_BASE_URL . 'assets/css/rtl-petslist-elementor.css' );
		} else {
			wp_enqueue_style( 'petslist-elementor', PETSLIST_CORE_BASE_URL . 'assets/css/petslist-elementor.css' );
		}
		// JS
		wp_enqueue_script( 'headroom', PETSLIST_CORE_BASE_URL . 'assets/js/headroom.js', array( 'jquery' ), '', true );
		wp_enqueue_script( 'petslist-core', PETSLIST_CORE_BASE_URL . 'assets/js/petslist-core.js', array( 'jquery' ), '', true );
	}

	public function petslist_core_body_classes( $classes ) {
		$theme = wp_get_theme();
		if ( 'Petslist' == $theme->name || 'Petslist' == $theme->parent_theme ) {
			$classes[] = Options::$options['sticky_header'] ? 'sticky-header-enable' : '';
		}
		return $classes;
	}

	public function restrict_admin_area() {
		$theme = wp_get_theme();
		if ( 'Petslist' == $theme->name || 'Petslist' == $theme->parent_theme ) {
			if ( !empty(Options::$options['remove_admin_bar']) && ! current_user_can( 'administrator' ) ) {
				show_admin_bar( false );
			}
		}
	}

	public function after_theme_loaded() {
		if ( defined( 'RT_FRAMEWORK_VERSION' ) ) {
			require_once PETSLIST_CORE_BASE_DIR . 'inc/category.php'; // Categories
			require_once PETSLIST_CORE_BASE_DIR . 'inc/post-meta.php'; // Post Meta
			require_once PETSLIST_CORE_BASE_DIR . 'widgets/init.php'; // Widgets
		}

		if ( did_action( 'elementor/loaded' ) ) {
			require_once PETSLIST_CORE_BASE_DIR . 'elementor/hooks.php'; // Hooks
			require_once PETSLIST_CORE_BASE_DIR . 'elementor/init.php'; // Elementor
		}
	}

	public function demo_importer() {
		require_once PETSLIST_CORE_BASE_DIR . 'inc/demo-importer.php';
	}

	public function load_textdomain() {
		load_plugin_textdomain( $this->plugin, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public static function social_share( $sharer = [] ) {
		include PETSLIST_CORE_BASE_DIR . 'inc/social-share.php';
	}
}

Petslist_Core::instance();