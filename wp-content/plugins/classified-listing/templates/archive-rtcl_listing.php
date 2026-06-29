<?php
/**
 * @package ClassifiedListing/Templates
 * @version 1.5.4
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

?>
	<header class="rtcl-listing-header">
		<?php if ( apply_filters( 'rtcl_show_page_title', true ) ) : ?>
			<h1 class="rtcl-listings-header-title page-title"><?php Functions::page_title(); ?></h1>
		<?php endif; ?>

		<?php
		/**
		 * Hook: rtcl_archive_description.
		 *
		 * @hooked TemplateHooks::taxonomy_archive_description - 10
		 * @hooked TemplateHooks::listing_archive_description - 10
		 */
		do_action( 'rtcl_archive_description' );
		?>
	</header>
<?php

$listing_page_id = Functions::get_page_id( 'listings' );

if ( post_password_required( $listing_page_id ) ) { ?>
	<div class="rtcl-content-wrapper">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo get_the_password_form( $listing_page_id );
		?>
	</div>
	<?php
} else {
	/**
	 * Hook: rtcl_before_main_content.
	 *
	 * @hooked rtcl_output_content_wrapper - 10 (outputs opening divs for the content)
	 */
	do_action( 'rtcl_before_main_content' );

	/**
	 * Hook: rtcl_before_listing_loop.
	 *
	 * @hooked TemplateHooks::output_all_notices() - 10
	 * @hooked TemplateHooks::listings_actions - 20
	 */
	do_action( 'rtcl_before_listing_loop' );


	Functions::listing_loop_start();

	/**
	 * Prepend listings
	 */
	do_action( 'rtcl_listing_loop_prepend_data' );

	if ( rtcl()->wp_query()->have_posts() ) {
		while ( rtcl()->wp_query()->have_posts() ) :
			rtcl()->wp_query()->the_post();

			/**
			 * Hook: rtcl_listing_loop.
			 */
			do_action( 'rtcl_listing_loop' );

			Functions::get_template_part( 'content', 'listing' );

		endwhile;
	}

	Functions::listing_loop_end();

	if ( ! rtcl()->wp_query()->have_posts() ) {
		/**
		 * Hook: rtl_no_listings_found.
		 *
		 * @hooked no_listings_found - 10
		 */
		do_action( 'rtcl_no_listings_found' );
	}

	/**
	 * Hook: rtcl_after_listing_loop.
	 *
	 * @hooked TemplateHook::pagination() - 10
	 */
	do_action( 'rtcl_after_listing_loop' );

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
}

do_action( 'rtcl_after_content_wrapper' );

if ( Functions::is_block_theme() ) {
	if ( function_exists( 'block_footer_area' ) ) {
		block_footer_area();
	}
} else {
	get_footer( 'listing' );
}