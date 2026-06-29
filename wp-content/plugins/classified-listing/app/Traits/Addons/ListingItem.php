<?php
/**
 * Traits for Elementor single page editor
 *
 * The Elementor builder.
 *
 * @package  Classifid-listing
 * @since    2.0.10
 */

namespace Rtcl\Traits\Addons;

use Rtcl\Helpers\Functions;

/**
 * Undocumented class
 */
trait ListingItem {

	/**
	 * Listing last Item id return
	 */
	public static function get_prepared_listing_id() {
		if ( is_singular( rtcl()->post_type ) ) {
			return get_the_ID();
		}
		$getFbId = Functions::isEnableFb() ? get_post_meta( get_the_ID(), '_rtcl_form_id', true ) : null;
		global $wpdb;
		$cache_key = 'rtcl_last_post_id' . ( $getFbId ? '_' . $getFbId : null );
		$_post_id  = get_transient( $cache_key );
		if ( false === $_post_id || rtcl()->post_type !== get_post_type( $_post_id ) || 'publish' !== get_post_status( $_post_id ) ) {
			delete_transient( $cache_key );
			if ( $getFbId ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$_post_id = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT MAX(p.ID) 
						FROM {$wpdb->prefix}posts p
						INNER JOIN {$wpdb->prefix}postmeta pm 
						ON p.ID = pm.post_id
						WHERE p.post_type = %s 
						AND p.post_status = %s
						AND pm.meta_key = '_rtcl_form_id'
						AND pm.meta_value = %s
						",
						rtcl()->post_type,
						'publish',
						$getFbId, // Replace $specific_value with the desired value for '_fb_form_id'.
					),
				);
			}
			if ( ! $_post_id ) {
				$_post_id = $wpdb->get_var(
					$wpdb->prepare( "SELECT MAX(ID) FROM {$wpdb->prefix}posts WHERE post_type =  %s AND post_status = %s", rtcl()->post_type, 'publish' ),
				);
			}
			set_transient( $cache_key, $_post_id, 12 * HOUR_IN_SECONDS );
		}

		return $_post_id;
	}
}
