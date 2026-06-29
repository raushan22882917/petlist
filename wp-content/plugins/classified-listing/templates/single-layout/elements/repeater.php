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
$value = $field->getFormattedCustomFieldValue( $listing->get_id() );

if ( empty( $value ) ) {
	echo "<div class='has-no-value'></div>";
	return;
}

$repeaterItemFields = $field->getField();
$is_collapsable     = isset( $repeaterItemFields['collapsable'] ) && $repeaterItemFields['collapsable'] == 'yes' ? 'rtcl-is-collapsable' : 'rtcl-not-collapsable';
$layout             = isset( $repeaterItemFields['layout'] ) ? 'layout_' . $repeaterItemFields['layout'] : '';
$icon               = $field->getIconData();
$labelPlacement = !empty( $field->getSlField()['label_placement'] ) ? $field->getSlField()['label_placement'] : '';
?>
<div class="rtcl-sl-element <?php echo esc_attr( $is_collapsable . ' ' . $layout ); ?>  label-<?php echo esc_attr($labelPlacement)?>">
	<?php
	if ( ( ! empty( $icon['type'] ) && 'class' === $icon['type'] && ! empty( $icon['class'] ) ) || ! empty( $field->getLabel() ) ) {
		?>
		<div class="rtcl-slf-label-wrap rtcl-repeater-group-title">
			<?php
			if ( ! empty( $icon['type'] ) && 'class' === $icon['type'] && ! empty( $icon['class'] ) ) {
				?>
				<div class="rtcl-field-icon"><i class="<?php echo esc_attr( $icon['class'] ); ?>"></i></div>
				<?php
			}
			if ( ! empty( $field->getLabel() ) ) {
				?>
				<div class='rtcl-slf-label'><?php echo esc_html( $field->getLabel() ); ?></div>
				<?php
			}
			?>
		</div>
	<?php } ?>
	<div class="rtcl-slf-value">
		<?php
		$repeaterFields = $field->getData( 'fields', [] );
		if ( ! empty( $repeaterFields ) && is_array( $value ) ) {
			?>
			<div class="rtcl-slf-repeater-items">
				<?php
				foreach ( $value as $rValueIndex => $rValues ) {
					?>
					<div class="rtcl-slf-repeater-item">
						<?php
						foreach ( $repeaterFields as $repeaterField ) {
							$rField = new FBField( $repeaterField );
							$rValue = 'file' === $rField->getElement() ? ( ! empty( $rValues[ $rField->getName() ] )
																		   && is_array( $rValues[ $rField->getName() ] )
								? FBHelper::getFieldAttachmentFiles( $listing->get_id(), $rField->getField(), $rValues[ $rField->getName() ], true )
								: [] ) : ( $rValues[ $rField->getName() ] ?? '' );
							?>
							<div class="rtcl-slf-repeater-field <?php echo esc_attr( $rField->getElement() ) ?>"
								 data-name="<?php echo esc_attr( $field->getName() ); ?>"
								 data-uuid="<?php echo esc_attr( $field->getUuid() ); ?>">
								<?php
								$rIcon = $rField->getIconData();
								if ( ( ! empty( $rIcon['type'] ) && 'class' === $rIcon['type'] && ! empty( $rIcon['class'] ) )
									 || ! empty( $rField->getLabel() )
								) {
									?>
									<div class="rtcl-slf-label-wrap">
										<?php
										if ( ! empty( $rIcon['type'] ) && 'class' === $rIcon['type'] && ! empty( $rIcon['class'] ) ) {
											?>
											<div class="rtcl-field-icon"><i
													class="<?php echo esc_attr( $rIcon['class'] ); ?>"></i>
											</div>
											<?php
										}
										if ( ! empty( $rField->getLabel() ) ) {
											?>
											<div
												class='rtcl-slf-label'><?php echo esc_html( $rField->getLabel() ); ?></div>
											<?php
										}
										?>
									</div>
								<?php } ?>
								<div class="rtcl-slf-value">
									<?php Functions::print_html( FBHelper::getFormattedFieldHtml( $rValue, $rField ) ); ?>
								</div>
							</div>
							<?php
						}
						?>
					</div>
					<?php
				}
				?>
			</div>
			<?php
		}
		?>
	</div>
</div>