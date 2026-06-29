<?php

namespace Rtcl\Controllers\Ajax;

use Rtcl\Gateways\Store\GatewayStore;
use Rtcl\Helpers\Functions;
use Rtcl\Traits\SingletonTrait;
use WP_Error;

/**
 * Class Checkout
 *
 * @package Rtcl\Controllers\Ajax
 */
class Checkout {

	use SingletonTrait;

	function __construct() {
		add_action( 'wp_ajax_rtcl_ajax_checkout_action', [ __CLASS__, 'rtcl_ajax_checkout_action' ] );
		add_action( 'wp_ajax_rtcl_calculate_checkout_tax', [ __CLASS__, 'calculate_checkout_tax' ] );
	}

	public static function rtcl_ajax_checkout_action() {
		Functions::clear_notices();
		$success              = false;
		$redirect_url         = $gateway_id = null;
		$payment_process_data = [];

		if ( isset( $_POST['rtcl_checkout_nonce'] ) && wp_verify_nonce( $_POST['rtcl_checkout_nonce'], 'rtcl_checkout' ) ) {
			$pricing_id     = isset( $_REQUEST['pricing_id'] ) ? absint( $_REQUEST['pricing_id'] ) : 0;
			$payment_method = isset( $_REQUEST['payment_method'] ) ? sanitize_key( $_POST['payment_method'] ) : '';
			$checkout_data  = apply_filters(
				'rtcl_checkout_process_data',
				wp_parse_args(
					$_REQUEST,
					[
						'type'           => '',
						'listing_id'     => 0,
						'pricing_id'     => $pricing_id,
						'payment_method' => $payment_method,
					],
				),
			);
			$pricing        = rtcl()->factory->get_pricing( $checkout_data['pricing_id'] );
			$gateway        = Functions::get_payment_gateway( $checkout_data['payment_method'] );

			$checkout_totals = rtcl()->session->get( 'rtcl_checkout_totals', [] );
			if ( ! $gateway && $pricing && ( ( $pricing->getPrice() + 0 ) === 0 || ( isset( $checkout_totals['total'] ) && ( $checkout_totals['total'] + 0 ) == 0 ) ) ) {
				$gateway = new GatewayStore();
			}

			// Use WP_Error to handle checkout errors.
			$errors = new WP_Error();
			do_action( 'rtcl_checkout_data', $checkout_data, $pricing, $gateway, $_REQUEST, $errors );
			$errors = apply_filters( 'rtcl_checkout_validation_errors', $errors, $checkout_data, $pricing, $gateway, $_REQUEST );

			if ( is_wp_error( $errors ) && $errors->has_errors() ) {
				Functions::add_notice( $errors->get_error_message(), 'error' );
			} else {
				$country       = isset( $_REQUEST['billing_country'] ) ? sanitize_text_field( $_REQUEST['billing_country'] ) : '';
				$state         = isset( $_REQUEST['billing_state'] ) ? sanitize_text_field( $_REQUEST['billing_state'] ) : '';
				$current_user  = wp_get_current_user();
				$pricing_price = $pricing->getPrice();
				$total_price   = $pricing_price;
				$tax_amount    = 0.00;
				if ( Functions::is_enable_tax() ) {
					$multiple_tax = self::get_tax_amount( $country, $state, $pricing_price );
					if ( Functions::is_enable_multiple_tax() ) {
						foreach ( $multiple_tax as $single_tax ) {
							$tax_amount = $tax_amount + $single_tax['raw_amount'];
						}
					} else {
						$single_tax = current( $multiple_tax );
						$tax_amount = $single_tax['raw_amount'];
					}
					$total_price = $pricing_price + $tax_amount;
				}
				$metaInputs = [
					'customer_id'           => $current_user->ID,
					'customer_ip_address'   => Functions::get_ip_address(),
					'_order_key'            => apply_filters( 'rtcl_generate_order_key', uniqid( 'rtcl_oder_' ) ),
					'_pricing_id'           => $pricing->getId(),
					'amount'                => $total_price,
					'_tax_amount'           => $tax_amount,
					'_subtotal'             => $pricing_price,
					'_payment_method'       => $gateway->id,
					'_payment_method_title' => $gateway->method_title,
					'_order_currency'       => Functions::get_order_currency(),
					'_billing_email'        => $current_user ? $current_user->user_email : null,
				];
				if ( $current_user->first_name ) {
					$metaInputs['_billing_first_name'] = $current_user->first_name;
				}
				if ( $current_user->last_name ) {
					$metaInputs['_billing_last_name'] = $current_user->last_name;
				}
				if ( ! Functions::is_billing_address_disabled() ) {
					$checkout      = rtcl()->checkout();
					$billingFields = $checkout->get_checkout_fields( 'billing' );
					if ( ! empty( $billingFields ) ) {
						foreach ( $billingFields as $_key => $field ) {
							if ( $_value = $checkout->get_value( $_key ) ) {
								if ( 'billing_email' === $_key ) {
									if ( is_email( $_value ) ) {
										$metaInputs[ '_' . $_key ] = $_value;
										update_user_meta( $current_user->ID, '_' . $_key, $_value );
									}
								} else {
									$metaInputs[ '_' . $_key ] = $_value;
									update_user_meta( $current_user->ID, '_' . $_key, $_value );
								}
							}
						}
					}
				}
				$newOrderArgs = [
					'post_title'  => esc_html__( 'Order on', 'classified-listing' ) . ' ' . current_time( 'l jS F Y h:i:s A' ),
					'post_status' => 'rtcl-created',
					'post_parent' => '0',
					'ping_status' => 'closed',
					'post_author' => 1,
					'post_type'   => rtcl()->post_type_payment,
					'meta_input'  => $metaInputs,
				];

				$order_id = wp_insert_post( apply_filters( 'rtcl_checkout_process_new_order_args', $newOrderArgs, $pricing, $gateway, $checkout_data ) );

				if ( $order_id ) {
					$order = rtcl()->factory->get_order( $order_id );
					$order->set_order_key();
					do_action( 'rtcl_checkout_process_new_payment_created', $order_id, $order );
					// process payment
					try {
						$payment_process_data = $gateway->process_payment( $order );
						$payment_process_data = apply_filters( 'rtcl_checkout_process_payment_result', $payment_process_data, $order );
						$redirect_url         = ! empty( $payment_process_data['redirect'] ) ? $payment_process_data['redirect'] : null;
						// Redirect to success/confirmation/payment page
						if ( isset( $payment_process_data['result'] ) && 'success' === $payment_process_data['result'] ) {
							$success = true;
							do_action( 'rtcl_checkout_process_success', $order, $payment_process_data );
						} else {
							wp_delete_post( $order->get_id(), true );
							if ( ! empty( $payment_process_data['message'] ) ) {
								Functions::add_notice( $payment_process_data['message'], 'error' );
							}
							do_action( 'rtcl_checkout_process_error', $order, $payment_process_data );
						}
					} catch ( \Exception $e ) {
					}
				} else {
					Functions::add_notice( esc_html__( 'Error to create payment.', 'classified-listing' ), 'error' );
				}
			}
		} else {
			Functions::add_notice( esc_html__( 'Session error', 'classified-listing' ), 'error' );
		}

		$error_message   = Functions::get_notices( 'error' );
		$success_message = Functions::get_notices( 'success' );
		Functions::clear_notices();
		$res_data = wp_parse_args(
			$payment_process_data,
			[
				'error_message'   => $error_message,
				'success_message' => $success_message,
				'success'         => $success,
				'redirect_url'    => $redirect_url,
				'gateway_id'      => $gateway_id,
			],
		);
		wp_send_json( apply_filters( 'rtcl_checkout_process_ajax_response_args', $res_data ) );
	}

	public static function calculate_checkout_tax() {
		$error        = true;
		$message      = '';
		$price        = $tax_amount = 0.00;
		$multiple_tax = [];

		if ( wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			$country    = isset( $_POST['country_code'] ) ? sanitize_text_field( $_POST['country_code'] ) : '';
			$state      = isset( $_POST['state_code'] ) ? sanitize_text_field( $_POST['state_code'] ) : '';
			$type       = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
			$pricing_id = isset( $_POST['pricing_id'] ) ? absint( $_POST['pricing_id'] ) : '';

			if ( $pricing_id ) {
				$pricing = rtcl()->factory->get_pricing( $pricing_id );
				$price   = $pricing->getPrice();
			}

			// Use discounted price if coupon plugin is active and coupon is applied
			if ( function_exists( 'rtcl_coupon' ) ) {
				$applied_coupon  = rtcl()->session->get( 'rtcl_applied_coupon', '' );
				$checkout_totals = rtcl()->session->get( 'rtcl_checkout_totals', [] );
				if ( ! empty( $applied_coupon ) && ! empty( $checkout_totals['total'] ) && floatval( $checkout_totals['total'] ) > 0 ) {
					$price = floatval( $checkout_totals['total'] );
				}
			}

			$error = false;

			$multiple_tax = self::get_tax_amount( $country, $state, $price );
		} else {
			$message = __( 'Session expired.', 'classified-listing' );
		}

		if ( Functions::is_enable_multiple_tax() ) {
			foreach ( $multiple_tax as $single_tax ) {
				$tax_amount = $tax_amount + $single_tax['raw_amount'];
			}
		} else {
			$single_tax = current( $multiple_tax );
			$tax_amount = $single_tax['raw_amount'];
		}

		$total_amount = $price + $tax_amount;

		wp_send_json(
			[
				'error'               => $error,
				'msg'                 => $message,
				'enable_multiple_tax' => Functions::is_enable_multiple_tax(),
				'available_tax'       => $multiple_tax,
				'pricing_price'       => Functions::get_payment_formatted_price( $price ),
				'tax_amount'          => Functions::get_payment_formatted_price( $tax_amount ),
				'total_amount'        => Functions::get_payment_formatted_price( $total_amount ),
			],
		);
	}

	public static function get_tax_amount( $country, $state, $pricing_price ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'rtcl_tax_rates';

		$tax_amount = 0.00;
		$where      = [];
		$params     = [];

		// Only add conditions if values exist
		if ( ! empty( $country ) ) {
			$where[]  = 'country = %s';
			$params[] = $country;

			if ( ! empty( $state ) ) {
				$where[]  = 'country_state = %s';
				$params[] = $state;
			}
		}

		$sql = "SELECT * FROM `{$table_name}`";

		if ( $where ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where );
		}

		$sql .= ' ORDER BY tax_rate_priority DESC';

		$results = $where
			? $wpdb->get_results( $wpdb->prepare( $sql, $params ) )
			: $wpdb->get_results( $sql );

		if ( empty( $results ) ) {
			// First fallback: same country, blank state
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM `{$table_name}`
					 WHERE country = %s
					 AND country_state = ''
					 ORDER BY tax_rate_priority DESC",
					$country,
				),
			);

			if ( empty( $results ) ) {
				$results = $wpdb->get_results( "SELECT * FROM $table_name WHERE country = '' AND country_state = '' ORDER BY tax_rate_priority DESC" );
			}
		}

		$multiple_tax[] = [
			'label'      => __( 'Tax', 'classified-listing' ),
			'amount'     => $tax_amount,
			'raw_amount' => $tax_amount,
		];

		if ( ! empty( $results ) ) {
			$multiple_tax = [];

			foreach ( $results as $row ) {
				$tax_rate   = $row->tax_rate;
				$tax_amount = ( $tax_rate * $pricing_price ) / 100;

				$multiple_tax[] = [
					'label'      => $row->tax_rate_name,
					'amount'     => Functions::get_payment_formatted_price( $tax_amount ),
					'raw_amount' => $tax_amount,
				];
			}
		}

		return $multiple_tax;
	}
}
