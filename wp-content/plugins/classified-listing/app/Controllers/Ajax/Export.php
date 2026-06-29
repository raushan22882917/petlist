<?php

namespace Rtcl\Controllers\Ajax;

use Rtcl\Helpers\Functions;
use Rtcl\Models\Form\Form;
use Rtcl\Services\FormBuilder\FBField;
use Rtcl\Services\FormBuilder\FBHelper;

class Export {
	function __construct() {
		add_action( 'wp_ajax_rtcl_taxonomy_settings_export', [ __CLASS__, 'rtcl_taxonomy_settings_export' ] );
		add_action( 'wp_ajax_rtcl_listings_export', [ __CLASS__, 'rtcl_listings_export' ] );
		add_action( 'wp_ajax_rtcl_remove_temporary_file', [ __CLASS__, 'remove_temporary_file' ] );
	}

	/**
	 * @return void
	 */
	public static function remove_temporary_file() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Unauthorized access!!!', 'classified-listing' ) );
		}

		if ( ! wp_verify_nonce( $_POST[ rtcl()->nonceId ] ?? '', rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session Expired!!', 'classified-listing' ) );
		}

		if ( empty( $_POST['file_path'] ) ) {
			wp_send_json_error( esc_html__( 'Unknown file!!', 'classified-listing' ) );
		}

		$path     = sanitize_text_field( $_POST['file_path'] );
		$filename = basename( $path );
		$filepath = untrailingslashit( RTCL_PATH ) . '/assets/export/' . $filename;

		if ( file_exists( $filepath ) ) {
			wp_delete_file( $filepath );
			wp_send_json_success( esc_html__( 'File removed successfully!!!', 'classified-listing' ) );
		}

		wp_send_json_error( esc_html__( 'Not allowed to remove the file!!', 'classified-listing' ) );
	}

	public static function rtcl_taxonomy_settings_export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Unauthorized access!!!', 'classified-listing' ) );
		}

		if ( ! wp_verify_nonce( $_POST[ rtcl()->nonceId ] ?? '', rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session Expired!!', 'classified-listing' ) );
		}

		$export_types = [ 'categories', 'locations', 'types', 'settings' ];

		$download_path = RTCL_PATH . '/assets/export/';
		$filename      = 'data_' . time() . '.json';

		self::export_as_json( $download_path, $export_types, $filename );

		$data = [
			'path'         => rtcl()->get_assets_uri( '/export/' . $filename ),
			'file_name'    => $filename,
			'export_types' => $export_types,
		];

		wp_send_json_success( $data );
	}

	public static function export_as_json( $download_path, $export_types, $filename ) {
		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$results = [];

		if ( in_array( 'categories', $export_types ) ) {
			$first_categories = get_terms( [ 'taxonomy' => 'rtcl_category', 'hide_empty' => false, 'parent' => 0 ] );
			$categories       = [];
			if ( ! is_wp_error( $first_categories ) && is_array( $first_categories ) ) {
				foreach ( $first_categories as $first_value ) {
					$first_value->meta = get_term_meta( $first_value->term_id );

					//second categories
					$get_second_categories = get_terms( [ 'taxonomy' => 'rtcl_category', 'hide_empty' => false, 'parent' => $first_value->term_id ] );
					$second_categories     = [];
					if ( ! is_wp_error( $get_second_categories ) && is_array( $get_second_categories ) ) {
						foreach ( $get_second_categories as $second_value ) {
							$second_value->meta  = get_term_meta( $second_value->term_id );
							$second_categories[] = $second_value;

							//third categories
							$get_third_categories = get_terms( [ 'taxonomy' => 'rtcl_category', 'hide_empty' => false, 'parent' => $second_value->term_id ] );
							$third_categories     = [];
							if ( ! is_wp_error( $get_third_categories ) && is_array( $get_third_categories ) ) {
								foreach ( $get_third_categories as $third_value ) {
									$third_value->meta  = get_term_meta( $third_value->term_id );
									$third_categories[] = $third_value;
								}
							}
							$second_value->child = $third_categories;
						}
					}
					$first_value->child = $second_categories;

					$categories[] = $first_value;
				}
			}
			$results['categories'] = $categories;
		}

		if ( in_array( 'locations', $export_types ) ) {
			$first_locations = get_terms( [ 'taxonomy' => 'rtcl_location', 'hide_empty' => false, 'parent' => 0 ] );
			$locations       = [];
			if ( ! is_wp_error( $first_locations ) && is_array( $first_locations ) ) {
				foreach ( $first_locations as $first_value ) {
					$first_value->meta = get_term_meta( $first_value->term_id );

					//second locations
					$get_second_locations = get_terms( [ 'taxonomy' => 'rtcl_location', 'hide_empty' => false, 'parent' => $first_value->term_id ] );
					$second_locations     = [];
					if ( ! is_wp_error( $get_second_locations ) && is_array( $get_second_locations ) ) {
						foreach ( $get_second_locations as $second_value ) {
							$second_value->meta = get_term_meta( $second_value->term_id );
							$second_locations[] = $second_value;

							//third locations
							$get_third_locations = get_terms( [ 'taxonomy' => 'rtcl_location', 'hide_empty' => false, 'parent' => $second_value->term_id ] );
							$third_locations     = [];
							if ( ! is_wp_error( $get_third_locations ) && is_array( $get_third_locations ) ) {
								foreach ( $get_third_locations as $third_value ) {
									$third_value->meta = get_term_meta( $third_value->term_id );
									$third_locations[] = $third_value;
								}
							}
							$second_value->child = $third_locations;
						}
					}
					$first_value->child = $second_locations;

					$locations[] = $first_value;
				}
			}
			$results['locations'] = $locations;
		}

		if ( in_array( 'types', $export_types ) ) {
			$get_listing_types = get_option( 'rtcl_listing_types' );
			$listing_types     = [];
			foreach ( $get_listing_types as $key => $value ) {
				$listing_types[] = [
					'key'   => $key,
					'value' => $value,
				];
			}
			$results['types'] = $listing_types;
		}

		if ( in_array( 'settings', $export_types ) ) {
			//TODO: all tab and subtab mention here
			$tabs = [
				'general'         => [
					'listing_label_settings' => [],
					'location_settings'      => [],
					'currency_settings'      => [],
					'social_share_settings'  => [],
				],
				'archive_listing' => [],
				'single_listing'  => [],
				'moderation'      => [],
				'payment'         => [
					'offline'      => [],
					'paypal'       => [],
					'authorizenet' => [],
					'stripe'       => [],
				],
				'tax'             => [
					'tax_rate_settings' => [],
				],
				'email'           => [
					'notifications_settings' => [],
					'templates_settings'     => [],
				],
				'account'         => [],
				'style'           => [],
				'misc'            => [
					'media_settings' => [],
					'map_settings'   => [],
				],
				'chat'            => [],
				'advanced'        => [],
				'tools'           => [],
				'ai'              => [],
				'app'             => [],
				'membership'      => [],
				'booking'         => [],
				'marketplace'     => [],
				'addons'          => [],
			];

			$settings_data = [];

			foreach ( $tabs as $key => $value ) {
				if ( empty( $value ) ) {
					//main tab
					$option = get_option( 'rtcl_' . $key . '_settings' );
					if ( $option ) {
						$settings_data[] = [
							'key'   => 'rtcl_' . $key . '_settings',
							'value' => $option,
						];
					}
				} else {
					//sub tab
					$option = get_option( 'rtcl_' . $key . '_settings' );
					if ( $option ) {
						$settings_data[] = [
							'key'   => 'rtcl_' . $key . '_settings',
							'value' => $option,
						];
					}

					foreach ( $value as $sub_key => $sub_value ) {
						if ( empty( $sub_value ) ) {
							$option = get_option( 'rtcl_' . $key . '_' . $sub_key );
							if ( $option ) {
								$settings_data[] = [
									'key'   => 'rtcl_' . $key . '_' . $sub_key,
									'value' => $option,
								];
							}
						}
					}
				}
			}

			$results['settings'] = $settings_data;
		}

		$results = json_encode( $results );

		if ( ! is_dir( $download_path ) ) {
			mkdir( $download_path );
		}
		$wp_filesystem->put_contents( $download_path . $filename, $results );

		return true;
	}

	public static function rtcl_listings_export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Unauthorized access!!!', 'classified-listing' ) );
		}

		if ( ! wp_verify_nonce( $_REQUEST[ rtcl()->nonceId ] ?? '', rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session Expired!!', 'classified-listing' ) );
		}

		$columns = Functions::get_listings_default_fields();

		$custom_fields = Functions::get_listings_custom_fields();

		$listings[] = array_merge( $columns, $custom_fields );

		$args = [
			'post_type'      => rtcl()->post_type,
			'posts_per_page' => - 1,
		];

		$query         = new \WP_Query( $args );
		$listing_posts = $query->posts;
		foreach ( $listing_posts as $post ) {
			$listing_post = [];
			$listing      = rtcl()->factory->get_listing( $post->ID );

			$listing_post[] = $listing->get_the_title();
			$listing_post[] = $listing->get_the_content();
			$listing_post[] = get_the_excerpt( $listing->get_id() );
			$listing_post[] = $listing->get_ad_type();
			$category       = $listing->get_last_child_category();
			$listing_post[] = is_object( $category ) ? $category->slug : '';
			$location       = $listing->get_last_child_location();
			$listing_post[] = is_object( $location ) ? $location->slug : '';
			$listing_post[] = $listing->get_tags();
			$images         = $listing->get_images();
			$image_list     = [];
			foreach ( $images as $image ) {
				if ( isset( $image->url ) ) {
					$image_list[] = $image->url;
				}
			}
			$listing_post[] = implode( ',', $image_list );
			$video_urls     = $listing->get_video_urls();
			$listing_post[] = empty( $video_urls ) ? '' : current( $video_urls );
			$listing_post[] = $post->post_date;
			$listing_post[] = $listing->get_author_id();

			$user = get_user_by( 'id', $listing->get_author_id() );

			$listing_post[] = $user->first_name ?? '';
			$listing_post[] = $user->last_name ?? '';
			$listing_post[] = $user->user_email ?? '';
			$listing_post[] = $user->user_login ?? '';

			$listing_post[] = $listing->get_pricing_type();
			$listing_post[] = $listing->get_price_type();
			$listing_post[] = $listing->get_price();
			$listing_post[] = $listing->get_max_price();

			$all_profiles    = get_post_meta( $listing->get_id(), '_rtcl_social_profiles', true );
			$social_profiles = '';

			if ( is_array( $all_profiles ) ) {
				$separator = '';
				foreach ( $all_profiles as $social_key => $social_url ) {
					$profile         = $separator . $social_key . '|' . trim( $social_url );
					$social_profiles .= trim( $profile );
					$separator       = ',';
				}
			}

			$listing_post[] = $social_profiles;
			$listing_post[] = get_post_meta( $listing->get_id(), 'website', true );
			$listing_post[] = get_post_meta( $listing->get_id(), 'email', true );
			$listing_post[] = get_post_meta( $listing->get_id(), 'phone', true );
			$listing_post[] = get_post_meta( $listing->get_id(), '_rtcl_whatsapp_number', true );
			$listing_post[] = get_post_meta( $listing->get_id(), 'address', true );
			$listing_post[] = get_post_meta( $listing->get_id(), 'zipcode', true );
			$listing_post[] = get_post_meta( $listing->get_id(), 'latitude', true );
			$listing_post[] = get_post_meta( $listing->get_id(), 'longitude', true );
			$listing_post[] = get_post_meta( $listing->get_id(), 'hide_map', true );
			$listing_post[] = get_post_meta( $listing->get_id(), 'never_expires', true );
			$listing_post[] = get_post_meta( $listing->get_id(), 'expiry_date', true );
			$listing_post[] = $listing->get_view_counts();

			$bhs_data = get_post_meta( $listing->get_id(), '_rtcl_bhs', true );
			$bhs_data = is_array( $bhs_data ) ? $bhs_data : [];

			$bhs_output = '';
			if ( ! empty( $bhs_data['active'] ) ) {
				$bhs_type = ! empty( $bhs_data['type'] ) && 'selective' === $bhs_data['type'] ? 'Selective' : 'Open 24/7';
				$bhs_lines   = [];
				$bhs_lines[] = 'Status: Active | Type: ' . $bhs_type;

				if ( 'Selective' === $bhs_type ) {
					$day_names = [
						0 => 'Sunday',
						1 => 'Monday',
						2 => 'Tuesday',
						3 => 'Wednesday',
						4 => 'Thursday',
						5 => 'Friday',
						6 => 'Saturday',
					];
					foreach ( $day_names as $day_index => $day_name ) {
						$day = $bhs_data['days'][ $day_index ] ?? [];
						if ( ! empty( $day['open'] ) ) {
							if ( empty( $day['times'] ) || ! is_array( $day['times'] ) ) {
								$bhs_lines[] = $day_name . ': Open 24 Hours';
							} else {
								$time_ranges = [];
								foreach ( $day['times'] as $time ) {
									if ( ! empty( $time['start'] ) && ! empty( $time['end'] ) ) {
										$time_ranges[] = $time['start'] . '-' . $time['end'];
									}
								}
								$bhs_lines[] = $day_name . ': ' . ( ! empty( $time_ranges ) ? implode( ', ', $time_ranges ) : 'Open 24 Hours' );
							}
						} else {
							$bhs_lines[] = $day_name . ': Closed';
						}
					}
				}

				if ( ! empty( $bhs_data['special'] ) && is_array( $bhs_data['special'] ) ) {
					$special_entries = [];
					foreach ( $bhs_data['special'] as $sbh ) {
						if ( empty( $sbh['date'] ) ) {
							continue;
						}
						$occur = ! empty( $sbh['occur'] ) && 'once' === $sbh['occur'] ? 'Once' : 'Repeat';
						if ( ! empty( $sbh['open'] ) && ! empty( $sbh['times'] ) && is_array( $sbh['times'] ) ) {
							$time_ranges = [];
							foreach ( $sbh['times'] as $time ) {
								if ( ! empty( $time['start'] ) && ! empty( $time['end'] ) ) {
									$time_ranges[] = $time['start'] . '-' . $time['end'];
								}
							}
							$hours = ! empty( $time_ranges ) ? implode( ', ', $time_ranges ) : 'Open 24 Hours';
						} elseif ( ! empty( $sbh['open'] ) ) {
							$hours = 'Open 24 Hours';
						} else {
							$hours = 'Closed';
						}
						$special_entries[] = $sbh['date'] . ' (' . $occur . '): ' . $hours;
					}
					if ( ! empty( $special_entries ) ) {
						$bhs_lines[] = 'Special: ' . implode( '; ', $special_entries );
					}
				}

				$bhs_output = implode( "\n", $bhs_lines );
			}
			$listing_post[] = $bhs_output;

			$listing_post[] = $listing->get_status();

			foreach ( $custom_fields as $custom_meta ) {
				$cf_data = get_post_meta( $listing->get_id(), $custom_meta, true );

				if ( is_array( $cf_data ) ) {
					$row_strings = [];

					foreach ( $cf_data as $step_key => $step_data ) {
						if ( is_array( $step_data ) ) {
							$pairs = [];

							foreach ( $step_data as $key => $value ) {
								$key   = trim( $key );
								$value = trim( (string) $value );

								$pairs[] = "{$key}:{$value}";
							}

							$row_strings[] = implode( ' | ', $pairs );
						} else {
							$row_strings[] = "{$step_key} | {$step_data}";
						}
					}

					$listing_post[] = implode( ', ', $row_strings );
				} else {
					$listing_post[] = $cf_data;
				}
			}

			//insert
			$listings[] = $listing_post;
		}
		wp_reset_postdata();

		$data = [];
		foreach ( $listings as $row ) {
			$data[] = $row;
		}

		self::export_as_csv( $data );
		exit();
	}

	public static function export_as_csv( $data, $filename = "rtcl-listings.csv", $delimiter = "," ) {
		$f = fopen( 'php://memory', 'w' );
		foreach ( $data as $line ) {
			fputcsv( $f, $line, $delimiter );
		}
		fseek( $f, 0 );
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
		fpassthru( $f );
		fclose( $f );
	}

}