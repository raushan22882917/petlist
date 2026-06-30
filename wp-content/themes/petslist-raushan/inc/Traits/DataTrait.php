<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace RadiusTheme\Petslist\Traits;
use RadiusTheme\Petslist\Options;
use RtclPro\Helpers\Fns;

trait DataTrait {

  protected static $instance = null;

  public static function instance() {
    if ( null == self::$instance ) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /* = Petslist Logo Settings
  ==========================================================*/
  public static function petslist_main_logo(){
    $meta_logo = get_post_meta(get_the_ID(), 'petslist_layout_settings', true);
    if ( !empty($meta_logo['petslist_logo_version']) ) {
      $logo_one = wp_get_attachment_image( $meta_logo['petslist_logo_version'], 'full' );
    } elseif ( function_exists( 'petslist_logo_img' ) ) {
      $logo_one = petslist_logo_img( 'light' );
    } else {
      $logo_one = '';
    }
    return $logo_one;
  }
  
  	public static function rt_logo_two(){
		$meta_logo = get_post_meta(get_the_ID(), 'petslist_layout_settings', true);
		if ( !empty($meta_logo['petslist_logo_version']) ) {
			$logo_two = wp_get_attachment_image( $meta_logo['petslist_logo_version'], 'full' );
		} elseif ( function_exists( 'petslist_logo_img' ) ) {
			$logo_two = petslist_logo_img( 'dark' );
		} else {
			$logo_two = '';
		}
		return $logo_two;
  	}

  	public static function rt_mobile_logo(){
		if ( function_exists( 'petslist_logo_img' ) ) {
			return petslist_logo_img( 'mobile' );
		}
		return '';
  	}

  	public static function get_primary_color() {
		return apply_filters( 'rt_primary_color', Options::$options['primary_color'] );
	}

	public static function get_secondary_color() {
		return apply_filters( 'rt_secondary_color', Options::$options['secondary_color'] );
	}

	public static function get_body_color() {
		return apply_filters( 'rt_body_color', Options::$options['body_color'] );
	}

	public static function get_heading_color() {
		return apply_filters( 'rt_heading_color', Options::$options['heading_color'] );
	}

 	public static function get_button_color1() {
		return apply_filters( 'rt_heading_color_1', Options::$options['button_color_1'] );
	}

  	public static function get_button_color2() {
		return apply_filters( 'rt_heading_color_2', Options::$options['button_color_2'] );
	}

  	public static function is_chat_enabled() {
		if ( Options::$options['header_chat_icon'] && class_exists( 'Rtcl' ) && class_exists( 'RtclPro' ) ) {
			if ( Fns::is_enable_chat() ) {
				return true;
			}
		}
		return false;
	}

	public static function is_header_link_btn_enabled() {
		$btn_flag = get_theme_mod( 'header_btn' );
		if ( empty( $btn_flag ) ) {
			return false;
		}
		return true;
	}

  	public static function is_header_login_enabled() {
		$login_btn = get_theme_mod( 'header_login_icon' );
		if ( empty( $login_btn ) ) {
			return false;
		}
		return true;
	}

  	public static function is_header_chat_enabled() {
		$login_btn = get_theme_mod( 'header_chat_icon' );
		if ( empty( $login_btn ) ) {
			return false;
		}
		return true;
	}

	public static function is_trheader_enable() {
		$tr_header = get_theme_mod( 'tr_header' );
		if ( empty( $tr_header ) ) {
			return false;
		}
		return true;
	}

	public static function is_copyright_area_enabled() {
		$copyright_area_flag = get_theme_mod( 'copyright_area' );
		if ( empty( $copyright_area_flag ) ) {
			return false;
		}
		return true;
	}
	
	public static function is_listing_archive_title_enabled() {
		$archive_title_flag = get_theme_mod( 'listing_archive_title' );
		if ( empty( $archive_title_flag ) ) {
			return false;
		}
		return true;
	}

	public static function is_listing_archive_custom_search_filter() {
		$archive_filter_type_flag = get_theme_mod( 'listing_archive_filter_type' );
		if ( empty( $archive_filter_type_flag ) ) {
			return false;
		}
		return true;
	}

	public static function is_preloader_enabled() {
		$preloader = get_theme_mod( 'preloader' );
		if ( empty( $preloader ) ) {
			return false;
		}
		return true;
	}

	public static function rt_grid_options(){
		return [
			'1'  => esc_html__( '1 Columns', 'petslist' ),
			'2'  => esc_html__( '2 Columns', 'petslist' ),
			'3'  => esc_html__( '3 Columns', 'petslist' ),
			'4'  => esc_html__( '4 Columns', 'petslist' ),
			'5'  => esc_html__( '5 Columns', 'petslist' ),
			'6'  => esc_html__( '6 Columns', 'petslist' ),
		];
	}

  public static function rt_number_options(){
		return [
			'1'  => esc_html__( '1', 'petslist' ),
			'2'  => esc_html__( '2', 'petslist' ),
			'3'  => esc_html__( '3', 'petslist' ),
			'4'  => esc_html__( '4', 'petslist' ),
			'5'  => esc_html__( '5', 'petslist' ),
			'6'  => esc_html__( '6', 'petslist' ),
		];
	}

}

