<?php

namespace Rtcl\Helpers;

/**
 * Per-listing engagement statistics.
 *
 * Combines two data sources for the My Account "360" analytics modal:
 *
 * 1. Lifetime totals already stored in post meta (`_views`, reveal / click
 *    counters) plus anything add-ons register via a callback — these are the
 *    card figures and represent the listing's all-time engagement.
 * 2. A {prefix}rtcl_listing_stats table that records one row per listing / day
 *    / metric from the moment it exists, giving genuine daily trends going
 *    forward.
 *
 * The portion of the lifetime total that predates the table (lifetime minus
 * everything the table has recorded) is shown as a single historical "baseline"
 * point anchored to the listing's last-modified date, so no existing data is
 * lost when daily tracking begins.
 *
 * @package classified-listing/app/Helpers
 */
class ListingStats {

	/**
	 * Cached stats-table existence check.
	 *
	 * @var bool|null
	 */
	protected static $table_exists = null;

	/**
	 * Fully-qualified stats table name.
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;

		return $wpdb->prefix . 'rtcl_listing_stats';
	}

	/**
	 * Whether the stats table exists (cached for the request).
	 *
	 * @return bool
	 */
	public static function table_exists() {
		global $wpdb;

		if ( null === self::$table_exists ) {
			$table = self::table();
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			self::$table_exists = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table );
		}

		return self::$table_exists;
	}

	/**
	 * Metric definitions used by the analytics modal.
	 *
	 * Each entry supports:
	 * - label    : Display label.
	 * - color    : Accent colour (cards + chart series).
	 * - card     : Whether to show a stat card.
	 * - chart    : 'views' for the bar chart, 'engagement' for the line chart,
	 *              or false to keep it out of the charts.
	 * - meta     : Post-meta key the lifetime total is read from, OR
	 * - callback : Callable receiving the listing ID and returning the total,
	 *              for add-ons whose value is not stored in post meta (e.g.
	 *              Pro's chat "Persons" via ChatController::get_chat_count).
	 *
	 * Free metrics are defined here; Pro / add-ons register their own through
	 * the filter (Pro maintains Pro data, free maintains free data).
	 *
	 * @return array
	 */
	public static function get_metric_definitions() {
		$metrics = [
			'view'           => [
				'label'    => esc_html__( 'Views', 'classified-listing' ),
				'color'    => '#4a6cf7',
				'card'     => true,
				'chart'    => 'views',
				'callback' => [ __CLASS__, 'get_view_total' ],
			],
			'reveal'         => [
				'label'    => esc_html__( 'Reveals', 'classified-listing' ),
				'color'    => '#d97706',
				'card'     => true,
				'chart'    => 'engagement',
				'callback' => [ __CLASS__, 'get_reveal_total' ],
			],
			'phone_click'    => [
				'label'    => esc_html__( 'Phone', 'classified-listing' ),
				'color'    => '#16a34a',
				'card'     => true,
				'chart'    => 'engagement',
				'callback' => [ __CLASS__, 'get_phone_click_total' ],
			],
			'whatsapp_click' => [
				'label'    => esc_html__( 'WhatsApp', 'classified-listing' ),
				'color'    => '#0ea5e9',
				'card'     => true,
				'chart'    => 'engagement',
				'callback' => [ __CLASS__, 'get_whatsapp_click_total' ],
			],
			'contact'        => [
				'label'    => esc_html__( 'Contact', 'classified-listing' ),
				'color'    => '#e11d48',
				'card'     => true,
				'chart'    => 'engagement',
				'callback' => [ __CLASS__, 'get_contact_total' ],
			],
		];

		return apply_filters( 'rtcl_listing_stats_metrics', $metrics );
	}

	/**
	 * Record (increment) a metric for a listing on the current day.
	 *
	 * No-op when the stats table is absent so callers can fire unconditionally.
	 *
	 * @param int    $listing_id Listing post ID.
	 * @param string $stat_key   Metric key (see get_metric_definitions()).
	 * @param int    $count      Amount to add. Default 1.
	 *
	 * @return void
	 */
	public static function record( $listing_id, $stat_key, $count = 1 ) {
		global $wpdb;

		$listing_id = absint( $listing_id );
		$count      = absint( $count );

		if ( ! $listing_id || '' === $stat_key || ! $count || ! self::table_exists() ) {
			return;
		}

		$stat_date = self::today();
		$table     = self::table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$table} (listing_id, stat_date, stat_key, stat_count)
				VALUES (%d, %s, %s, %d)
				ON DUPLICATE KEY UPDATE stat_count = stat_count + %d",
				$listing_id,
				$stat_date,
				$stat_key,
				$count,
				$count
			)
		);

		/**
		 * Fires after a listing stat has been recorded.
		 *
		 * @param int    $listing_id Listing post ID.
		 * @param string $stat_key   Metric key.
		 * @param int    $count      Amount added.
		 */
		do_action( 'rtcl_listing_stat_recorded', $listing_id, $stat_key, $count );
	}

	/**
	 * Lifetime total for each metric of a listing (card figures).
	 *
	 * Read from post meta or a metric callback — always the all-time value, so
	 * existing data is never lost.
	 *
	 * @param int $listing_id Listing post ID.
	 *
	 * @return array Map of metric key => int total.
	 */
	public static function get_totals( $listing_id ) {
		$listing_id = absint( $listing_id );
		$totals     = [];

		foreach ( self::get_metric_definitions() as $key => $def ) {
			$totals[ $key ] = self::get_metric_total( $listing_id, $def );
		}

		return $totals;
	}

	/**
	 * Daily series for the given metric keys over a date range.
	 *
	 * Each day's value comes from the stats table. The pre-table remainder of
	 * the lifetime total (lifetime minus everything the table has recorded) is
	 * added as a single baseline point on the listing's last-modified date —
	 * clamped into the visible range so historical data always shows.
	 *
	 * @param int    $listing_id Listing post ID.
	 * @param array  $keys       Metric keys to include.
	 * @param string $from       Start date (Y-m-d).
	 * @param string $to         End date (Y-m-d).
	 *
	 * @return array { labels: string[], values: array<string,int[]> }
	 */
	public static function get_series( $listing_id, array $keys, $from, $to ) {
		$listing_id = absint( $listing_id );
		$labels     = self::date_range_list( $from, $to );

		$values = [];
		foreach ( $keys as $key ) {
			$values[ $key ] = array_fill( 0, count( $labels ), 0 );
		}

		if ( empty( $keys ) || empty( $labels ) ) {
			return [
				'labels' => $labels,
				'values' => $values,
			];
		}

		$index = array_flip( $labels );

		// Daily rows from the table (only the requested keys, within range).
		$daily = self::get_daily_rows( $listing_id, $keys, $from, $to );
		foreach ( $daily as $row ) {
			if ( isset( $index[ $row->stat_date ], $values[ $row->stat_key ] ) ) {
				$values[ $row->stat_key ][ $index[ $row->stat_date ] ] = (int) $row->total;
			}
		}

		// Historical baseline on the last-modified date (clamped into range).
		$totals          = self::get_totals( $listing_id );
		$table_totals    = self::get_table_totals( $listing_id, $keys );
		$baseline_date   = self::baseline_date( $listing_id, $from, $to );
		$baseline_offset = isset( $index[ $baseline_date ] ) ? $index[ $baseline_date ] : 0;

		foreach ( $keys as $key ) {
			$lifetime = isset( $totals[ $key ] ) ? (int) $totals[ $key ] : 0;
			$tracked  = isset( $table_totals[ $key ] ) ? (int) $table_totals[ $key ] : 0;
			$baseline = max( 0, $lifetime - $tracked );

			if ( $baseline > 0 && isset( $values[ $key ][ $baseline_offset ] ) ) {
				$values[ $key ][ $baseline_offset ] += $baseline;
			}
		}

		return [
			'labels' => $labels,
			'values' => $values,
		];
	}

	/**
	 * The listing's last-modified date (Y-m-d), used to anchor the baseline.
	 *
	 * @param int $listing_id Listing post ID.
	 *
	 * @return string
	 */
	public static function last_updated_date( $listing_id ) {
		$modified = get_post_field( 'post_modified', absint( $listing_id ) );
		$ts       = $modified ? strtotime( $modified ) : false;

		return $ts ? gmdate( 'Y-m-d', $ts ) : self::today();
	}

	/**
	 * Resolve a range token to [from, to] dates (inclusive, Y-m-d).
	 *
	 * @param int $days Number of days back to include (including today).
	 *
	 * @return array [ string $from, string $to ]
	 */
	public static function get_range_dates( $days ) {
		$days = max( 1, absint( $days ) );
		$to   = self::today();
		$from = gmdate( 'Y-m-d', strtotime( $to . ' -' . ( $days - 1 ) . ' days' ) );

		return [ $from, $to ];
	}

	/**
	 * Sum of table-recorded counts per metric (all time) for a listing.
	 *
	 * @param int   $listing_id Listing post ID.
	 * @param array $keys       Metric keys.
	 *
	 * @return array Map of metric key => int total.
	 */
	protected static function get_table_totals( $listing_id, array $keys ) {
		global $wpdb;

		$totals = [];
		foreach ( $keys as $key ) {
			$totals[ $key ] = 0;
		}

		if ( empty( $keys ) || ! self::table_exists() ) {
			return $totals;
		}

		$table        = self::table();
		$placeholders = implode( ', ', array_fill( 0, count( $keys ), '%s' ) );
		$params       = array_merge( [ absint( $listing_id ) ], array_values( $keys ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT stat_key, SUM(stat_count) AS total
				FROM {$table}
				WHERE listing_id = %d AND stat_key IN ({$placeholders})
				GROUP BY stat_key",
				$params
			)
		);

		if ( $rows ) {
			foreach ( $rows as $row ) {
				$totals[ $row->stat_key ] = (int) $row->total;
			}
		}

		return $totals;
	}

	/**
	 * Daily table rows for the given metric keys within a date range.
	 *
	 * @param int    $listing_id Listing post ID.
	 * @param array  $keys       Metric keys.
	 * @param string $from       Start date (Y-m-d).
	 * @param string $to         End date (Y-m-d).
	 *
	 * @return array Row objects with stat_date, stat_key, total.
	 */
	protected static function get_daily_rows( $listing_id, array $keys, $from, $to ) {
		global $wpdb;

		if ( empty( $keys ) || ! self::table_exists() ) {
			return [];
		}

		$table        = self::table();
		$placeholders = implode( ', ', array_fill( 0, count( $keys ), '%s' ) );
		$params       = array_merge( [ absint( $listing_id ) ], array_values( $keys ), [ $from, $to ] );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT stat_date, stat_key, SUM(stat_count) AS total
				FROM {$table}
				WHERE listing_id = %d AND stat_key IN ({$placeholders}) AND stat_date BETWEEN %s AND %s
				GROUP BY stat_date, stat_key",
				$params
			)
		);

		return $rows ? $rows : [];
	}

	/**
	 * Resolve the baseline date for a listing, clamped into [from, to].
	 *
	 * @param int    $listing_id Listing post ID.
	 * @param string $from       Start date (Y-m-d).
	 * @param string $to         End date (Y-m-d).
	 *
	 * @return string Y-m-d date within the range.
	 */
	protected static function baseline_date( $listing_id, $from, $to ) {
		$date = self::last_updated_date( $listing_id );

		if ( $date < $from ) {
			return $from;
		}
		if ( $date > $to ) {
			return $to;
		}

		return $date;
	}

	/**
	 * Resolve the lifetime total for a single metric definition.
	 *
	 * @param int   $listing_id Listing post ID.
	 * @param array $def        Metric definition.
	 *
	 * @return int
	 */
	protected static function get_metric_total( $listing_id, array $def ) {
		if ( ! $listing_id ) {
			return 0;
		}

		if ( ! empty( $def['callback'] ) && is_callable( $def['callback'] ) ) {
			return absint( call_user_func( $def['callback'], $listing_id ) );
		}

		if ( ! empty( $def['meta'] ) ) {
			return absint( get_post_meta( $listing_id, $def['meta'], true ) );
		}

		return 0;
	}

	/**
	 * All-time total for the view metric from the stats table.
	 *
	 * @param int $listing_id Listing post ID.
	 *
	 * @return int
	 */
	public static function get_view_total( $listing_id ) {
		return self::get_single_stat_total( $listing_id, 'view' );
	}

	/**
	 * All-time total for the reveal metric from the stats table.
	 *
	 * @param int $listing_id Listing post ID.
	 *
	 * @return int
	 */
	public static function get_reveal_total( $listing_id ) {
		return self::get_single_stat_total( $listing_id, 'reveal' );
	}

	/**
	 * All-time total for the phone_click metric from the stats table.
	 *
	 * @param int $listing_id Listing post ID.
	 *
	 * @return int
	 */
	public static function get_phone_click_total( $listing_id ) {
		return self::get_single_stat_total( $listing_id, 'phone_click' );
	}

	/**
	 * All-time total for the whatsapp_click metric from the stats table.
	 *
	 * @param int $listing_id Listing post ID.
	 *
	 * @return int
	 */
	public static function get_whatsapp_click_total( $listing_id ) {
		return self::get_single_stat_total( $listing_id, 'whatsapp_click' );
	}

	/**
	 * All-time total for the contact metric from the stats table.
	 *
	 * @param int $listing_id Listing post ID.
	 *
	 * @return int
	 */
	public static function get_contact_total( $listing_id ) {
		return self::get_single_stat_total( $listing_id, 'contact' );
	}

	/**
	 * All-time total for the chat metric from the stats table.
	 *
	 * @param int $listing_id Listing post ID.
	 *
	 * @return int
	 */
	public static function get_chat_total( $listing_id ) {
		return self::get_single_stat_total( $listing_id, 'chat' );
	}

	/**
	 * All-time total for a single stat key from the stats table.
	 *
	 * @param int    $listing_id Listing post ID.
	 * @param string $stat_key   Metric key.
	 *
	 * @return int
	 */
	public static function get_single_stat_total( $listing_id, $stat_key ) {
		global $wpdb;

		$listing_id = absint( $listing_id );

		if ( ! $listing_id || ! self::table_exists() ) {
			return 0;
		}

		$table = self::table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return absint(
			$wpdb->get_var(
				$wpdb->prepare(
					"SELECT COALESCE(SUM(stat_count), 0) FROM {$table} WHERE listing_id = %d AND stat_key = %s",
					$listing_id,
					$stat_key
				)
			)
		);
	}

	/**
	 * Continuous list of Y-m-d dates from $from to $to inclusive.
	 *
	 * @param string $from Start date (Y-m-d).
	 * @param string $to   End date (Y-m-d).
	 *
	 * @return string[]
	 */
	protected static function date_range_list( $from, $to ) {
		$dates   = [];
		$current = strtotime( $from );
		$end     = strtotime( $to );

		if ( false === $current || false === $end || $current > $end ) {
			return $dates;
		}

		$guard = 0;
		while ( $current <= $end && $guard < 400 ) {
			$dates[] = gmdate( 'Y-m-d', $current );
			$current = strtotime( '+1 day', $current );
			$guard ++;
		}

		return $dates;
	}

	/**
	 * Current site-local date (Y-m-d).
	 *
	 * @return string
	 */
	protected static function today() {
		return current_time( 'Y-m-d' );
	}
}