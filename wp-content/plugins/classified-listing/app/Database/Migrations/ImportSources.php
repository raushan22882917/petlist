<?php
/* phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared */

namespace Rtcl\Database\Migrations;

/**
 * Persisted importer source configurations.
 *
 * Used by RSS (Phase 2) and Google Places (Phase 3) to remember saved feeds /
 * searches, their field mapping, schedule, and target taxonomy/status. CSV
 * imports are one-shot and do not write to this table.
 */
class ImportSources {

	private static string $tableName = 'rtcl_import_sources';

	public static function migrate() {
		global $wpdb;

		$charsetCollate = $wpdb->get_charset_collate();
		$table          = $wpdb->prefix . self::$tableName;

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) != $table ) {
			$sql = "CREATE TABLE $table (
				`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				`source_type` VARCHAR(32) NOT NULL,
				`label` VARCHAR(191) NULL,
				`url` TEXT NULL,
				`mapping` LONGTEXT NULL,
				`schedule` VARCHAR(32) NOT NULL DEFAULT 'off',
				`target_category` BIGINT UNSIGNED NULL,
				`target_location` BIGINT UNSIGNED NULL,
				`target_status` VARCHAR(32) NULL,
				`update_existing` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
				`last_run_at` DATETIME NULL,
				`next_run_at` DATETIME NULL,
				`created_by` BIGINT UNSIGNED NULL,
				`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				KEY source_type_idx (source_type),
				KEY schedule_idx (schedule),
				KEY next_run_at_idx (next_run_at)
			) $charsetCollate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			dbDelta( $sql );
		}
	}
}
