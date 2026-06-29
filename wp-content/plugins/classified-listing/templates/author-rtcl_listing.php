<?php
/**
 * @package ClassifiedListing/Templates
 * @version 2.2.1.1
 */

use Rtcl\Helpers\Functions;

defined( 'ABSPATH' ) || exit;

if ( Functions::is_block_theme() ) {
	if ( function_exists( 'wp_load_block_template' ) ) {
		wp_load_block_template();
	}

	if ( function_exists( 'block_header_area' ) ) {
		block_header_area();
	}
} else {
	get_header( 'listing' );
}

do_action( 'rtcl_before_content_wrapper' );

/**
 * Hook: rtcl_before_main_content.
 *
 * @hooked rtcl_output_content_wrapper - 10 (outputs opening divs for the content)
 */
do_action( 'rtcl_before_main_content' );

Functions::get_template( 'listing/author-content' );

/**
 * Hook: rtcl_after_main_content.
 *
 * @hooked rtcl_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'rtcl_after_main_content' );

/**
 * Hook: rtcl_sidebar.
 *
 * @hooked rtcl_get_sidebar - 10
 */

do_action( 'rtcl_sidebar' );

do_action( 'rtcl_after_content_wrapper' );

if ( Functions::is_block_theme() ) {
	if ( function_exists( 'block_footer_area' ) ) {
		block_footer_area();
	}
} else {
	get_footer( 'listing' );
}
