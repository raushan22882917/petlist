<?php

namespace Rtcl\Controllers\Admin;


use DateInterval;
use DateTime;
use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;

/**
 * Class PaymentStatus
 *
 * @package Rtcl\Controllers\Admin
 */
class PaymentStatus {

	function __construct() {
		add_action( 'transition_post_status', [ $this, 'transition_post_status' ], 10, 3 );
	}


	public function transition_post_status( $new_status, $old_status, $post ) {

		if ( rtcl()->post_type_payment !== $post->post_type ) {
			return;
		}

		// TODO : need to add some logic
		if ( 'rtcl-completed' === $new_status && 'rtcl-completed' !== $old_status ) {
			$order = rtcl()->factory->get_order( $post->ID );

			$applied_status = [ 'publish', 'rtcl-expired' ];

			$hasAnyPromotion = false;

			// when enable pay per ad pending post to publish
			if ( Functions::get_option_item( 'rtcl_general_settings', 'pending_listing_status_after_promotion', false, 'checkbox' ) ) {
				$applied_status[] = 'pending';
				$hasAnyPromotion  = true;
			}

			if ( $order && $order->pricing && ( empty( $order->pricing->getType() ) || "regular" === $order->pricing->getType() ) ) {
				$listing = rtcl()->factory->get_listing( $order->get_listing_id() );
				if ( ! absint( get_post_meta( $post->ID, '_applied', true ) ) && $listing && in_array( $listing->get_status(), $applied_status, true )
				     && $visible = absint( $order->pricing->getVisible() )
				) {
					$promotions                  = [];
					$do_update_status_to_publish = false;
					$rtcl_promotions             = Options::get_listing_promotions();
					$syncData                    = [];
					foreach ( $rtcl_promotions as $rtcl_promo_id => $rtcl_promotion ) {
						if ( $order->pricing->hasPromotion( $rtcl_promo_id ) ) {
							$hasAnyPromotion              = true;
							$promotions[ $rtcl_promo_id ] = $visible;
						}
					}
					if ( $hasAnyPromotion ) {
						$do_update_status_to_publish = true;
						try {
							$promoExpDate = new DateTime( current_time( 'mysql' ) );
							$promoExpDate->add( new DateInterval( "P{$visible}D" ) );

							$oldExpiryDate = get_post_meta( $order->get_listing_id(), 'expiry_date', true );
							$oldExpiryDate = $oldExpiryDate ? new DateTime( Functions::datetime( 'mysql', trim( $oldExpiryDate ) ) ) : '';
							if ( $oldExpiryDate && ( $promoExpDate > $oldExpiryDate ) ) {
								$expDate = $promoExpDate->format( 'Y-m-d H:i:s' );
								update_post_meta( $order->get_listing_id(), 'expiry_date', $expDate );
								$syncData['update']['expiry_date'] = $expDate;
							}
						} catch ( \Exception $e ) {

						}
					}

					$promotions_status = Functions::update_listing_promotions( $order->get_listing_id(), $promotions );

					// Check if post expired or pending, then turn it to published
					if ( in_array( $listing->get_status(), $applied_status, true ) && "publish" !== $listing->get_status() && $do_update_status_to_publish
					     && ! empty( $promotions_status )
					) {
						wp_update_post( [
							'ID'          => $listing->get_id(),
							'post_status' => 'publish'
						] );
						$syncData['post_status_update'] = 'publish';
					}
					Functions::syncMLListingMeta( $listing->get_id(), $syncData );

					update_post_meta( $order->get_id(), '_applied', 1 );

					// Hook for developers
					do_action( 'rtcl_payment_completed', $order );

				}
			}


			if ( $order ) {
				// send emails
				if ( Functions::get_option_item( 'rtcl_email_notifications_settings', 'notify_users', 'order_completed', 'multi_checkbox' ) ) {
					rtcl()->mailer()->emails['Order_Completed_Email_To_Customer']->trigger( $order->get_id(), $order );
				}

				if ( Functions::get_option_item( 'rtcl_email_notifications_settings', 'notify_admin', 'order_completed', 'multi_checkbox' ) ) {
					rtcl()->mailer()->emails['Order_Completed_Email_To_Admin']->trigger( $order->get_id(), $order );
				}
			}

		}

	}

}