<?php

namespace Rtcl\Controllers\Admin;


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;

class AddConfig {

	function __construct() {
		add_action( 'init', [ $this, 'addConfigurations' ], 5 );
		// Add a post display state for special  pages.
		add_filter( 'display_post_states', [ $this, 'add_display_post_states' ], 10, 2 );
	}

	public function set_myaccount_page_template() {
		if ( 'yes' === get_option( 'rtcl_myaccount_full_width_page_temp' ) ) {
			$page = Link::get_my_account_page_link();
			if ( ! empty( $page ) ) {
				$my_account_page_id = url_to_postid( $page );
				if ( $my_account_page_id ) {
					update_post_meta( $my_account_page_id, '_wp_page_template', '' );
					update_option( 'rtcl_myaccount_full_width_page_temp', 'no' );
				}
			}
		}
	}

	static function get_custom_page_list() {
		$pages = [
			'listings'     => [
				'title'   => esc_html__( 'Listings', 'classified-listing' ),
				'content' => '',
			],
			'listing_form' => [
				'title'   => esc_html__( 'Listing Form', 'classified-listing' ),
				'content' => '[rtcl_listing_form]',
			],
			'checkout'     => [
				'title'   => esc_html__( 'Checkout', 'classified-listing' ),
				'content' => '[rtcl_checkout]',
			],
			'myaccount'    => [
				'title'   => esc_html__( 'My Account', 'classified-listing' ),
				'content' => '[rtcl_my_account]',
			],
		];

		return apply_filters( 'rtcl_custom_pages_list', $pages );
	}

	public function add_display_post_states( $post_states, $post ) {
		$page_settings = Functions::get_page_ids();
		$pList         = $this->get_custom_page_list();
		foreach ( $page_settings as $type => $id ) {
			if ( $post && $post->ID == $id ) {
				$post_states[] = $pList[ $type ]['title'] . " " . esc_html__( "Page", "classified-listing" );
			}
		}

		return $post_states;
	}


	function addConfigurations() {
		$mms                 = Functions::get_option( 'rtcl_misc_media_settings' );
		$moderation_settings = Functions::get_option( 'rtcl_moderation_settings' );

		rtcl()->gallery = [
			'option_name'    => 'rtcl_gallery',
			'image_edit_cap' => isset( $moderation_settings['image_edit_cap'] ) && $moderation_settings['image_edit_cap'] == 'yes',
			'image_sizes'    => [
				"rtcl-gallery"           => [
					'width'  => isset( $mms['image_size_gallery']['width'] ) ? absint( $mms['image_size_gallery']['width'] ) : 924,
					'height' => isset( $mms['image_size_gallery']['width'] ) ? absint( $mms['image_size_gallery']['height'] ) : 462,
					'crop'   => isset( $mms['image_size_gallery']['crop'] ) && $mms['image_size_gallery']['crop'] === 'yes',
				],
				"rtcl-thumbnail"         => [
					'width'  => isset( $mms['image_size_thumbnail']['width'] ) ? absint( $mms['image_size_thumbnail']['width'] ) : 320,
					'height' => isset( $mms['image_size_thumbnail']['width'] ) ? absint( $mms['image_size_thumbnail']['height'] ) : 240,
					'crop'   => isset( $mms['image_size_thumbnail']['crop'] ) && $mms['image_size_thumbnail']['crop'] === 'yes',
				],
				"rtcl-gallery-thumbnail" => [
					'width'  => isset( $mms['image_size_gallery_thumbnail']['width'] ) ? absint( $mms['image_size_gallery_thumbnail']['width'] ) : 150,
					'height' => isset( $mms['image_size_gallery_thumbnail']['width'] ) ? absint( $mms['image_size_gallery_thumbnail']['height'] ) : 105,
					'crop'   => isset( $mms['image_size_gallery_thumbnail']['crop'] ) && $mms['image_size_gallery_thumbnail']['crop'] === 'yes',
				],
			],
		];

		$this->addImageSizes();
	}

	private function addImageSizes() {
		foreach ( rtcl()->gallery['image_sizes'] as $image_key => $image_size ) {
			add_image_size( $image_key, $image_size["width"], $image_size["height"], $image_size["crop"] );
		}
	}
}