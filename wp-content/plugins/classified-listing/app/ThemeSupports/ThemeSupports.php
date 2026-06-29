<?php

namespace Rtcl\ThemeSupports;

use Rtcl\Controllers\Hooks\TemplateHooks;
use Rtcl\Helpers\Functions;
use Rtcl\Resources\ThemeSupportCss;

class ThemeSupports {
	/**
	 * Current Theme name
	 *
	 * @var string
	 */
	private static $current_theme = '';

	/**
	 * @return void
	 */
	static function init() {
		self::$current_theme = get_template();
		do_action( 'rtcl_add_theme_support', self::$current_theme );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'css_theme_support' ], 99 );
		add_filter( 'astra_blog_post_per_page_exclusions', [ __CLASS__, 'astra_exclude_post_type' ] );
		if ( 'astra' === self::$current_theme ) {
			$astra_sidebar = apply_filters( 'astra_get_sidebar', 'sidebar-1' );
			if ( ! is_active_sidebar( $astra_sidebar ) && ! is_active_sidebar( 'rtcl-archive-sidebar' ) ) {
				remove_action( 'rtcl_sidebar', [ TemplateHooks::class, 'get_sidebar' ], 10 );
			}
		}
	}

	/**
	 * @return void
	 */
	static function css_theme_support() {
		if ( 'twentytwenty' === self::$current_theme ) {
			echo '<style id="rtcl-twentytwenty" media="screen">';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo ThemeSupportCss::twentyTwenty();
			echo '</style>';
//			wp_add_inline_style('twentytwenty-style', ThemeSupportCss::twentyTwenty());
		} elseif ( 'divi' === strtolower( self::$current_theme ) ) {
			wp_add_inline_style( 'rtcl-public', ThemeSupportCss::divi() );
		}
	}

	/**
	 * @param $exclusions
	 *
	 * @return mixed
	 */
	public static function astra_exclude_post_type( $exclusions ) {
		$exclusions[] = rtcl()->post_type;

		return $exclusions;
	}
}