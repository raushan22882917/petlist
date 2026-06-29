<?php

namespace Rtcl\Controllers\Ajax;

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\ListingStats;

/**
 * Listing analytics AJAX handler.
 *
 * Serves the per-listing "360" analytics modal shown on the My Account
 * listings table: stat cards plus a Views bar chart and an Engagement line
 * chart, rendered client-side with Chart.js. All figures come from data the
 * plugin already keeps (post-meta counters + add-on callbacks) — no dedicated
 * table is required.
 *
 * @package classified-listing/app/Controllers/Ajax
 */
class ListingAnalyticsAjax {

	/**
	 * Date ranges (in days) offered by the modal.
	 *
	 * @var int[]
	 */
	const ALLOWED_RANGES = [ 7, 30, 90 ];

	/**
	 * Register AJAX hooks.
	 */
	public function __construct() {
		add_action( 'wp_ajax_rtcl_get_listing_analytics', [ $this, 'get_listing_analytics' ] );
	}

	/**
	 * Return the analytics data set for a single listing as JSON.
	 *
	 * Validates the nonce and the current user's ownership of the listing,
	 * resolves the requested date range, then assembles cards and chart series.
	 *
	 * @return void Sends a JSON response and exits.
	 */
	public function get_listing_analytics() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Session expired. Please reload the page.', 'classified-listing' ) ] );
		}

		$listing_id = absint( Functions::request( 'listing_id' ) );

		if ( ! $listing_id || ! $this->can_view_analytics( $listing_id ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'You are not allowed to view analytics for this listing.', 'classified-listing' ) ] );
		}

		$listing = rtcl()->factory->get_listing( $listing_id );

		if ( ! $listing ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Listing not found.', 'classified-listing' ) ] );
		}

		$range = $this->resolve_range( Functions::request( 'range' ) );

		wp_send_json_success( $this->build_payload( $listing, $range ) );
	}

	/**
	 * Clamp a requested range to one of the allowed values.
	 *
	 * @param mixed $requested Raw range request value.
	 *
	 * @return int Days.
	 */
	protected function resolve_range( $requested ) {
		$requested = absint( $requested );

		return in_array( $requested, self::ALLOWED_RANGES, true ) ? $requested : 7;
	}

	/**
	 * Determine whether the current user may view a listing's analytics.
	 *
	 * @param int $listing_id Listing post ID.
	 *
	 * @return bool
	 */
	protected function can_view_analytics( $listing_id ) {
		$can_view = ( absint( get_post_field( 'post_author', $listing_id ) ) === get_current_user_id() )
		            || Functions::current_user_can( 'edit_' . rtcl()->post_type, $listing_id );

		return (bool) apply_filters( 'rtcl_can_view_listing_analytics', $can_view, $listing_id );
	}

	/**
	 * Build the full JSON payload for a listing and range.
	 *
	 * @param \Rtcl\Models\Listing $listing Listing model instance.
	 * @param int                  $range   Range in days.
	 *
	 * @return array
	 */
	protected function build_payload( $listing, $range ) {
		$listing_id = $listing->get_id();
		$metrics    = ListingStats::get_metric_definitions();

		list( $from, $to ) = ListingStats::get_range_dates( $range );

		$totals      = ListingStats::get_totals( $listing_id, $from, $to );
		$chart_keys  = [];
		$cards       = [];
		$views_key   = '';
		$engage_keys = [];

		foreach ( $metrics as $key => $def ) {
			if ( ! empty( $def['card'] ) ) {
				$cards[] = [
					'key'   => $key,
					'label' => $def['label'],
					'value' => isset( $totals[ $key ] ) ? (int) $totals[ $key ] : 0,
					'color' => $def['color'],
				];
			}

			if ( isset( $def['chart'] ) && 'views' === $def['chart'] ) {
				$views_key    = $key;
				$chart_keys[] = $key;
			} elseif ( isset( $def['chart'] ) && 'engagement' === $def['chart'] ) {
				$engage_keys[] = $key;
				$chart_keys[]  = $key;
			}
		}

		$series        = ListingStats::get_series( $listing_id, $chart_keys, $from, $to );
		$display_labels = array_map( [ $this, 'format_label' ], $series['labels'] );

		$views = [
			'label' => esc_html__( 'Unique Visitors', 'classified-listing' ),
			'color' => $views_key && isset( $metrics[ $views_key ]['color'] ) ? $metrics[ $views_key ]['color'] : '#4a6cf7',
			'data'  => $views_key && isset( $series['values'][ $views_key ] ) ? $series['values'][ $views_key ] : [],
		];

		$engagement_datasets = [];
		foreach ( $engage_keys as $key ) {
			$engagement_datasets[] = [
				'label' => $metrics[ $key ]['label'],
				'color' => $metrics[ $key ]['color'],
				'data'  => isset( $series['values'][ $key ] ) ? $series['values'][ $key ] : [],
			];
		}

		$updated_ts   = strtotime( ListingStats::last_updated_date( $listing_id ) );
		$last_updated = $updated_ts ? date_i18n( get_option( 'date_format' ), $updated_ts ) : '';

		$payload = [
			'title'        => $listing->get_the_title(),
			'subtitle'     => esc_html__( 'Engagement performance overview', 'classified-listing' ),
			'last_updated' => $last_updated
				/* translators: %s: date the historical totals were last updated. */
				? sprintf( esc_html__( 'Information updated on %s', 'classified-listing' ), $last_updated )
				: '',
			'range'      => $range,
			'labels'     => $display_labels,
			'cards'      => $cards,
			'views'      => $views,
			'engagement' => [
				'label'    => esc_html__( 'Engagement Metrics', 'classified-listing' ),
				'datasets' => $engagement_datasets,
			],
			'empty'      => array_sum( wp_list_pluck( $cards, 'value' ) ) === 0,
		];

		return apply_filters( 'rtcl_listing_analytics_payload', $payload, $listing, $range );
	}

	/**
	 * Format a Y-m-d date as a short, localized chart label.
	 *
	 * @param string $date Date in Y-m-d.
	 *
	 * @return string
	 */
	protected function format_label( $date ) {
		$timestamp = strtotime( $date );

		return $timestamp ? date_i18n( 'M j', $timestamp ) : $date;
	}
}