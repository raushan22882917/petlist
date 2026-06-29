<?php

namespace Rtcl\Gateways\Store;

use Rtcl\Helpers\Link;
use Rtcl\Models\Payment;
use Rtcl\Models\PaymentGateway;

class GatewayStore extends PaymentGateway {


	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'store';
		$this->option             = $this->option . $this->id;
		$this->method_title       = esc_html__( 'Store Gateway', 'classified-listing' );
		$this->method_description = esc_html__( 'Free payment', 'classified-listing' );

		// Define user set variables.
		$this->enable      = 'yes';
		$this->title       = $this->method_title;
		$this->description = $this->method_description;
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param Payment $order
	 * @param array   $data
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function process_payment( $order, $data = [] ) {
		if ( ! $order instanceof Payment ) {
			return [
				'result'   => 'error',
				'message'  => esc_html__( 'Payment not found', 'classified-listing' ),
				'redirect' => null,
			];
		}

		$order->payment_complete( wp_generate_password() );

		return [
			'result'   => 'success',
			'redirect' => Link::get_payment_receipt_page_link( $order->get_id() ),
		];

	}


	/**
	 * @return array
	 */
	public function rest_api_data() {
		return [
			'id'    => $this->id,
			'title' => wp_strip_all_tags( $this->get_title() )
		];
	}

}