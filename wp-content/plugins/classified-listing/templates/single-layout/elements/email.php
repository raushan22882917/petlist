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
if ( ! is_a( $field, FBField::class ) || ! is_a( $listing, Listing::class ) ) {
	return;
}

$email = get_post_meta( $listing->get_id(), 'email', true );

if ( ! Functions::check_visibility( $listing->get_author_id(), 'email' ) ) {
	return;
}

if ( empty( $email ) ) {
	echo "<div class='has-no-value'></div>";

	return;
}
?>
<a href="mailto:<?php echo esc_url( $email ); ?>"><?php echo esc_html( $email ); ?></a>