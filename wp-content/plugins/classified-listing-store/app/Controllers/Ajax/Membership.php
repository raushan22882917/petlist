<?php

namespace RtclStore\Controllers\Ajax;

use Rtcl\Helpers\Link;
use WP_Term;
use RtclStore\Helpers\Functions as StoreFunctions;

class Membership {

    public static function init() {
        if ( StoreFunctions::is_membership_enabled() ) {
            add_filter( 'rtcl_ajax_category_selection_before_post', [ __CLASS__, 'is_valid_to_post_at_category' ] );
            add_filter( 'rtcl_rest_api_form_category_before_post', [ __CLASS__, 'is_valid_to_post_at_category_rest_api' ] );
        }
    }

    /**
     * @param WP_Term $term
     *
     * @return WP_Term
     */
    public static function is_valid_to_post_at_category_rest_api( $term ) {
        $user_id = get_current_user_id();
        if ( !empty( $term->parent ) || !$user_id ) {
            return $term;
        }

        if ( StoreFunctions::is_enable_store_manager() && ( $store = StoreFunctions::get_manager_store() ) && $user_id !== $store->owner_id() ) {
            $member = rtclStore()->factory->get_membership( $store->owner_id() );
            $store_owner_id = $store->owner_id();
        } else {
            $member = rtclStore()->factory->get_membership( $user_id );
            $store_owner_id = 0;
        }


        if ( $member && !$member->is_expired() ) {
            if ( !$member->is_valid_to_post_at_category( $term->term_id ) ) {
                $term->disabled = true;
            }
        } else {
            $is_valid_to_post_add_as_free = StoreFunctions::user_is_valid_to_post_as_free( $store_owner_id );
            $is_valid_to_post_to_category = StoreFunctions::is_valid_to_post_at_category( $term->term_id );
            if ( !$is_valid_to_post_to_category || !$is_valid_to_post_add_as_free ) {
                $term->disabled = true;
            }
        }
        return $term;
    }

    static function is_valid_to_post_at_category( $response ) {
        if ( StoreFunctions::is_enable_store_manager() && ( $store = StoreFunctions::get_manager_store() ) && get_current_user_id() !== $store->owner_id() ) {
            $member = rtclStore()->factory->get_membership( $store->owner_id() );
            $store_owner_id = $store->owner_id();
        } else {
            $member = rtclStore()->factory->get_membership();
            $store_owner_id = 0;
        }
        $cat_id = isset( $response['cat_id'] ) ? absint( $response['cat_id'] ) : 0;
        if ( $cat_id ) {
            $is_valid_to_post_add_as_free = StoreFunctions::user_is_valid_to_post_as_free( $store_owner_id );
            $is_valid_to_post_to_category = StoreFunctions::is_valid_to_post_at_category( $cat_id );
            $cat = get_term_by( 'id', $cat_id, rtcl()->category );
            if ( $member && !$member->is_expired() ) {
                if ( !$member->is_valid_to_post_at_category( $cat_id ) ) {
                    if ( $is_valid_to_post_to_category ) {
                        if ( !$is_valid_to_post_add_as_free || !$member->hasUnlimitedFreeAds() ) {
                            $response['success'] = false;
                            $response['message'] = array_merge( $response['message'], [
                                apply_filters( 'rtcl_category_error_message', sprintf(
                                    __( 'You are not allow to post at %s category. <a href="%s">Update your membership</a>.', "classified-listing-store" ),
                                    $cat ? $cat->name : '--',
                                    Link::get_checkout_endpoint_url( 'membership' )
                                ), $cat )
                            ] );
                        }

                    } else {
                        $response['success'] = false;
                        $response['message'] = array_merge( $response['message'], [
                            apply_filters( 'rtcl_category_error_message', sprintf(
                                __( 'You are not allow to post at %s category. <a href="%s">Update your membership</a>.', "classified-listing-store" ),
                                $cat ? $cat->name : '--',
                                Link::get_checkout_endpoint_url( 'membership' )
                            ), $cat )
                        ] );

                        return $response;
                    }
                }
            } else {
                if ( !$is_valid_to_post_to_category ) {
                    $response['success'] = false;
                    $response['message'] = array_merge( $response['message'], [
                        apply_filters( 'rtcl_category_error_message_free', sprintf(
                            __( 'You are not allow to post at %s category as free. <a href="%s">Buy a membership</a>.', "classified-listing-store" ),
                            $cat ? $cat->name : '--',
                            Link::get_checkout_endpoint_url( 'membership' )
                        ), $cat )
                    ] );

                    return $response;
                }
                if ( !$is_valid_to_post_add_as_free ) {
                    $response['success'] = false;
                    $response['message'] = array_merge( $response['message'], [
                        apply_filters( 'rtcl_remaining_free_ads_error_text', sprintf( __( 'You have no free ads remaining. You can buy a membership to post ad. <a href="%s">Buy a Membership.</a>', 'classified-listing-store' ),
                            Link::get_checkout_endpoint_url( 'membership' ) ) )
                    ] );

                    return $response;
                }

            }

        }

        return $response;
    }

}