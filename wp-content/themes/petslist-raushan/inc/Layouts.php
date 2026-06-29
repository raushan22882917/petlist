<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

namespace RadiusTheme\Petslist;

use Rtcl\Helpers\Functions;

class Layouts {

	protected static $instance = null;

	public $base;
	public $type;
	public $meta_value;

	public function __construct() {
		$this->base = 'petslist';

		add_action( 'template_redirect', [ $this, 'layout_settings' ] );
	}

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function layout_settings() {
		$is_listing = $is_listing_archive = false;

		if ( class_exists( 'Rtcl' ) ) {
			$is_listing_archive = Functions::is_listings() || Functions::is_listing_taxonomy();
		}

		if ( $is_listing_archive ) {
			$is_listing = true;
		}
		// Single Pages
		if ( ( is_single() || is_page() ) && ! $is_listing ) {
			$post_type        = get_post_type();
			$post_id          = get_the_id();
			$this->meta_value = get_post_meta( $post_id, "{$this->base}_layout_settings", true );

			switch ( $post_type ) {
				case 'post':
					$this->type = 'single_post';
					break;
				case 'rtcl_listing':
					$this->type = 'listing_single';

					Options::$options[ $this->type . '_layout' ]  = 'right-sidebar';
					break;
				default:
					$this->type = 'page';
			}

			Options::$layout            = $this->meta_layout_option( 'layout' );
			Options::$padding_top       = $this->meta_layout_option( 'padding_top' );
			Options::$padding_bottom    = $this->meta_layout_option( 'padding_bottom' );
			Options::$has_top_bar       = $this->meta_layout_global_option( 'top_bar', true );
			Options::$header_width      = $this->meta_layout_global_option( 'header_width' );
			Options::$header_style      = $this->meta_layout_global_option( 'header_style' );
			Options::$menu_alignment    = $this->meta_layout_global_option( 'menu_alignment' );
			Options::$footer_style      = $this->meta_layout_global_option( 'footer_style' );
			Options::$has_tr_header     = $this->meta_layout_global_option( 'tr_header', true );
			Options::$has_breadcrumb    = $this->meta_layout_global_option( 'breadcrumb', true );

		} // Blog and Archive
		elseif ( is_home() || is_archive() || is_search() || is_404() || $is_listing ) {
			if ( is_404() ) {
				$this->type                                   = 'error';
				Options::$options[ $this->type . '_layout' ]  = 'full-width';
			} elseif ( $is_listing_archive ) {
				$this->type = 'listing_archive';
			} else {
				$this->type = 'blog';
			}

			Options::$layout            = $this->layout_option( 'layout' );
			Options::$padding_top       = $this->layout_option( 'padding_top' );
			Options::$padding_bottom    = $this->layout_option( 'padding_bottom' );
			Options::$has_breadcrumb    = $this->layout_global_option( 'breadcrumb', true );
			Options::$has_top_bar       = $this->layout_global_option( 'top_bar', true );
			Options::$header_width      = $this->layout_global_option( 'header_width' );
			Options::$menu_alignment    = $this->layout_global_option( 'menu_alignment' );
			Options::$header_style      = $this->layout_global_option( 'header_style' );
			Options::$footer_style      = $this->layout_global_option( 'footer_style' );
			Options::$has_tr_header     = $this->layout_global_option( 'tr_header', true );
		}
	}

	// Single
	private function meta_layout_global_option( $key, $is_bool = false ) {
		$layout_key = $this->type . '_' . $key;

		$meta      = ! empty( $this->meta_value[ $key ] ) ? $this->meta_value[ $key ] : 'default';
		$op_layout = Options::$options[ $layout_key ] ? Options::$options[ $layout_key ] : 'default';
		$op_global = Options::$options[ $key ];

		if ( $meta != 'default' ) {
			$result = $meta;
		} elseif ( $op_layout != 'default' ) {
			$result = $op_layout;
		} else {
			$result = $op_global;
		}
		if ( $is_bool ) {
			$result = ( $result === 1 || $result === 'on' ) ? true : false;
		}

		return $result;
	}

	// Single
	private function meta_layout_option( $key ) {
		$layout_key = $this->type . '_' . $key;

		$meta      = ! empty( $this->meta_value[ $key ] ) ? $this->meta_value[ $key ] : 'default';
		$op_layout = Options::$options[ $layout_key ];


		if ( $meta != 'default' ) {
			$result = $meta;
		} else {
			$result = $op_layout;
		}

		return $result;
	}

	// Archive
	private function layout_global_option( $key, $is_bool = false ) {
		$layout_key = $this->type . '_' . $key;

		$op_layout = Options::$options[ $layout_key ] ? Options::$options[ $layout_key ] : 'default';
		$op_global = Options::$options[ $key ];

		if ( $op_layout != 'default' ) {
			$result = $op_layout;
		} else {
			$result = $op_global;
		}
		if ( $is_bool ) {
			$result = ( $result === 1 || $result === 'on' ) ? true : false;
		}

		return $result;
	}

	// Archive
	private function layout_option( $key ) {
		$layout_key = $this->type . '_' . $key;
		$op_layout  = Options::$options[ $layout_key ];

		return $op_layout;
	}

	private function bgimg_option( $key, $is_single = true ) {
		$layout_key = $this->type . '_' . $key;

		if ( $is_single ) {
			$meta = ! empty( $this->meta_value[ $key ] ) ? $this->meta_value[ $key ] : '';
		} else {
			$meta = '';
		}

		$op_layout = Options::$options[ $layout_key ];
		$op_global = Options::$options[ $key ];

		if ( $meta ) {
			$src = wp_get_attachment_image_src( $meta, 'full', true );
			$img = $src[0];
		} elseif ( ! empty( $op_layout['url'] ) ) {
			$img = $op_layout['url'];
		} elseif ( ! empty( $op_global['url'] ) ) {
			$img = $op_global['url'];
		} else {
			$img = Helper::get_img( 'banner.jpg' );
		}

		return $img;
	}

}