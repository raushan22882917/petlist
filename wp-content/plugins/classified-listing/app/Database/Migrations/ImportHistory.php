<?php
/* phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared */

namespace Rtcl\Database\Migrations;

/**
 * Run log for the listing importers (CSV, RSS, Google Places).
 *
 * One row per import run, written when a run starts and updated when it finishes.
 */
class ImportHistory {

	private static string $tableName = 'rtcl_import_history';

	public static function migrate() {
		global $wpdb;

		$charsetCollate = $wpdb->get_charset_collate();
		$table          = $wpdb->prefix . self::$tableName;

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) != $table ) {
			$sql = "CREATE TABLE $table (
				`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				`source_type` VARCHAR(32) NOT NULL,
				`source_key` VARCHAR(255) NULL,
				`params` LONGTEXT NULL,
				`status` VARCHAR(16) NOT NULL DEFAULT 'running',
				`user_id` BIGINT UNSIGNED NULL,
				`total` INT UNSIGNED NOT NULL DEFAULT 0,
				`imported` INT UNSIGNED NOT NULL DEFAULT 0,
				`updated` INT UNSIGNED NOT NULL DEFAULT 0,
				`skipped` INT UNSIGNED NOT NULL DEFAULT 0,
				`errors` LONGTEXT NULL,
				`started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`finished_at` DATETIME NULL,
				PRIMARY KEY (`id`),
				KEY source_type_idx (source_type),
				KEY status_idx (status),
				KEY started_at_idx (started_at),
				KEY user_id_idx (user_id)
			) $charsetCollate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			dbDelta( $sql );
		}
	}
}
