<?php
/**
 *
 * @package ClassifiedListing/Templates
 * @version 5.2.0
 * @var Form $form
 * @var string $fieldUuid
 * @var FBField $field
 * @var Listing $field
 */

use Rtcl\Helpers\Functions;
use Rtcl\Models\Form\Form;
use Rtcl\Models\Listing;
use Rtcl\Services\FormBuilder\FBField;
use Rtcl\Services\FormBuilder\FBHelper;

defined( 'ABSPATH' ) || exit;
global $listing;
if ( !is_a( $field, FBField::class ) || !is_a( $listing, Listing::class ) ) {
	return;
}

$latitude = get_post_meta( $listing->get_id(), 'latitude', true );
$longitude = get_post_meta( $listing->get_id(), 'longitude', true );
$address = null;
if ( 'geo' === Functions::location_type() ) {
	$address = esc_html( wp_strip_all_tags( get_post_meta( $listing->get_id(), '_rtcl_geo_address', true ) ) );
}

if ( !$address ) {
	$locations = [];
	$rawLocations = $listing->get_locations();
	if ( count( $rawLocations ) ) {
		foreach ( $rawLocations as $location ) {
			$locations[] = $location->name;
		}
	}
	if ( $zipcode = get_post_meta( $listing->get_id(), 'zipcode', true ) ) {
		$locations[] = esc_html( $zipcode );
	}
	if ( $address = get_post_meta( $listing->get_id(), 'address', true ) ) {
		$locations[] = esc_html( $address );
	}
	$locations = array_reverse( $locations );
	$address = !empty( $locations ) ? implode( ',', $locations ) : null;
}
$map_options = [];
$map_settings = [
	'has_map'     => Functions::has_map() && !Functions::hide_map( $listing->get_id() ),
	'latitude'    => $latitude,
	'longitude'   => $longitude,
	'address'     => $address,
	'map_options' => $map_options
];
$map_settings = apply_filters( 'rtcl_single_listing_map_settings', $map_settings ); // Filter Added By Rashid
Functions::get_template( "listing/map", $map_settings );