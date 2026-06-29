<?php
/**
 * The template for displaying product content in the single-rtcl_listing.php template
 *
 * This template can be overridden by copying it to yourtheme/classified-listing/content-single-rtcl_listing.php.
 *
 * @package ClassifiedListing/Templates
 * @version 2.2.25
 */

use RadiusTheme\Petslist\Helper;
use RadiusTheme\Petslist\Options;

defined( 'ABSPATH' ) || exit;

global $listing;

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}

$style = Options::$options['listing_single_style'];

/**
 * Hook: rtcl_before_single_product.
 *
 * @hooked rtcl_print_notices - 10
 */
do_action( 'rtcl_before_single_listing' );

?>

<?php Helper::get_custom_listing_template( 'single/content-single-'.$style ); ?>

<?php do_action( 'rtcl_after_single_listing' ); ?>