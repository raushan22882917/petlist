<?php


namespace Rtcl\Emails;


use Rtcl\Helpers\Functions;
use Rtcl\Models\Listing;
use Rtcl\Models\RtclEmail;

class ReportAbuseEmailToAdmin extends RtclEmail {
	public $data = [];

	function __construct() {

		$this->id            = 'report_abuse';
		$this->template_html = 'emails/report-abuse-email-to-admin';

		// Call parent constructor.
		parent::__construct();
	}


	/**
	 * Get email subject.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return __( '[{site_title}] Report Abuse via - {listing_title}', 'classified-listing' );
	}

	/**
	 * Get email heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return esc_html__( 'Report Abuse For "{listing_title}"', 'classified-listing' );
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param               $listing_id
	 * @param array         $data
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function trigger( $listing_id, $data = [] ) {

		$return = false;

		$this->setup_locale();

		$this->data = $data;
		$listing    = null;
		if ( $listing_id ) {
			$listing = new Listing( $listing_id );
		}

		if ( is_a( $listing, Listing::class ) ) {

			$user                = wp_get_current_user();
			$name                = Functions::get_author_name( $user );
			$this->data['name']  = $name;
			$this->data['email'] = $user->user_email;
			$this->object        = $listing;
			$this->placeholders  = wp_parse_args( [ '{listing_title}' => html_entity_decode($listing->get_the_title()) ], $this->placeholders );
			$this->set_recipient( Functions::get_admin_email_id_s() );
			$this->set_replay_to_name( $name );
			$this->set_replay_to_email_address( $user->user_email );
		}

		if ( $this->get_recipient() ) {
			$return = $this->send();
		}

		$this->restore_locale();

		return $return;
	}


	/**
	 * Get content html.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_html() {
		return Functions::get_template_html(
			$this->template_html, [
				'listing' => $this->object,
				'email'   => $this,
				'data'    => $this->data
			]
		);
	}
}