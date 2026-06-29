<?php
/* phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared */

namespace Rtcl\Database\Migrations;

use Rtcl\Abstracts\Migration;

class Session extends Migration {

	public static function migrate() {
		global $wpdb;

		$charsetCollate = $wpdb->get_charset_collate();

		$table = $wpdb->prefix . 'rtcl_sessions';
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) != $table ) {
			$sql = "CREATE TABLE $table (
    		  `session_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			  `session_key` char(32) NOT NULL,
			  `session_value` longtext NOT NULL,
			  `session_expiry` BIGINT UNSIGNED NOT NULL,
			  PRIMARY KEY  (`session_key`),
			  UNIQUE KEY session_id (`session_id`)) $charsetCollate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			dbDelta( $sql );
		}
	}
}