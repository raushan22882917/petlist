<?php
/**
 *
 * @package ClassifiedListing/Templates
 * @version 5.2.0
 */

use Rtcl\Models\Listing;
use RtclPro\Controllers\Hooks\TemplateHooks;

defined( 'ABSPATH' ) || exit;
global $listing;

if ( ! is_a( $listing, Listing::class ) ) {
	return;
}

TemplateHooks::add_chat_link( $listing );