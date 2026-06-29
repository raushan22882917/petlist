<?php
/**
 * @package ClassifiedListing/Templates
 * @version 1.5.4
 */

use Rtcl\Helpers\Functions;
use RadiusTheme\Petslist\Helper;
use RadiusTheme\Petslist\Options;

global $listing;

if ( isset( $_GET['view'] ) && in_array( $_GET['view'], [ 'grid', 'list' ], true ) ) {
	$view = esc_attr( $_GET['view'] );
} else {
	$view = Functions::get_option_item( 'rtcl_general_settings', 'default_view', 'list' );
}

$style = Options::$options['listing_archive_style'];

?>
<div <?php Functions::listing_class('listing-layout-'.$style, $listing) ?><?php Functions::listing_data_attr_options() ?>>
    <?php
		if ( $view == 'grid' ) {
			Helper::get_custom_listing_template( 'archive/grid' );
		} else {
			Helper::get_custom_listing_template( 'archive/list' );
		}
	?>
</div>
