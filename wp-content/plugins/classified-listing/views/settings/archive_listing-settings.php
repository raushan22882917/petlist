<?php

use Rtcl\Resources\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Payment
 */
$options = array(
	'listing_section'   => [
		'title'       => esc_html__( 'Listing Archive Settings', 'classified-listing' ),
		'type'        => 'section',
		'description' => '',
	],
	'listings_per_page' => [
		'title'       => esc_html__( 'Listings Per Page', 'classified-listing' ),
		'type'        => 'number',
		'default'     => 20,
		'css'         => 'width:50px',
		'description' => esc_html__( 'Number of listings to show per page. Use a value of "0" to show all listings.',
			'classified-listing' )
	],
	'default_view'      => [
		'title'   => esc_html__( 'Default Listing View', 'classified-listing' ),
		'type'    => 'select',
		'default' => 'grid',
		'options' => [
			'list' => esc_html__( 'List view', 'classified-listing' ),
			'grid' => esc_html__( 'Grid view', 'classified-listing' ),
		],
	],
	'listings_per_row'  => [
		'title'       => esc_html__( 'Listings Per Row', 'classified-listing' ),
		'type'        => 'responsive_number',
		'default'     => [
			'desktop' => 3,
			'tablet'  => 2,
			'mobile'  => 1
		],
		'options'     => [
			'desktop' => esc_html__( 'Desktop', 'classified-listing' ),
			'tablet'  => esc_html__( 'Tablet', 'classified-listing' ),
			'mobile'  => esc_html__( 'Mobile', 'classified-listing' ),
		],
		'description' => esc_html__( 'Number of listings to show per row for grid view.',
			'classified-listing' ),
		'dependency'  => [
			'rules' => [
				'#rtcl_archive_listing_settings-default_view' => [
					'type'  => '=',
					'value' => 'grid'
				]
			]
		]
	],
	'orderby'           => array(
		'title'   => esc_html__( 'Listings Order by', 'classified-listing' ),
		'type'    => 'select',
		'default' => 'date',
		'options' => array(
			'title' => esc_html__( 'Title', 'classified-listing' ),
			'date'  => esc_html__( 'Date posted', 'classified-listing' ),
			'price' => esc_html__( 'Price', 'classified-listing' ),
			'views' => esc_html__( 'Views count', 'classified-listing' )
		)
	),
	'order'             => array(
		'title'   => esc_html__( 'Listings Sort by', 'classified-listing' ),
		'type'    => 'select',
		'default' => 'desc',
		'options' => array(
			'asc'  => esc_html__( 'Ascending', 'classified-listing' ),
			'desc' => esc_html__( 'Descending', 'classified-listing' )
		)
	),
	'taxonomy_orderby'  => array(
		'title'   => esc_html__( 'Category / Location Order by', 'classified-listing' ),
		'type'    => 'select',
		'default' => 'title',
		'options' => array(
			'name'        => esc_html__( 'Name', 'classified-listing' ),
			'id'          => esc_html__( 'Id', 'classified-listing' ),
			'count'       => esc_html__( 'Count', 'classified-listing' ),
			'slug'        => esc_html__( 'Slug', 'classified-listing' ),
			'_rtcl_order' => esc_html__( 'Custom Order', 'classified-listing' ),
			'none'        => esc_html__( 'None', 'classified-listing' ),
		),
	),
	'taxonomy_order'    => array(
		'title'   => esc_html__( 'Category / Location Sort by', 'classified-listing' ),
		'type'    => 'select',
		'default' => 'asc',
		'options' => array(
			'asc'  => esc_html__( 'Ascending', 'classified-listing' ),
			'desc' => esc_html__( 'Descending', 'classified-listing' )
		)
	),
	'display_options'   => [
		'title'   => esc_html__( 'Show in Listing', 'classified-listing' ),
		'type'    => 'multi_checkbox',
		'default' => [ 'date', 'user', 'views', 'category', 'location', 'excerpt', 'price' ],
		'options' => Options::get_listing_display_options()
	],
);

return apply_filters( 'rtcl_archive_listing_settings_options', $options );
