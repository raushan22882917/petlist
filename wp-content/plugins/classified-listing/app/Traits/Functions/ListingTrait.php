<?php


namespace Rtcl\Traits\Functions;


use Rtcl\Helpers\Functions;
use Rtcl\Models\Listing;
use Rtcl\Resources\Options;

trait ListingTrait {

	/**
	 * Get the character limit for listing titles
	 *
	 * @return int The maximum number of characters allowed for listing titles
	 */
	static function get_title_character_limit() {
		return apply_filters( 'rtcl_listing_get_title_character_limit', absint( Functions::get_option_item( 'rtcl_moderation_settings', 'title_max_limit' ) ) );
	}

	/**
	 * Get the character limit for listing descriptions
	 *
	 * @return int The maximum number of characters allowed for listing descriptions
	 */
	static function get_description_character_limit() {
		return apply_filters( 'rtcl_listing_get_description_character_limit',
			absint( Functions::get_option_item( 'rtcl_moderation_settings', 'description_max_limit' ) ) );
	}


	/**
	 * Check if gallery slider is enabled for single listing
	 *
	 * @return bool True if gallery slider is enabled, false otherwise
	 */
	static function is_gallery_slider_enabled() {
		$misc_settings = ! Functions::get_option_item( 'rtcl_single_listing_settings', 'disable_gallery_slider', false, 'checkbox' );

		return (bool) apply_filters( 'rtcl_single_listing_slider_enabled', $misc_settings );
	}


	/**
	 * @param       $cat_id
	 * @param  Listing/null $listing
	 *
	 * @return mixed|void
	 * @var Listing $listing
	 */
	/**
	 * Get HTML for listing form price unit selection
	 *
	 * @param  int  $cat_id  Category ID
	 * @param  Listing|null  $listing  Listing object
	 *
	 * @return mixed|void HTML content for price unit selection
	 */
	static function get_listing_form_price_unit_html( $cat_id, $listing = null ) {
		if ( ! $cat_id && ! $listing ) {
			return;
		}

		$price_unit  = null;
		$price_units = [];
		if ( is_a( $listing, Listing::class ) ) {
			$price_units = $listing->get_price_units();
			$price_unit  = $listing->get_price_unit();
		} elseif ( $cat_id ) {
			$price_units = self::get_category_price_units( $cat_id );
		}
		$price_unit_list = Options::get_price_unit_list();
		$html            = Functions::get_template_html( 'listing-form/price-unit',
			compact( 'price_unit_list', 'price_units', 'price_unit', 'cat_id', 'listing' ) );

		return apply_filters( 'rtcl_get_listing_form_price_unit_html', $html, $cat_id, $listing );
	}

	/**
	 * @param $cat_id
	 *
	 * @return array
	 */
	/**
	 * Get price units for a specific category
	 *
	 * @param  int  $cat_id  Category ID
	 *
	 * @return array Array of price units
	 */
	static function get_category_price_units( $cat_id ) {
		$price_units = get_term_meta( $cat_id, '_rtcl_price_units' );
		if ( empty( $price_units ) && $term = get_term( $cat_id, rtcl()->category ) ) {
			if ( $term->parent ) {
				$price_units = get_term_meta( $term->parent, '_rtcl_price_units' );
			}
		}

		return $price_units;
	}


	/**
	 * @param $cat_id
	 *
	 * @return boolean
	 */
	/**
	 * Check if category has price units
	 *
	 * @param  int  $cat_id  Category ID
	 *
	 * @return boolean True if category has price units, false otherwise
	 */
	static function category_has_price_units( $cat_id ) {
		return count( self::get_category_price_units( $cat_id ) ) > 0;
	}

	/**
	 * Disable the listing details page view and show 404
	 *
	 * @return void
	 */
	public static function disable_listing_details_page_view() {
		if ( ! Functions::is_listing() ) {
			return;
		}

		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}

	/**
	 * Display listing details template for logged in users
	 *
	 * @return void
	 */
	public static function listing_details_for_logged_in_users() {
		Functions::get_template( 'single-listing-restricted' );
	}

	/**
	 * Create a new term in the specified taxonomy
	 *
	 * @param  string  $taxonomy  Taxonomy name
	 * @param  array  $data  Term data including name, slug, parent, description, order, meta and child
	 *
	 * @return array Response array with success status, data and message
	 */
	public static function create_term( $taxonomy, $data ) {
		$data = wp_parse_args(
			$data,
			[
				'name'        => '',
				'slug'        => '',
				'parent'      => 0,
				'description' => '',
				'order'       => 0,
				'meta'        => [],
				'child'       => [],
			],
		);

		$return = [
			'success' => false,
			'data'    => null,
			'message' => __( 'Item is empty.', 'classified-listing' ),
		];
		try {
			if ( $data['name'] ) {
				$unique     = ! empty( $data['slug'] ) ? $data['slug'] : $data['name'];
				$term_exist = term_exists( $unique, $taxonomy );
				if ( empty( $term_exist ) ) {
					$term = wp_insert_term(
						$data['name'],
						$taxonomy,
						[
							'parent'      => isset( $data['parent'] ) ? absint( $data['parent'] ) : 0,
							'slug'        => $data['slug'],
							'description' => $data['description'],
						],
					);
					if ( ! is_wp_error( $term ) ) {
						update_term_meta( $term['term_id'], '_rtcl_order', absint( $data['order'] ) );
						if ( is_array( $data['meta'] ) && ! empty( $data['meta'] ) ) {
							foreach ( $data['meta'] as $meta_key => $meta ) {
								update_term_meta( $term['term_id'], $meta_key, $meta );
							}
						}
						$return['success'] = true;
						$return['data']    = $term;
						/* translators:  name */
						$return['message'] = sprintf( esc_html__( '%s Successfully created', 'classified-listing' ), esc_html( $data['name'] ) );
					} else {
						$return['message'] = $term->get_error_message();
					}
				} else {
					$return['success'] = true;
					$return['data']    = $term_exist;
					/* translators:  Name */
					$return['message'] = sprintf( esc_html__( '%s is already exist', 'classified-listing' ), esc_html( $data['name'] ) );
				}
			}
		} catch ( \Exception $e ) {
			$return['message'] = $e->getMessage();
		}

		return $return;
	}

	/**
	 * Set listing term for a post
	 *
	 * @param  string  $name  Term name
	 * @param  string  $taxonomy  Taxonomy name (default: 'rtcl_category')
	 * @param  int  $post_id  Post ID
	 *
	 * @return void
	 */
	public static function set_listing_term( $name = '', $taxonomy = 'rtcl_category', $post_id = 0 ) {
		$name = trim( $name );

		if ( $name ) {
			$cat_id  = null;
			$cat_ids = [];
			$parent  = 0;

			$terms = explode( '>', $name );
			$limit = apply_filters( 'rtcl_import_terms_hierarchy_limit', 3 );

			if ( ! empty( $terms ) ) {
				foreach ( $terms as $index => $slug ) {
					if ( $limit === $index ) {
						break;
					}

					$check_term = term_exists( $slug, $taxonomy );

					if ( ! $check_term ) {
						$title  = self::slug_to_title( $slug );
						$cat_id = wp_insert_term(
							$title,
							$taxonomy,
							[
								'slug'   => sanitize_title( $slug ),
								'parent' => $parent,
							],
						);
						if ( is_wp_error( $cat_id ) ) {
							continue;
						}
						$cat_ids[] = absint( $cat_id['term_id'] );
					} else {
						$cat_id          = $check_term;
						$existing_parent = $cat_id['term_id'];
						while ( $existing_parent ) {
							$cat_ids[]       = absint( $existing_parent );
							$existing_term   = get_term_by( 'ID', $existing_parent, $taxonomy );
							$existing_parent = $existing_term->parent;
						}
					}

					$parent = $cat_id['term_id'] ?? 0;
				}
				if ( ! is_wp_error( $cat_id ) && ! empty( $cat_ids ) ) {
					wp_set_object_terms( $post_id, $cat_ids, $taxonomy );
				}
			}
		}
	}

	/**
	 * Convert taxonomy term slug to a proper title
	 *
	 * @param  string  $slug  The taxonomy term slug.
	 *
	 * @return string The formatted title.
	 */
	public static function slug_to_title( $slug ) {
		$title = str_replace( [ '-', '_' ], ' ', $slug );

		return ucwords( $title );
	}

	/**
	 * Prepares a list of social links by filtering and validating input data.
	 *
	 * @param  string  $data  Comma-separated list of social profiles,
	 *                     where each profile consists of a key and URL separated by a pipe character.
	 *
	 * @return array Associative array of valid social links with the profile key as the key and URL as the value.
	 */
	public static function prepare_listing_social_links( $data ) {
		$socials = [];
		if ( ! empty( $data ) ) {
			$all_profiles = explode( ',', $data );

			$social_profile_list = array_keys( Options::get_social_profiles_list() );

			if ( is_array( $all_profiles ) ) {
				foreach ( $all_profiles as $profile ) {
					$social_profile = explode( '|', trim( $profile ) );

					$social_key = isset( $social_profile[0] ) ? trim( $social_profile[0] ) : '';
					$social_url = isset( $social_profile[1] ) ? trim( $social_profile[1] ) : '';
					$social_url = $social_url && filter_var( $social_url, FILTER_VALIDATE_URL ) ? $social_url : '';

					if ( $social_key && $social_url && in_array( $social_key, $social_profile_list ) ) {
						$socials[ $social_key ] = $social_url;
					}
				}
			}
		}

		return $socials;
	}

	/**
	 * Get total published listings
	 */
	public static function need_listings_embedding() {
		$listings = get_posts( [
			'post_type'      => rtcl()->post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => [
				[
					'key'     => '_has_embedding',
					'compare' => 'NOT EXISTS',
				],
			],
		] );

		return count( $listings );
	}

}