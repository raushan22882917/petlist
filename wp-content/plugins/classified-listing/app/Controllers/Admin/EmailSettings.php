<?php

namespace Rtcl\Controllers\Admin;

use Rtcl\Log\Logger;
use Rtcl\Log\LogLevel;

class EmailSettings {

	public function __construct() {
		add_action( 'wp_mail_failed', array( $this, 'log_mailer_errors' ) );
	}


	function log_mailer_errors( $mailer ) {
		if ( $mailer ) {
			$log = new Logger( LogLevel::ERROR, [ 'filename' => 'mail' ] );
			$log->error( print_r( $mailer, true ) );
		}
	}

	/**
	 * @param $content_type
	 *
	 * @return string
	 */
	static function set_html_mail_content_type( $content_type ) {
		return 'text/html';
	}
}