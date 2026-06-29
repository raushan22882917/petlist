<?php

namespace RtclPro\Api\V1;

use Rtcl\Helpers\Functions;
use RtclPro\Gateways\Authorize\lib\Types\CreditCardType;
use RtclPro\Gateways\Authorize\lib\Types\PaymentType;
use RtclPro\Helpers\Api;
use RtclPro\Models\Subscriptions;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

class V1_SubscriptionApi {
	public function register_routes() {
		register_rest_route( 'rtcl/v1', 'subscription/(?P<id>[\d]+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_item_callback' ],
				'permission_callback' => [ Api::class, 'permission_check' ]
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'subscription_cancel_callback' ],
				'permission_callback' => [ Api::class, 'permission_check' ]
			]
		] );

		register_rest_route( 'rtcl/v1', 'subscription/update-pm', [
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => [ $this, 'subscription_update_mp_callback' ],
			'permission_callback' => [ Api::class, 'permission_check' ],
			'args'                => [
				'id'           => [
					'required'          => true,
					'type'              => 'integer',
					'validate_callback' => 'is_numeric',
					'description'       => esc_html__( 'Subscription id is required', 'classified-listing-pro' ),
				],
				'stripe_pm_id' => [
					'type'        => 'string',
					'description' => esc_html__( 'Stripe payment method id.', 'classified-listing-pro' ),
				],
				'card_number'  => [
					'type'        => 'string',
					'description' => esc_html__( 'Card number', 'classified-listing-pro' ),
				],
				'card_expiry'  => [
					'type'        => 'string',
					'description' => esc_html__( 'Card expiry date, (MM/YYYY,MM-YYYY)', 'classified-listing-pro' ),
				],
				'card_cvc'     => [
					'type'        => 'integer',
					'description' => esc_html__( 'Card cvc number', 'classified-listing-pro' ),
				]
			]
		] );
	}

	public function get_item_callback( WP_REST_Request $request ) {
		Api::is_valid_auth_request();
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( "rtcl_rest_authentication_error", __( 'You are not logged in.', 'classified-listing-pro' ), [ 'status' => 404 ] );
		}

		if ( ( ! $request->get_param( 'id' ) || ( ! $subscription = ( new Subscriptions() )->findById( $request->get_param( 'id' ) ) ) ) ) {
			return new WP_Error( "rtcl_rest_authentication_error", __( 'Subscription not found.', 'classified-listing-pro' ), [ 'status' => 400 ] );
		}

		return rest_ensure_response( $request->get_params() );

		return rest_ensure_response( $subscription->getData() );
	}

	public function subscription_cancel_callback( WP_REST_Request $request ) {
		Api::is_valid_auth_request();
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( "rtcl_rest_authentication_error", __( 'You are not logged in.', 'classified-listing-pro' ), [ 'status' => 404 ] );
		}

		if ( ( ! $request->get_param( 'id' ) || ( ! $subscription = ( new Subscriptions() )->findById( $request->get_param( 'id' ) ) ) ) ) {
			return new WP_Error( "rtcl_rest_authentication_error", __( 'Subscription not found.', 'classified-listing-pro' ), [ 'status' => 400 ] );
		}

		$gateway  = Functions::get_payment_gateway( $subscription->getGatewayId() );
		$response = $gateway->cancelSubscription( $subscription );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( "rtcl_rest_authentication_error", $response->get_error_message(), [ 'status' => 400 ] );
		}

		return rest_ensure_response( [
			'id'      => $subscription->getId(),
			'message' => __( 'Subscription cancel successfully.', 'classified-listing-pro' )
		] );
	}

	public function subscription_update_mp_callback( WP_REST_Request $request ) {
		Api::is_valid_auth_request();
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( "rtcl_rest_authentication_error", __( 'You are not logged in.', 'classified-listing-pro' ), [ 'status' => 404 ] );
		}

		if ( ( ! $request->get_param( 'id' ) || ( ! $subscription = ( new Subscriptions() )->findById( $request->get_param( 'id' ) ) ) ) ) {
			return new WP_Error( "rtcl_rest_authentication_error", __( 'Subscription not found.', 'classified-listing-pro' ), [ 'status' => 400 ] );
		}

		$gateway = Functions::get_payment_gateway( $subscription->getGatewayId() );
		if ( $gateway->id === 'stripe' || $gateway->id === 'stripe_cc' ) {

			$pm_id = Functions::clean( $request->get_param( 'stripe_pm_id' ) );
			if ( empty( $pm_id ) ) {
				return new WP_Error( "rtcl_rest_authentication_error", __( 'No payment method id found.', 'classified-listing-pro' ), [ 'status' => 400 ] );
			}

			if ( ! preg_match( '/^pm_.*$/', $pm_id ) ) {
				return new WP_Error( "rtcl_rest_authentication_error", __( 'Invalid payment method id.', 'classified-listing-pro' ), [ 'status' => 400 ] );
			}

			$response = $gateway->updateSubscriptionPaymentMethod( $subscription, $pm_id );

			if ( is_wp_error( $response ) ) {
				return new WP_Error( "rtcl_rest_authentication_error", $response->get_error_message(), [ 'status' => 400 ] );
			}

			$response['message'] = __( 'Subscription payment card information updated.', 'classified-listing-pro' );

			return rest_ensure_response( $response );
		} else if ( $gateway->id === 'authorizenet' ) {

			if ( empty( $request->get_param( 'card_number' ) ) || empty( $request->get_param( 'card_expiry' ) ) || empty( $request->get_param( 'card_cvc' ) ) ) {
				return new WP_Error( "rtcl_rest_authentication_error", __( 'Please select card field', 'classified-listing-pro' ), [ 'status' => 400 ] );
			}
			$card_number = sanitize_text_field( str_replace( ' ', '', $request->get_param( 'card_number' ) ) );
			$card_expiry = trim( sanitize_text_field( $request->get_param( 'card_expiry' ) ) );
			$card_cvc    = sanitize_text_field( $request->get_param( 'card_cvc' ) );


			$cardType = $gateway->get_card_type( $card_number );
			if ( ! in_array( $cardType, $gateway->getAuthorizenetCardTypes() ) ) {
				$message = sprintf( esc_html__( 'Merchant do not support accepting in %s', 'classified-listing-pro' ), $cardType );

				return new WP_Error( "rtcl_rest_authentication_error", $message, [ 'status' => 400 ] );
			}

			$exp_year = $exp_date = $exp_month = null;
			if ( $card_expiry && strpos( $card_expiry, '/' ) !== false ) {
				$exp_date  = explode( "/", $card_expiry );
				$exp_month = ! empty( $exp_date[0] ) ? trim( $exp_date[0] ) : null;
				$exp_year  = ! empty( $exp_date[1] ) ? trim( $exp_date[1] ) : null;
			} else if ( $card_expiry && strpos( $card_expiry, '-' ) !== false ) {
				$exp_date  = explode( "-", $card_expiry );
				$exp_month = ! empty( $exp_date[0] ) ? trim( $exp_date[0] ) : null;
				$exp_year  = ! empty( $exp_date[1] ) ? trim( $exp_date[1] ) : null;
			}
			if ( $exp_year && strlen( $exp_year ) == 2 ) {
				$exp_year += 2000;
			}
			if ( $exp_year && $exp_month ) {
				$exp_date = $exp_year . '-' . $exp_month;
			}
			if ( empty( $exp_date ) || empty( $card_number ) || empty( $card_cvc ) ) {
				return new WP_Error( "rtcl_rest_authentication_error", __( 'Please select card field', 'classified-listing-pro' ), [ 'status' => 400 ] );
			}

			$creditCard = new CreditCardType();
			$creditCard->setCardNumber( $card_number );
			$creditCard->setExpirationDate( $exp_date );
			$creditCard->setCardCode( $card_cvc );

			$payment = new PaymentType();
			$payment->setCreditCard( $creditCard );

			$response = $gateway->updateSubscriptionPayment( $subscription, $payment );

			if ( is_wp_error( $response ) ) {
				return new WP_Error( "rtcl_rest_authentication_error", $response->get_error_message(), [ 'status' => 400 ] );
			}

			$response['message'] = __( 'Subscription payment card information updated.', 'classified-listing-pro' );

			return rest_ensure_response( $response );
		}

		return new WP_Error( "rtcl_rest_authentication_error", __( "Subscription gateway not found.", 'classified-listing-pro' ), [ 'status' => 400 ] );
	}
}