<?php
/**
 *
 * @package ClassifiedListing/Templates
 * @version 5.2.0
 * @var string $fieldUuid
 * @var FBField $field
 * @var Listing $field
 */

use Rtcl\Helpers\Functions;
use RtclBooking\Helpers\Functions as BookingFunctions;
use Rtcl\Services\FormBuilder\FBField;
use Rtcl\Models\Listing;

defined( 'ABSPATH' ) || exit;

global $listing;
if ( !is_a( $field, FBField::class ) || !is_a( $listing, Listing::class ) ) {
	return;
}
$listing_id = $listing->get_id();
$post_status = get_post_status( $listing_id );

if ( class_exists(BookingFunctions::class) && ( BookingFunctions::is_active_booking( $listing_id ) && BookingFunctions::is_enable_booking() ) && 'publish' === $post_status ) {
	$type = BookingFunctions::get_booking_type( $listing_id );
	if ( ! empty( $type ) ) {
		Functions::get_template( 'booking/listing-booking-form',
			[
				'type'       => $type,
				'listing_id' => $listing_id
			],
			'',
			rtclBooking()->get_plugin_template_path()
		);
	}
}