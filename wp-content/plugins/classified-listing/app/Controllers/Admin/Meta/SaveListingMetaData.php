<?php

namespace Rtcl\Controllers\Admin\Meta;

use Rtcl\Helpers\Functions;
use Rtcl\Models\RtclCFGField;
use Rtcl\Resources\Options;
use Rtcl\Services\FormBuilder\FBHelper;

class SaveListingMetaData {
	public function __construct() {
		add_action( 'save_post', [ $this, 'save_listing_meta_data' ], 10, 2 );
	}

	/**
	 * @param $post_id
	 * @param $post
	 *
	 * @return mixed|void
	 */
	public function save_listing_meta_data( $post_id, $post ) {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			return $post_id;
		}

		if ( ! isset( $_POST['post_type'] ) ) {
			return $post_id;
		}

		if ( rtcl()->post_type != $post->post_type ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the logged in user has permission to edit this post
		if ( ! current_user_can( 'edit_' . rtcl()->post_type, $post_id ) ) {
			return $post_id;
		}

		$edit_expired_date = false;

		foreach (
			[
				'expiry_date-aa',
				'expiry_date-mm',
				'expiry_date-jj',
				'expiry_date-hh',
				'expiry_date-mn'
			] as $timeunit
		) {
			if ( ! empty( $_POST[ 'hidden_' . $timeunit ] ) && $_POST[ 'hidden_' . $timeunit ] != $_POST[ $timeunit ] ) {
				$edit_expired_date = true;
				break;
			}
		}
		$syncData = [];
		if ( ! isset( $_POST['never_expires'] ) && $edit_expired_date ) {
			$aa          = Functions::sanitize( $_POST['expiry_date-aa'] );
			$mm          = Functions::sanitize( $_POST['expiry_date-mm'] );
			$jj          = Functions::sanitize( $_POST['expiry_date-jj'] );
			$hh          = Functions::sanitize( $_POST['expiry_date-hh'] );
			$mn          = Functions::sanitize( $_POST['expiry_date-mn'] );
			$ss          = Functions::sanitize( $_POST['expiry_date-ss'] );
			$jj          = ( $jj > 31 ) ? 31 : $jj;
			$hh          = ( $hh > 23 ) ? $hh - 24 : $hh;
			$mn          = ( $mn > 59 ) ? $mn - 60 : $mn;
			$ss          = ( $ss > 59 ) ? $ss - 60 : $ss;
			$expiry_date = "$aa-$mm-$jj $hh:$mn:$ss";
			update_post_meta( $post_id, 'expiry_date', $expiry_date );
			$syncData['update']['expiry_date'] = $expiry_date;
		}

		if ( isset( $_POST['overwrite'] ) ) {
			if ( isset( $_POST['never_expires'] ) ) {
				update_post_meta( $post_id, 'never_expires', 1 );
				delete_post_meta( $post_id, 'expiry_date' );
				$syncData['update']['never_expires'] = 1;
				$syncData['delete']                  = [ 'expiry_date' ];
			} else {
				delete_post_meta( $post_id, 'never_expires' );
				$syncData['delete'] = [ 'never_expires' ];
			}

			// Feature
			if ( isset( $_POST['featured'] ) ) {
				update_post_meta( $post_id, 'featured', 1 );
				$syncData['update']['featured'] = 1;
			} else {
				delete_post_meta( $post_id, 'featured' );
				delete_post_meta( $post_id, 'feature_expiry_date' );
				$syncData['delete'][] = 'featured';
				$syncData['delete'][] = 'feature_expiry_date';
			}
			do_action( 'rtcl_listing_overwrite_change', $post_id, $_POST );
		}
		Functions::syncMLListingMeta( $post_id, $syncData );

		// Update view
		if ( isset( $_POST['_views'] ) ) {
			update_post_meta( $post_id, '_views', absint( $_POST['_views'] ) );
		}

		if ( ! FBHelper::isEnabled() ) {
			// Category
			if ( isset( $_POST['rtcl_category'] ) ) {
				$cats = absint( $_POST['rtcl_category'] );
				wp_set_object_terms( $post_id, $cats ?: null, rtcl()->category );
			}

			// Ad type
			if ( isset( $_POST['ad_type'] ) ) {
				$ad_type = Functions::sanitize( $_POST['ad_type'] );
				update_post_meta( $post_id, 'ad_type', $ad_type );
			}

			if ( isset( $_POST['_rtcl_listing_pricing'] ) && $listing_pricing = sanitize_text_field( wp_unslash( $_POST['_rtcl_listing_pricing'] ) ) ) { // phpcs:ignore 
				$listing_pricing = in_array( $listing_pricing, array_keys( Options::get_listing_pricing_types() ) ) ? $listing_pricing : 'price';

				update_post_meta( $post_id, '_rtcl_listing_pricing', $listing_pricing );
				if ( isset( $_POST['_rtcl_max_price'] ) && 'range' === $listing_pricing ) {
					$max_price = Functions::format_decimal( $_POST['_rtcl_max_price'] ); // phpcs:ignore 
					update_post_meta( $post_id, '_rtcl_max_price', $max_price );
				}
			}

			// Price type
			if ( isset( $_POST['price_type'] ) ) {
				$price_type = Functions::sanitize( $_POST['price_type'] );
				update_post_meta( $post_id, 'price_type', $price_type );
			}

			// Video urls
			if ( isset( $_POST['_rtcl_video_urls'] ) ) {
				$video_urls = Functions::sanitize( $_POST['_rtcl_video_urls'], 'video_urls' ); // phpcs:ignore 
				update_post_meta( $post_id, '_rtcl_video_urls', $video_urls );
			}

			// Price
			if ( isset( $_POST['price'] ) ) {
				$price = Functions::format_decimal( $_POST['price'] ); // phpcs:ignore 
				update_post_meta( $post_id, 'price', $price );
			}

			// Price unit
			if ( isset( $_POST['_rtcl_price_unit'] ) ) {
				$price_unit = Functions::sanitize( $_POST['_rtcl_price_unit'] );
				update_post_meta( $post_id, '_rtcl_price_unit', $price_unit );
			}

			// Listing field data
			if ( isset( $_POST['rtcl_fields'] ) ) {
				foreach ( $_POST['rtcl_fields'] as $key => $value ) { // phpcs:ignore 
					$field_id = (int) str_replace( '_field_', '', $key );
					$field    = new RtclCFGField( $field_id );
					if ( $field_id && $field ) {
						$field->saveSanitizedValue( $post_id, $value );
					}
				}
			}

			if ( isset( $_POST['zipcode'] ) ) {
				$zipcode = Functions::sanitize( $_POST['zipcode'] );
				update_post_meta( $post_id, 'zipcode', $zipcode );
			}
			if ( isset( $_POST['location'] ) || isset( $_POST['sub_location'] ) || isset( $_POST['sub_sub_location'] ) ) {
				// Location
				$locations = [];
				if ( isset( $_POST['location'] ) ) {
					$locations[] = absint( $_POST['location'] );
				}
				if ( isset( $_POST['sub_location'] ) ) {
					$locations[] = absint( $_POST['sub_location'] );
				}
				if ( isset( $_POST['sub_sub_location'] ) ) {
					$locations[] = absint( $_POST['sub_sub_location'] );
				}
				wp_set_object_terms( $post_id, $locations, rtcl()->location );
			}

			// Save location meta data
			if ( isset( $_POST['address'] ) ) {
				$address = Functions::sanitize( $_POST['address'], 'textarea' );
				update_post_meta( $post_id, 'address', $address );
			}

			if ( isset( $_POST['phone'] ) ) {
				$phone = Functions::sanitize( $_POST['phone'] );
				update_post_meta( $post_id, 'phone', $phone );
			}

			if ( isset( $_POST['_rtcl_whatsapp_number'] ) ) {
				$whatsapp_number = Functions::sanitize( $_POST['_rtcl_whatsapp_number'] );
				update_post_meta( $post_id, '_rtcl_whatsapp_number', $whatsapp_number );
			}

			if ( isset( $_POST['email'] ) ) {
				$email = sanitize_email( Functions::sanitize( $_POST['email'] ) );
				update_post_meta( $post_id, 'email', $email );
			}
			if ( isset( $_POST['_rtcl_telegram'] ) ) {
				$telegram = Functions::sanitize($_POST['_rtcl_telegram'] );
				update_post_meta( $post_id, '_rtcl_telegram', $telegram );
			}

			if ( isset( $_POST['website'] ) ) {
				$website = esc_url_raw( $_POST['website'] );
				update_post_meta( $post_id, 'website', $website );
			}

			$latitude = isset( $_POST['latitude'] ) ? Functions::sanitize( $_POST['latitude'] ) : '';
			update_post_meta( $post_id, 'latitude', $latitude );

			$longitude = isset( $_POST['longitude'] ) ? Functions::sanitize( $_POST['longitude'] ) : '';
			update_post_meta( $post_id, 'longitude', $longitude );

			$hide_map = isset( $_POST['hide_map'] ) ? 1 : 0;
			update_post_meta( $post_id, 'hide_map', $hide_map );
			if ( 'geo' === Functions::location_type() ) {
				$geo_address = isset( $_POST['rtcl_geo_address'] ) ? Functions::sanitize( $_POST['rtcl_geo_address'] ) : '';
				update_post_meta( $post_id, '_rtcl_geo_address', $geo_address );
			}
		}

		do_action( 'rtcl_listing_update_metas_at_admin', $post_id, $_POST );
	}
}
