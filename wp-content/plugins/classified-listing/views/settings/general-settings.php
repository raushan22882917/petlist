<?php

use Rtcl\Resources\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Payment
 */
$options = array(
	'ls_section'                             => array(
		'title'       => esc_html__( 'Listing Settings', 'classified-listing' ),
		'type'        => 'section',
		'description' => '',
	),
	'include_results_from'                   => array(
		'title'   => esc_html__( 'Include Results From', 'classified-listing' ),
		'type'    => 'multi_checkbox',
		'default' => array( 'child_categories', 'child_locations' ),
		'options' => array(
			'child_categories' => esc_html__( 'Child categories', 'classified-listing' ),
			'child_locations'  => esc_html__( 'Child locations', 'classified-listing' )
		)
	),
	'listing_duration'                       => [
		'title'       => esc_html__( 'Listing Duration (in days)', 'classified-listing' ),
		'type'        => 'number',
		'default'     => 15,
		'description' => esc_html__( 'Use a value of "0" to keep a listing alive indefinitely.', 'classified-listing' ),
	],
	'delete_expired_listings'                => [
		'title'       => esc_html__( 'Delete Expired Listings (in days)', 'classified-listing' ),
		'type'        => 'number',
		'default'     => 15,
		'description' => esc_html__( 'If you have the renewal notification enabled (Settings > Email > Notify users via email), this will be the number of days after the "Renewal Reminder" email was sent.',
			'classified-listing' )
	],
	'has_favourites'                         => [
		'title'   => esc_html__( 'Enable Add to Favourite', 'classified-listing' ),
		'type'    => 'checkbox',
		'default' => 'yes',
		'label'   => esc_html__( 'Check this to enable Favourite', 'classified-listing' )
	],
	'rss_feed_number'                        => array(
		'title'       => esc_html__( 'Number of Listings for RSS Feed', 'classified-listing' ),
		'type'        => 'number',
		'default'     => 10,
		'css'         => 'width:50px',
		'description' => esc_html__( 'Number of listings to show in RSS Feed', 'classified-listing' )
	),
	'renew'                                  => [
		'title' => esc_html__( 'Renew Listing', 'classified-listing' ),
		'label' => esc_html__( 'Enable renew listing', 'classified-listing' ),
		'type'  => 'checkbox'
	],
	'new_listing_status'                     => [
		'title'       => esc_html__( 'New Listing Status', 'classified-listing' ),
		'type'        => 'select',
		'default'     => 'pending',
		'options'     => Options::get_status_list(),
		'description' => esc_html__( 'Listing status at new listing', 'classified-listing' )
	],
	'edited_listing_status'                  => [
		'title'   => esc_html__( 'Listing Status after Edit', 'classified-listing' ),
		'type'    => 'select',
		'default' => 'pending',
		'options' => Options::get_status_list()
	],
	'redirect_new_listing'                   => [
		'title'       => esc_html__( 'Redirect after Submit Listing', 'classified-listing' ),
		'type'        => 'select',
		'default'     => 'submission',
		'options'     => Options::get_redirect_page_list(),
		'description' => esc_html__( 'Redirect after successfully post a new listing', 'classified-listing' )
	],
	'redirect_new_listing_custom'            => [
		'title'      => esc_html__( 'Custom Redirect URL after Submit Listing', 'classified-listing' ),
		'type'       => 'url',
		'dependency' => [
			'rules' => [
				'#rtcl_general_settings-redirect_new_listing' => [
					'type'  => 'equal',
					'value' => 'custom'
				]
			]
		]
	],
	'redirect_update_listing'                => [
		'title'       => esc_html__( 'Redirect after Edit Listing', 'classified-listing' ),
		'type'        => 'select',
		'class'       => 'rtcl-select2',
		'default'     => 'account',
		'options'     => Options::get_redirect_page_list(),
		'description' => esc_html__( 'Redirect after successfully post a new listing', 'classified-listing' )
	],
	'redirect_update_listing_custom'         => [
		'title'      => esc_html__( 'Custom Redirect URL after Edit Listing', 'classified-listing' ),
		'type'       => 'url',
		'dependency' => [
			'rules' => [
				'#rtcl_general_settings-redirect_update_listing' => [
					'type'  => 'equal',
					'value' => 'custom'
				]
			]
		]
	],
	'pending_listing_status_after_promotion' => [
		'title' => esc_html__( 'Publish Pending Listing after Payment Success', 'classified-listing' ),
		'type'  => 'checkbox',
		'label' => esc_html__( 'Allow pending listing to publish after payment success (Pay per ad & promotions)', 'classified-listing' )
	],
	'note_section'                           => array(
		'title' => esc_html__( 'Information', 'classified-listing' ),
		'type'  => 'section',
	),
	'admin_note_to_users'                    => array(
		'title'       => esc_html__( 'Admin Note to All Users', 'classified-listing' ),
		'type'        => 'textarea',
		'css'         => 'width:500px;min-height:100px',
		'description' => esc_html__( "This information will show to all user's dashboard.", 'classified-listing' )
	),
);

return apply_filters( 'rtcl_general_settings_options', $options );
