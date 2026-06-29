<?php

namespace Rtcl\Shortcodes;


use Rtcl\Helpers\Functions;

class Categories {

	public static function output( $atts ) {

		$settings = shortcode_atts( [
			'view'           => 'grid',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'columns'        => 4,
			'types'          => '',
			'description'    => true,
			'excerpt_length' => '',
			'show_cat'       => '',
			'show_count'     => true,
			'icon'           => true,
			'image'          => false,
			'hide_empty'     => false,
			'pad_counts'     => true,
			'equal_height'   => true
		], $atts, 'rtcl_categories' );

		// Enqueue dependencies
		wp_enqueue_style( 'rtcl-public' );
		wp_enqueue_script( 'rtcl-public' );

		$args = [
			'taxonomy'     => rtcl()->category,
			'orderby'      => $settings['orderby'],
			'order'        => $settings['order'],
			'hide_empty'   => ! empty( $settings['hide_empty'] ) ? 1 : 0,
			'include'      => $settings['show_cat'] ? explode( ',', $settings['show_cat'] ) : [],
			'parent'       => 0,
			'hierarchical' => false,
		];
		if ( $settings['orderby'] == 'custom' ) {
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = '_rtcl_order'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		}
		if ( $settings['types'] && $types = explode( ',', $settings['types'] ) ) {
			if ( is_array( $types ) && ! empty( $types ) ) {
				$args['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				                        [
					                        'key'     => '_rtcl_types',
					                        'value'   => $types,
					                        'compare' => 'IN'
				                        ]
				];
			}
		}

		$terms         = get_terms( apply_filters( 'rtcl_shortcode_categories_terms_args', $args ) );
		$allowed_views = apply_filters( 'rtcl_category_shortcode_allowed_views', [ 'grid', 'list' ] );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$view = isset( $settings['view'] ) && in_array( $settings['view'], $allowed_views, true ) ? sanitize_key( $settings['view'] )
				: 'grid';

			Functions::get_template( "categories/categories-{$view}", [
				'settings' => $settings,
				'terms'    => $terms
			] );

		} else {
			echo '<span>' . esc_html__( 'No Results Found.', 'classified-listing' ) . '</span>';
		}

	}

}
