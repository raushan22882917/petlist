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
$icon = $field->getIconData();
$vertical_cols  = ! empty( $fieldObj['vertical_cols'] ) ? 'cols-' . $fieldObj['vertical_cols'] : '';
$labelPlacement = !empty( $field->getSlField()['label_placement'] ) ? $field->getSlField()['label_placement'] : '';
?>
<div class="rtcl-sl-element label-<?php echo esc_attr( $labelPlacement ) ?> <?php echo esc_attr( $vertical_cols ) ?>">
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
		<?php Functions::print_html( FBHelper::getFormattedFieldHtml( $value, $field ) ); ?>
	</div>
</div>