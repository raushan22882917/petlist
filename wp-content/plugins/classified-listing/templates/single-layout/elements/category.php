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
$categories = [];
$rawCategories = $listing->get_categories();

if ( !empty( $rawCategories ) ) {
	foreach ( $rawCategories as $category ) {
		$term_link = get_term_link( $category ); // Get category URL
		if ( !is_wp_error( $term_link ) ) {
			$categories[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $term_link ),
				esc_html( $category->name )
			);
		}
	}
}

if ( empty( $categories ) ) {
	return;
}
$labelPlacement = !empty( $field->getSlField()['label_placement'] ) ? $field->getSlField()['label_placement'] : '';
?>
<div class="rtcl-sl-element rtcl-category-fields label-<?php echo esc_attr( $labelPlacement ) ?>">
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
				<div class='rtcl-slf-label'><?php echo esc_html( $field->getLabel() ); ?>:</div>
				<?php
			}
			?>
		</div>
	<?php } ?>
	<div class="rtcl-slf-value"><?php echo wp_kses_post( implode( "<span>, </span>", $categories ) ) ?></div>
</div>