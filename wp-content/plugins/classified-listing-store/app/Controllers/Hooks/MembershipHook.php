<?php

namespace RtclStore\Controllers\Hooks;

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Models\Listing;
use Rtcl\Models\Pricing;
use Rtcl\Models\VStore;
use RtclStore\Helpers\Functions as StoreFunctions;
use RtclStore\Models\Membership;
use WP_Error;

class MembershipHook {

	public static function init() {
		if ( StoreFunctions::is_membership_enabled() ) {
			add_action( 'rtcl_listing_form_after_save_or_update', [ __CLASS__, 'update_posting_count' ], 1, 3 );
			add_action( 'rtcl_before_renew_listing', [ __CLASS__, 'check_validation_renew_listing' ], 10, 3 );
			add_action( 'rtcl_after_renew_listing', [ __CLASS__, 'add_log_renew_listing' ], 10, 3 );
			add_action( 'rtcl_before_add_edit_listing_before_category_condition', [
				__CLASS__,
				'verify_membership_before_category'
			] );
			add_action( 'rtcl_before_add_edit_listing_into_category_condition', [
				__CLASS__,
				'verify_membership_into_category'
			], 10, 2 );

			add_filter( 'rtcl_checkout_process_new_order_args', [ __CLASS__, 'add_meta_to_membership_order' ], 20, 2 );
			if ( StoreFunctions::is_renew_only_membership() ) {
				add_filter( 'rtcl_enable_renew_button', [ __CLASS__, 'enable_renew_button_only_for_membership' ] );
				add_filter( 'rtcl_email_listing_renewal_link', [ __CLASS__, 'renew_email_link' ], 10, 2 );
			}
		}
	}

	/**
	 * @param Listing  $listing
	 * @param VStore   $vStore
	 * @param WP_Error $wp_error
	 *
	 * @return void
	 */
	public static function check_validation_renew_listing( Listing $listing, VStore $vStore, WP_Error $wp_error ) {
		$renewOnlyForMembership = StoreFunctions::is_renew_only_membership();
		$membership             = rtclStore()->factory->get_membership();
		if ( $renewOnlyForMembership ) {
			if ( ! $membership ) {
				$wp_error->add( 'rtcl_renew_listing_error', esc_html__( "You have no membership to renew", "classified-listing-store" ) );

				return;
			}

			if ( $membership->is_expired() ) {
				$wp_error->add( 'rtcl_renew_listing_error', esc_html__( "Your membership is expired.", "classified-listing-store" ) );

				return;
			}
		}

		$listingCatIds = $listing->get_category_ids();
		$listingCatId  = ! empty( $listingCatIds ) ? $listingCatIds[0] : 0;
		$vStore->add( 'listingCatId', $listingCatId );
		if ( StoreFunctions::user_is_valid_to_post_as_free() ) {
			if ( $listingCatId && StoreFunctions::is_valid_to_post_at_category( $listingCatId ) ) {
				$vStore->add( 'allowFree', true );

				return;
			} else {
				$vStore->add( 'freeCategoryError', true );
			}
		}

		$membership = empty( $membership ) ? rtclStore()->factory->get_membership() : $membership;
		if ( ! $membership ) {
			$wp_error->add( 'rtcl_renew_listing_error', esc_html__( "You have no ads remain to renewF", "classified-listing-store" ) );

			return;
		}

		if ( $membership->is_expired() ) {
			$wp_error->add( 'rtcl_renew_listing_error', esc_html__( "Your membership is expired.", "classified-listing-store" ) );

			return;
		}

		if ( $membership->hasUnlimitedFreeAds() ) {
			$vStore->add( 'allowFree', true );
			$vStore->add( 'membership', $membership );

			return;
		}

		if ( ! $membership->get_remaining_ads() ) {
			$wp_error->add( 'rtcl_renew_listing_error', esc_html__( "You have no ads at you membership to renew", "classified-listing-store" ) );

			return;
		}

		if ( ! $membership->is_valid_to_post_at_category( $listingCatId ) ) {
			$cat = get_term_by( 'id', $listingCatId, rtcl()->category );
			$wp_error->add( 'rtcl_renew_listing_error', sprintf(
				__( 'You are not allow to renew at %s category. <a href="%s">Update your membership</a>.', "classified-listing-store" ),
				$cat ? $cat->name : '--',
				Link::get_checkout_endpoint_url( 'membership' )
			) );

		}
		$vStore->add( 'membership', $membership );

	}

	/**
	 * @param Listing  $listing
	 * @param VStore   $vStore
	 * @param WP_Error $wp_error
	 *
	 * @return void
	 */
	public static function add_log_renew_listing( Listing $listing, VStore $vStore, WP_Error $wp_error ) {
		$allowFree = $vStore->get( 'allowFree' );
		if ( $allowFree ) {
			$listingCatId = $vStore->get( 'listingCatId' );
			if ( empty( $listingCatId ) ) {
				$listingCatIds = $listing->get_category_ids();
				$listingCatId  = ! empty( $listingCatIds ) ? $listingCatIds[0] : 0;
				$vStore->add( 'listingCatId', $listingCatId );
			}
			$data = [
				'post_id'    => $listing->get_id(),
				'user_id'    => $listing->get_owner_id(),
				'cat_id'     => $listingCatId,
				'status'     => 'renew',
				'created_at' => current_time( 'mysql' )
			];
			StoreFunctions::update_posting_log( $data );

			return;
		}
		$membership = $vStore->get( 'membership' );

		if (  $membership && is_a( $membership, Membership::class ) ) {
			$membership->update_post_count();
			return;
		}

		$wp_error->add( 'rtcl_renew_listing_error', esc_html__( "You have no ads to renew.", "classified-listing-store" ) );

	}

	public static function enable_renew_button_only_for_membership( $status ) {

		if ( ! rtclStore()->factory->get_membership() ) {
			return false;
		}

		return $status;
	}

	public static function renew_email_link( $link, $listing_id ) {
		$link = Link::get_listing_promote_page_link( $listing_id );

		return $link;
	}

	/**
	 * @param array   $new_payment_args
	 * @param Pricing $pricing
	 *
	 * @return array
	 */
	static function add_meta_to_membership_order( $new_payment_args, $pricing ) {
		if ( $pricing && 'membership' === $pricing->getType() ) {
			$new_payment_args['meta_input']['payment_type'] = 'membership';
			$membership_promotions                          = get_post_meta( $pricing->getId(), '_rtcl_membership_promotions', true );
			if ( ! empty( $membership_promotions ) ) {
				$new_payment_args['meta_input']['_rtcl_membership_promotions'] = $membership_promotions;
			}
		}

		return $new_payment_args;
	}

	/**
	 * @param Listing $listing
	 * @param string  $type
	 * @param int     $cat_id
	 */
	static function update_posting_count( Listing $listing, string $type, int $cat_id ) {
		if ( ! in_array( $type, [ 'new', 'renew' ] ) || ! $cat_id ) {
			return;
		}

		$user_id = $listing->get_owner_id();
		$data    = [
			'post_id'    => $listing->get_id(),
			'user_id'    => $user_id,
			'cat_id'     => $cat_id,
			'created_at' => $listing->get_listing()->post_date
		];
		$member  = rtclStore()->factory->get_membership( $user_id );
		if ( $member && ! $member->is_expired() ) {
			if ( StoreFunctions::user_is_valid_to_post_as_free( $user_id ) && StoreFunctions::is_valid_to_post_at_category( $cat_id ) ) {
				StoreFunctions::update_posting_log( $data );
			} else {
				$member->update_post_count();
			}
		} else {
			StoreFunctions::update_posting_log( $data );
		}
	}

	/**
	 * @param $data
	 *
	 * @return void
	 * @deprecated
	 */
	static private function update_posting_log( $data ) {
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'rtcl_posting_log',
			$data,
			[
				'%d',
				'%d',
				'%d',
				'%s'
			]
		);
	}

	static function verify_membership_before_category( $post_id ) {
		if ( $post_id ) {
			return;
		}
		$store_owner_id = 0;
		if ( StoreFunctions::is_enable_store_manager() && ( $store = StoreFunctions::get_manager_store() ) && get_current_user_id() !== $store->owner_id() ) {
			$member         = rtclStore()->factory->get_membership( $store->owner_id() );
			$store_owner_id = $store->owner_id();
		} else {
			$member = rtclStore()->factory->get_membership();
		}

		$enable_free_ads = Functions::get_option_item( 'rtcl_membership_settings', 'enable_free_ads', false, 'checkbox' );

		if ( $member && ! $member->is_expired() ) {
			$allow_free_ads         = false;
			$has_unlimited_free_ads = $member->hasUnlimitedFreeAds();
			$remaining_free_ads     = StoreFunctions::user_is_valid_to_post_as_free( $store_owner_id );
			if ( $enable_free_ads && ( $has_unlimited_free_ads || $remaining_free_ads ) ) {
				$remaining_free_ads = $has_unlimited_free_ads ? __( "unlimited", 'classified-listing-store' ) : $remaining_free_ads;
				Functions::add_notice(
					apply_filters( 'rtcl_remaining_free_ads_success_text',
						sprintf( __( 'You have %s free ads.', 'classified-listing-store' ), $remaining_free_ads ),
						$remaining_free_ads, $member )
				);
				$allow_free_ads = true;
			}

			if ( $remaining_ads = $member->is_valid_to_post() ) {
				Functions::add_notice( apply_filters( 'rtcl_remaining_regular_ads_success_text',
					sprintf( __( 'You have %s regular ads.', 'classified-listing-store' ), $remaining_ads ),
					$remaining_ads, $member ) );
			} else {
				if ( ! $allow_free_ads ) {
					Functions::add_notice( apply_filters( 'rtcl_remaining_ads_error_text',
						sprintf( __( 'You have no remaining ads at your current membership. <a href="%s">Update your membership</a>.', 'classified-listing-store' ), Link::get_checkout_endpoint_url( 'membership' ) ),
						$member ), 'error' );
				}
			}
		} else {
			if ( $enable_free_ads ) {
				if ( $remaining_free_ads = StoreFunctions::user_is_valid_to_post_as_free( $store_owner_id ) ) {
					Functions::add_notice( apply_filters( 'rtcl_remaining_free_ads_success_text', sprintf( __( 'You have %s free ads.', 'classified-listing-store' ), $remaining_free_ads ),
						$remaining_free_ads ) );
				} elseif ( ! is_user_logged_in() && Functions::get_option_item( 'rtcl_account_settings', 'enable_post_for_unregister', false, 'checkbox' ) ) {
					Functions::add_notice( apply_filters( 'rtcl_remaining_free_ads_success_text', sprintf( __( 'You have %s free ads as unregistered user.', 'classified-listing-store' ), $enable_free_ads ),
						$enable_free_ads ) );
				} else {
					Functions::add_notice( apply_filters( 'rtcl_remaining_free_ads_error_text',
						sprintf( __( 'You have no free ads remaining. You can buy a membership to post ad. <a href="%s">Buy a Membership.</a>', 'classified-listing-store' ),
							Link::get_checkout_endpoint_url( 'membership' ) ) ), 'error' );
				}
			} else {
				Functions::add_notice( apply_filters( 'rtcl_membership_buy_membership_error_text',
					sprintf( __( 'You can buy a membership to post ad. <a href="%s">Buy a Membership</a>', 'classified-listing-store' ), Link::get_checkout_endpoint_url( 'membership' ) ) ), 'error' );
			}
		}


	}

	static function verify_membership_into_category( $post_id, $category_id ) {
		if ( ! $post_id && $category_id ) {
			if ( StoreFunctions::is_enable_store_manager() && ( $store = StoreFunctions::get_manager_store() ) && get_current_user_id() !== $store->owner_id() ) {
				$member         = rtclStore()->factory->get_membership( $store->owner_id() );
				$store_owner_id = $store->owner_id();
			} else {
				$member         = rtclStore()->factory->get_membership();
				$store_owner_id = 0;
			}
			$category_id                  = Functions::get_term_top_most_parent_id( $category_id, rtcl()->category );
			$cat                          = get_term_by( 'id', $category_id, rtcl()->category );
			$is_valid_to_post_add_as_free = StoreFunctions::user_is_valid_to_post_as_free( $store_owner_id );
			$is_valid_to_post_to_category = StoreFunctions::is_valid_to_post_at_category( $category_id );
			if ( $member && ! $member->is_expired() ) {
				if ( ! $member->is_valid_to_post_at_category( $category_id ) ) {
					if ( $is_valid_to_post_to_category ) {
						if ( ! $is_valid_to_post_add_as_free || ! $member->hasUnlimitedFreeAds() ) {
							Functions::add_notice( apply_filters( 'rtcl_category_error_message', sprintf(
								__( 'You are not allow to post at %s category. <a href="%s">Update your membership.</a>', "classified-listing-store" ),
								$cat ? $cat->name : '--',
								Link::get_checkout_endpoint_url( 'membership' )
							), $cat ), 'error' );
						}

					} else {
						Functions::add_notice( apply_filters( 'rtcl_category_error_message', sprintf(
							__( 'You are not allow to post at %s category. <a href="%s">Update your membership.</a>', "classified-listing-store" ),
							$cat ? $cat->name : '--',
							Link::get_checkout_endpoint_url( 'membership' )
						), $cat ), 'error' );
					}
				}
			} else {
				if ( ! $is_valid_to_post_add_as_free ) {
					Functions::add_notice( apply_filters( 'rtcl_remaining_free_ads_error_text',
						sprintf( __( 'You have no free ads remaining. You can buy a membership to post ad. <a href="%s">Buy a Membership.</a>', 'classified-listing-store' ),
							Link::get_checkout_endpoint_url( 'membership' ) ) ), 'error' );
				}
				if ( ! $is_valid_to_post_to_category ) {
					Functions::add_notice( apply_filters( 'rtcl_category_error_message_free', sprintf(
						__( 'You are not allow to post at %s category as free. <a href="%s">Buy a membership.</a>', "classified-listing-store" ),
						$cat ? $cat->name : '--',
						Link::get_checkout_endpoint_url( 'membership' )
					), $cat ), 'error' );
				}
			}
		}
	}

	public static function verify_membership() {
		//        $free_ads = absint(Functions::get_option_item('rtcl_membership_settings', 'free_ads', 0));
		$member = rtclStore()->factory->get_membership();
		if ( $member && ! $member->is_expired() ) {
			Functions::add_notice( __( "Only members can add new listing", "classified-listing-store" ), 'error' );
		}
	}

}