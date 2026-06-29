<?php
/**
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.1.4
 */

use Rtcl\Helpers\Functions;

global $listing;

$sidebar_position = Functions::get_option_item( 'rtcl_single_listing_settings', 'detail_page_sidebar_position', 'right' );

if ( in_array( $sidebar_position, array( 'left', 'right' ) ) ) {
	$listing_info_class = 'rtcl-listing-sidebar-info-wrap';
} else {
	$listing_info_class = 'rtcl-listing-bottom-sidebar';
}
?>

<!-- Seller / User Information -->
<div class="<?php echo esc_attr( $listing_info_class ); ?>">
	<div class="listing-sidebar">
		<?php $listing->the_user_info(); ?>
		<?php do_action( 'rtcl_after_single_listing_sidebar', $listing->get_id() ); ?>
	</div>
</div>
