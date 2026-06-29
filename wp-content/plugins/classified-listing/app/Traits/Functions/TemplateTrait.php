<?php

namespace Rtcl\Traits\Functions;

use Rtcl\Helpers\Functions;

trait TemplateTrait {
	use SettingsTrait;

	static function page_title( $echo = true ) {

		if ( is_search() ) {
			/* translators: %s: search query */
			$page_title = sprintf( __( 'Search results: &ldquo;%s&rdquo;', 'classified-listing' ), get_search_query() );

			if ( get_query_var( 'paged' ) ) {
				/* translators: %s: page number */
				$page_title .= sprintf( __( '&nbsp;&ndash; Page %s', 'classified-listing' ), get_query_var( 'paged' ) );
			}
		} elseif ( is_tax() ) {

			$page_title = single_term_title( '', false );

		} else {
			$listings_page_id = self::get_page_id( 'listings' );
			$page_title = get_the_title( $listings_page_id );

			$category = get_query_var( '__cat' )
				? get_query_var( '__cat' )
				: ( isset( $_GET['rtcl_category'] ) ? sanitize_text_field( $_GET['rtcl_category'] )
					: '' ); /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
			if ( !empty( $category ) ) {
				$cat_term = get_term_by( 'slug', $category, rtcl()->category );
				$page_title = $cat_term->name ?? '';
			}

			$location = get_query_var( '__loc' )
				? get_query_var( '__loc' )
				: ( isset( $_GET['rtcl_location'] ) ? sanitize_text_field( $_GET['rtcl_location'] )
					: '' ); /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
			if ( !empty( $location ) ) {
				$loc_term = get_term_by( 'slug', $location, rtcl()->location );
				$page_title .= isset( $loc_term->name ) ? __( ' in ', 'classified-listing' ) . $loc_term->name : '';
			}
		}

		$page_title = apply_filters( 'rtcl_page_title', $page_title );

		if ( $echo ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $page_title;
		} else {
			return $page_title;
		}
	}

	static function get_loop_display_mode() {
		// Only return listings when filtering things.
		if ( self::get_loop_prop( 'is_search' ) || self::get_loop_prop( 'is_filtered' ) ) {
			return 'listings';
		}
		$display_type = '';
		if ( Functions::is_listings() ) {
			$display_type = get_option( 'rtcl_listings_page_display', '' );
		} elseif ( Functions::is_listing_taxonomy() ) {
			$term = get_queried_object();
			$display_type = get_term_meta( $term->term_id, 'display_type', true ); //TODO : Need to add in future
			$display_type = '' === $display_type ? apply_filters( 'rtcl_taxonomy_archive_display', 'sub_taxonomy' ) : $display_type;

			if ( in_array( $display_type, [ 'sub_taxonomy', 'both' ], true ) ) {
				if ( empty( self::get_sub_terms( $term->taxonomy, $term->term_id ) ) ) {
					$display_type = 'listings';
				}
			}

		}

		if ( ( Functions::is_listings() || 'sub_taxonomy' !== $display_type ) && 1 < self::get_loop_prop( 'current_page' ) ) {
			return 'listings';
		}

		// Ensure valid value.
		if ( '' === $display_type || !in_array( $display_type, [ 'listings', 'sub_taxonomy', 'both' ], true ) ) {
			$display_type = 'listings';
		}

		return apply_filters( 'rtcl_get_loop_display_mode', $display_type );
	}

	public static function is_enable_template_support() {
		return current_theme_supports( 'rtcl' ) || ( 'rtcl_template' === self::get_base_template() );
	}

}