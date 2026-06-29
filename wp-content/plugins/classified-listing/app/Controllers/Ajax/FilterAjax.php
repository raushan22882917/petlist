<?php

namespace Rtcl\Controllers\Ajax;

use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;
use Rtcl\Services\EmbeddingService;
use Rtcl\Traits\SingletonTrait;
use WP_Query;

class FilterAjax {
	use SingletonTrait;

	public static function init() {
		add_action( 'wp_ajax_rtcl_ajax_filter_get_sub_terms_html', [ __CLASS__, 'get_sub_level_terms_html' ] );
		add_action( 'wp_ajax_nopriv_rtcl_ajax_filter_get_sub_terms_html', [ __CLASS__, 'get_sub_level_terms_html' ] );
		add_action( 'wp_ajax_rtcl_ajax_filter_load_data', [ __CLASS__, 'load_data' ] );
		add_action( 'wp_ajax_nopriv_rtcl_ajax_filter_load_data', [ __CLASS__, 'load_data' ] );
		add_action( 'wp_ajax_rtcl_ai_quick_search', [ __CLASS__, 'quick_search_callback' ] );
		add_action( 'wp_ajax_nopriv_rtcl_ai_quick_search', [ __CLASS__, 'quick_search_callback' ] );
	}

	public static function quick_search_callback() {
		if ( ! wp_verify_nonce( $_POST[ rtcl()->nonceId ] ?? '', rtcl()->nonceText ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Session expired!', 'classified-listing' ) ] );
		}

		if ( ! Functions::is_semantic_search_enabled() ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Unauthorized action!', 'classified-listing' ) ] );
		}

		$keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : '';

		if ( empty( $keyword ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Keyword empty!', 'classified-listing' ) ] );
		}

		$args = [
			'post_type'      => rtcl()->post_type,
			'posts_per_page' => 10,
			'post_status'    => 'publish',
		];

		$service       = new EmbeddingService();
		$similar_posts = $service->search( $keyword, 0, 'best_match' );

		if ( empty( $similar_posts ) ) {
			wp_send_json_success( [
				'html' => '<div class="rtcl-no-results">' . esc_html__( 'No results found.', 'classified-listing' ) . '</div>',
			] );
		}

		$args['post__in'] = $similar_posts;

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			wp_send_json_success( [
				'html' => '<div class="rtcl-no-results">' . esc_html__( 'No results found.', 'classified-listing' ) . '</div>',
			] );
		}

		ob_start();

		while ( $query->have_posts() ) {
			$query->the_post();
			$listing = rtcl()->factory->get_listing( get_the_ID() );
			if ( ! $listing ) {
				continue;
			}
			?>

			<div class="rtcl-ai-search-top-picks-box">
				<div class="rtcl-ai-search-item">
					<h3><a href="<?php
						the_permalink(); ?>"><?php
							the_title(); ?></a></h3>
					<span class="rtcl-location">
						<?php
						$listing->the_locations(); ?>
					</span>
				</div>
			</div>

			<?php
		}

		wp_reset_postdata();

		$html = ob_get_clean();

		wp_send_json_success( [
			'html' => $html,
		] );
	}

	public static function load_data() {
		$params     = ! empty( $_POST['params'] ) && is_array( $_POST['params'] ) ? $_POST['params'] : [];
		$filterData = ! empty( $_POST['filterData'] ) && is_array( $_POST['filterData'] ) ? $_POST['filterData'] : [];

		if ( ! empty( $_POST['is_listings'] ) ) {
			global $is_listings;
			$is_listings = true;
		}
		if ( ! empty( $_POST['is_listing'] ) ) {
			global $is_listing;
			$is_listing = absint( $_POST['is_listing'] );
		}
		if ( ! empty( $_POST['listing_term'] ) ) {
			global $listing_term;
			$listing_term = $_POST['listing_term'];
		}
		$q = ! empty( $params['q'] ) ? sanitize_text_field( wp_unslash( $params['q'] ) ) : '';
		if ( ! empty( $params['orderby'] ) ) {
			$_GET['orderby'] = $params['orderby'];
		}
		if ( ! empty( $params['view'] ) ) {
			$_GET['view'] = $params['view'];
		}
		$orderByArgs  = rtcl()->query->get_catalog_ordering_args();
		$perPage      = apply_filters( 'rtcl_loop_listing_per_page', Functions::get_option_item( 'rtcl_archive_listing_settings', 'listings_per_page' ) );
		$perPage      = max( 1, absint( $perPage ) );
		$currentPaged = max( 1, ! empty( $params['page'] ) ? absint( $params['page'] ) : 1 );

		$args = [
			'post_type'      => rtcl()->post_type,
			'post_status'    => 'publish',
			'posts_per_page' => $perPage,
			'paged'          => $currentPaged,
			'meta_query'     => [],
			'tax_query'      => [],
		];

		if ( ! empty( $orderByArgs ) && is_array( $orderByArgs ) ) {
			$args = wp_parse_args( $args, $orderByArgs );
		}

		$active_filters = [];

		if ( ! empty( $q ) ) {
			$_GET['q']        = $q;
			$active_filters[] = [
				'id'       => 'q',
				'itemId'   => 'search',
				'label'    => __( 'Keyword', 'classified-listing' ),
				'selected' => [ esc_html( $q ) => esc_html( $q ) ],
			];

			if ( Functions::is_semantic_search_enabled() ) {
				$service       = new EmbeddingService();
				$similar_posts = $service->search( $q );
				if ( ! empty( $similar_posts ) ) {
					$args['post__in'] = $similar_posts;
				} else {
					$args['s'] = $q;
				}
			} else {
				$args['s'] = $q;
			}
		}

		$params['filter_ad_type'] = ! empty( $params['filter_ad_type'] ) ? ( is_string( $params['filter_ad_type'] ) ? explode( ',', $params['filter_ad_type'] ) : $params['filter_ad_type'] ) : [];
		if ( ! empty( $params['filter_ad_type'] ) ) {
			$types    = Functions::get_listing_types();
			$selected = [];
			$adTypes  = array_filter( $params['filter_ad_type'],
				function ( $rawType ) use ( &$selected, $types ) {
					if ( ! empty( $types[ $rawType ] ) ) {
						$selected[ $rawType ] = $types[ $rawType ];

						return true;
					} else {
						return false;
					}
				} );

			if ( ! empty( $adTypes ) ) {
				$active_filters[]     = [
					'id'       => 'filter_ad_type',
					'itemId'   => 'ad_type',
					'label'    => __( 'Ad Type', 'classified-listing' ),
					'selected' => $selected,
				];
				$args['meta_query'][] = [
					'key'     => 'ad_type',
					'value'   => $adTypes,
					'compare' => 'IN',
				];
			}
		}
		$params['filter_category'] = ! empty( $params['filter_category'] ) ? ( is_string( $params['filter_category'] ) ? explode( ',', $params['filter_category'] ) : $params['filter_category'] ) : [];
		if ( ! empty( $params['filter_category'] ) ) {
			$rawIds = array_filter( array_map( 'absint', $params['filter_category'] ) );
			if ( ! empty( $rawIds ) ) {
				$terms = get_terms( [
					'taxonomy'   => rtcl()->category,
					'hide_empty' => false,
					'include'    => $rawIds,
				] );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$termIds  = [];
					$selected = [];
					foreach ( $terms as $term ) {
						$termIds[]                  = $term->term_id;
						$selected[ $term->term_id ] = $term->name;
					}
					$active_filters[]    = [
						'id'       => 'filter_category',
						'itemId'   => 'category',
						'label'    => __( 'Categories', 'classified-listing' ),
						'selected' => $selected,
					];
					$args['tax_query'][] = [
						'taxonomy' => rtcl()->category,
						'terms'    => $termIds,
						'field'    => 'term_id',
					];
				}
			}
		}

		$params['filter_location'] = ! empty( $params['filter_location'] ) ? ( is_string( $params['filter_location'] ) ? explode( ',', $params['filter_location'] ) : $params['filter_location'] ) : [];
		if ( ! empty( $params['filter_location'] ) ) {
			$rawIds = array_filter( array_map( 'absint', $params['filter_location'] ) );
			if ( ! empty( $rawIds ) ) {
				$terms = get_terms( [
					'taxonomy'   => rtcl()->location,
					'hide_empty' => false,
					'include'    => $rawIds,
				] );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$termIds  = [];
					$selected = [];
					foreach ( $terms as $term ) {
						$termIds[]                  = $term->term_id;
						$selected[ $term->term_id ] = $term->name;
					}
					$active_filters[]    = [
						'id'       => 'filter_location',
						'itemId'   => 'location',
						'label'    => __( 'Locations', 'classified-listing' ),
						'selected' => $selected,
					];
					$args['tax_query'][] = [
						'taxonomy' => rtcl()->location,
						'terms'    => $termIds,
						'field'    => 'term_id',
					];
				}
			}
		}

		$params['filter_tag'] = ! empty( $params['filter_tag'] ) ? ( is_string( $params['filter_tag'] ) ? explode( ',', $params['filter_tag'] ) : $params['filter_tag'] ) : [];
		if ( ! empty( $params['filter_tag'] ) ) {
			$rawIds = array_filter( array_map( 'absint', $params['filter_tag'] ) );

			if ( ! empty( $rawIds ) ) {
				$terms = get_terms( [
					'taxonomy'   => rtcl()->tag,
					'hide_empty' => false,
					'include'    => $rawIds,
				] );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$termIds  = [];
					$selected = [];
					foreach ( $terms as $term ) {
						$termIds[]                  = $term->term_id;
						$selected[ $term->term_id ] = $term->name;
					}
					$active_filters[]    = [
						'id'       => 'filter_tag',
						'itemId'   => 'tag',
						'label'    => __( 'Tags', 'classified-listing' ),
						'selected' => $selected,
					];
					$args['tax_query'][] = [
						'taxonomy' => rtcl()->tag,
						'terms'    => $termIds,
						'field'    => 'term_id',
					];
				}
			}
		}

		$params['filter_price'] = ! empty( $params['filter_price'] ) ? ( is_string( $params['filter_price'] ) ? explode( ',', $params['filter_price'] ) : $params['filter_price'] ) : [];
		if ( ! empty( $params['filter_price'] ) ) {
			$price = array_map( 'intval', $params['filter_price'] );

			if ( $n = count( $price ) ) {
				if ( 2 == $n ) {
					$args['meta_query'][] = [
						'relation' => 'OR',
						[
							'key'     => 'price',
							'value'   => array_map( 'intval', array_values( $price ) ),
							'type'    => 'NUMERIC',
							'compare' => 'BETWEEN',
						],
						[
							'relation' => 'AND',
							[
								'key'     => '_rtcl_max_price',
								'value'   => array_map( 'intval', array_values( $price ) ),
								'type'    => 'NUMERIC',
								'compare' => 'BETWEEN',
							],
							[
								'key'     => '_rtcl_max_price',
								'compare' => 'EXISTS',
							],
						],
					];
				} else {
					if ( ! empty( $price[1] ) ) {
						$args['meta_query'][] = [
							'relation' => 'OR',
							[
								'relation' => 'AND',
								[
									'key'     => 'price',
									'value'   => [ .01, intval( $price[1] ) ],
									'type'    => 'NUMERIC',
									'compare' => 'BETWEEN',
								],
								[
									'key'     => '_rtcl_max_price',
									'compare' => 'NOT EXISTS',
								],
							],
							[
								'relation' => 'AND',
								[
									'key'     => '_rtcl_max_price',
									'value'   => (int) $price[1],
									'type'    => 'NUMERIC',
									'compare' => '<=',
								],
								[
									'key'     => '_rtcl_max_price',
									'compare' => 'EXISTS',
								],
							],
						];
					} else {
						$args['meta_query'][] = [
							'key'     => 'price',
							'value'   => (int) $price[0],
							'type'    => 'NUMERIC',
							'compare' => '>=',
						];
					}
				}
				$active_filters[] = [
					'id'       => 'filter_price',
					'itemId'   => 'price_range',
					'label'    => __( 'Price Filter', 'classified-listing' ),
					'selected' => [ 'filter_price' => implode( ' - ', $price ) ],
				];
			}
		}

		$distance = ! empty( $params['distance'] ) ? absint( $params['distance'] ) : 0;
		if ( ! empty( $distance ) ) {
			$lat = ! empty( $params['center_lat'] ) ? trim( $params['center_lat'] ) : ''; /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
			$lan = ! empty( $params['center_lng'] ) ? trim( $params['center_lng'] ) : ''; /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */

			if ( $lat && $lan ) {
				$rs_data        = Options::radius_search_options();
				$rtcl_geo_query = [
					'lat_field' => 'latitude',
					'lng_field' => 'longitude',
					'latitude'  => $lat,
					'longitude' => $lan,
					'distance'  => $distance,
					'units'     => ! empty( $rs_data['units'] ) ? $rs_data['units'] : 'km',
				];
				$geo_query      = array_filter( apply_filters( 'rtcl_ajax_filter_query_geo_query', $rtcl_geo_query ) );
				if ( ! empty( $geo_query ) ) {
					$args['rtcl_geo_query'] = $geo_query;
					$active_filters[]       = [
						'id'       => 'radius_filter',
						'itemId'   => 'radius_filter',
						'label'    => __( 'Radius Filter', 'classified-listing' ),
						'selected' => [ 'distance' => $distance . ' ' . $rtcl_geo_query['units'] ],
					];
				}
			}
		}

		$data = apply_filters( 'rtcl_ajax_filter_before_query_modify_data', compact( 'args', 'active_filters' ), $params, $filterData );

		if ( ! empty( $data['args'] ) ) {
			$args = $data['args'];
		}

		if ( ! empty( $data['active_filters'] ) ) {
			$active_filters = $data['active_filters'];
		}

		$args = apply_filters( 'rtcl_ajax_filter_load_data_query_args', $args, $params, $filterData );

		if ( ! empty( $args['tax_query'] ) ) {
			if ( empty( $args['tax_query']['relation'] ) && count( $args['tax_query'] ) > 1 ) {
				$args['tax_query']['relation'] = 'AND';
			}
		}
		if ( ! empty( $args['meta_query'] ) ) {
			if ( empty( $args['meta_query']['relation'] ) && count( $args['meta_query'] ) > 1 ) {
				$args['meta_query']['relation'] = 'AND';
			}
		}

		$query    = new WP_Query( $args );
		$listings = null;
		if ( $query->have_posts() ) {
			do_action( 'rtcl_ajax_filter_query_before_loop', $query, $params, $filterData );
			ob_start();
			while ( $query->have_posts() ) :
				$query->the_post();
				do_action( 'rtcl_listing_loop' );
				Functions::get_template_part( 'content', 'listing' );
			endwhile;
			$listings = ob_get_clean();
			do_action( 'rtcl_ajax_filter_query_after_loop', $query, $params, $filterData );
		}

		$response = [
			'params'         => $params,
			'active_filters' => $active_filters,
			'listings'       => $listings,
			'pagination'     => [
				'pages'         => $query->max_num_pages,
				'current_page'  => $currentPaged,
				'current_items' => $query->post_count,
				'items'         => $query->found_posts,
				'per_page'      => $perPage,
			],
		];
		wp_send_json_success( apply_filters( 'rtcl_ajax_filter_load_data_response', $response, $params, $filterData ) );
	}

	public static function get_sub_level_terms_html() {
		do_action( 'rtcl_set_local' );

		$args = wp_parse_args(
			$_REQUEST,
			[
				'taxonomy' => rtcl()->category,
				'parent'   => 0,
				'values'   => [],
			],
		);

		wp_send_json_success( Functions::get_ajax_filter_sub_terms_html( $args ) );
	}
}