<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

namespace RadiusTheme\Petslist;

use RadiusTheme\Petslist\Customizer\Default_Data;

if ( ! class_exists( 'Options' ) ) {
	class Options {

		protected static $instance = null;

		// Sitewide static variables
		public static $options = null;

		// Template specific variables
		public static $layout = null;
		public static $sidebar = null;
		public static $header_width = null;
		public static $header_style = null;
		public static $footer_style = null;
		public static $padding_top = null;
		public static $padding_bottom = null;
		public static $has_banner_search = null;
		public static $has_breadcrumb = null;
		public static $has_tr_header;
		public static $has_top_bar;
		public static $bgimg;
		public static $menu_alignment;

		public static $inner_padding_top = null;
		public static $inner_padding_bottom = null;

		private function __construct() {
			add_action( 'after_setup_theme', [ $this, 'set_options' ] );
			add_action( 'customize_preview_init', [ $this, 'set_options' ] );
		}

		public static function instance() {
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		public function set_options() {
			$defaults = Default_Data::default_values();
			$newData  = [];
			foreach ( $defaults as $key => $dValue ) {
				$value           = get_theme_mod( $key, $defaults[ $key ] );
				$newData[ $key ] = $value;
			}
			self::$options = $newData;
		}

	}
}

