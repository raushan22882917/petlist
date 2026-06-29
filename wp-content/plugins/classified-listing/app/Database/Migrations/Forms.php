<?php
/* phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared */

namespace Rtcl\Database\Migrations;

use Rtcl\Abstracts\Migration;
use Rtcl\Models\Form\Form;
use Rtcl\Services\FormBuilder\FormPreDefined;

class Forms extends Migration {

	private static string $tableName = 'rtcl_forms';

	public static function migrate() {
		global $wpdb;

		$charsetCollate = $wpdb->get_charset_collate();

		$table = $wpdb->prefix . self::$tableName;
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) != $table ) {
			$sql = "CREATE TABLE $table (
			  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			  `title` VARCHAR(255) NOT NULL,
			  `slug` VARCHAR(191) NOT NULL,
			  `status` VARCHAR(45) NULL DEFAULT 'draft',
			  `fields` json NULL,
			  `sections` json NULL,
			  `single_layout` json NULL,
			  `translations` json NULL,
			  `settings` json NULL,
			  `type` VARCHAR(45) NULL,
			  `default` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
			  `created_by` BIGINT(20) UNSIGNED NULL,
			  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
			  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`),
              UNIQUE KEY slug_unique (slug),
              KEY status_idx (status),
              KEY type_idx (type),
              KEY created_by_idx (created_by)
            ) $charsetCollate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			dbDelta( $sql );
		}

		// Create new form if table is empty
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) {
			$row = $wpdb->get_var( "SELECT COUNT(*) from $table" );
			if ( !$row ) {
				$formData = FormPreDefined::sample();
				Form::query()->insert( $formData );
			}
		}
	}
	
	public static function add_single_layout_column() {
		global $wpdb;

		$table = $wpdb->prefix . self::$tableName;
		$exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) );
		if ( $exists !== $table ) {
			return;
		}

		$colExists = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) 
         FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_SCHEMA = %s 
           AND TABLE_NAME   = %s 
           AND COLUMN_NAME  = 'single_layout'",
			DB_NAME, $table
		) );

		if ( ! $colExists ) {
			$sql = "ALTER TABLE `$table` 
                ADD `single_layout` JSON NULL AFTER `sections`";
			$wpdb->query( $sql );

			// Optional: handle/inspect errors
			// if ( ! empty( $wpdb->last_error ) ) error_log( 'Migration error: ' . $wpdb->last_error );
		}

	}
}