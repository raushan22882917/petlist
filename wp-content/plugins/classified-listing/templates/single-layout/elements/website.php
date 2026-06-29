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
$website = get_post_meta( $listing->get_id(), 'website', true );

if ( empty( $website ) ) {
	return;
} ?>
<a href="<?php echo esc_url( $website ); ?>"
   target="_blank" rel="nofollow"><?php _e( 'Website', 'classified-listing' ); ?></a>