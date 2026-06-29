<?php

namespace Rtcl\Models;

class EmbeddingModel {

	protected static $table = 'rtcl_ai_embeddings';

	/**
	 * Create table on plugin activation
	 */
	public static function create_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . self::$table;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "
		CREATE TABLE $table_name (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			listing_id BIGINT(20) UNSIGNED NOT NULL,
			title TEXT NOT NULL,
			embedding LONGTEXT NOT NULL,
			info LONGTEXT NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY listing_id (listing_id)
		) $charset_collate;
		";

		dbDelta( $sql );
	}

	/**
	 * Insert or update an embedding record
	 */
	public static function upsert( $listing_id, $title, $embedding, $info = [] ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$table;

		return $wpdb->replace( $table_name, [
			'listing_id' => $listing_id,
			'title'      => $title,
			'embedding'  => wp_json_encode( $embedding ),
			'info'       => maybe_serialize( $info ),
			'created_at' => current_time( 'mysql' ),
		] );
	}

	/**
	 * Fetch all embeddings
	 */
	public static function get_all() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$table;

		return $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A );
	}

	/**
	 * Fetch embedding by listing ID
	 */
	public static function get_by_listing( $listing_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$table;

		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_name WHERE listing_id = %d", $listing_id ),
			ARRAY_A,
		);
	}
}