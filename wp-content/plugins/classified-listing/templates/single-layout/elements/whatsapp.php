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
$whatsapp_number = get_post_meta( $listing->get_id(), '_rtcl_whatsapp_number', true );

if ( ! Functions::check_visibility( $listing->get_author_id(), 'whatsapp' ) ) {
	return;
}

if ( empty( $whatsapp_number ) ) {
	return;
}
$icon = $field->getIconData();
$labelPlacement = !empty( $field->getSlField()['label_placement'] ) ? $field->getSlField()['label_placement'] : '';
?>
<div class="rtcl-sl-element label-<?php echo esc_attr($labelPlacement)?>">
	<?php
	if ( ( !empty( $icon['type'] ) && 'class' === $icon['type'] && !empty( $icon['class'] ) ) || !empty( $field->getLabel() ) ) {
		?>
		<div class="rtcl-slf-label-wrap">
			<?php
			if ( !empty( $icon['type'] ) && 'class' === $icon['type'] && !empty( $icon['class'] ) ) {
				?>
				<div class="rtcl-field-icon"><i class="<?php echo esc_attr( $icon['class'] ); ?>"></i></div>
				<?php
			}
			if ( !empty( $field->getLabel() ) ) {
				?>
				<div class='rtcl-slf-label'><?php echo esc_html( $field->getLabel() ); ?></div>
				<?php
			}
			?>
		</div>
	<?php } ?>
	<div class="rtcl-slf-value">
		<a href="tel:<?php echo esc_attr( $whatsapp_number ); ?>"><?php echo esc_html( $whatsapp_number ); ?></a>
	</div>
</div>