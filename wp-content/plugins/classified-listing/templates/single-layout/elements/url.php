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
$value = $field->getFormattedCustomFieldValue( $listing->get_id() );

if ( empty( $value ) ) {
	echo "<div class='has-no-value'></div>";
	return;
}
$nofollow = !empty( $field->getNofollow() ) ? ' rel="nofollow"' : '';
?>
<a href="<?php echo esc_url( $value ); ?>"
   target="<?php echo esc_attr( $field->getTarget() ); ?>"<?php echo esc_html( $nofollow ); ?>><?php echo esc_html( $field->getLabel() ); ?></a>