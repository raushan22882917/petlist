<?php

namespace Rtcl\Database;

use Rtcl\Database\Migrations\Forms;
use Rtcl\Database\Migrations\ImportHistory;
use Rtcl\Database\Migrations\ImportSources;
use Rtcl\Database\Migrations\Session;

class DbMigration {

	public static function run() {
		Session::migrate();
		Forms::migrate();
		ImportHistory::migrate();
		ImportSources::migrate();
		do_action( 'rtcl_run_db_migration' );
	}
}