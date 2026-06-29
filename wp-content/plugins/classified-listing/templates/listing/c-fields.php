<?php
/**
 * @author        RadiusTheme
 * @package       classified-listing/templates/listing
 * @version       3.0.0
 *
 * @var Form  $form
 * @var array $fields
 * @var int   $listing_id
 */

use Rtcl\Helpers\Functions;
use Rtcl\Models\Form\Form;
use Rtcl\Services\FormBuilder\FBField;
use Rtcl\Services\FormBuilder\FBHelper;

if ( ! is_a( $form, Form::class ) ) {
	return;
}

$fields = $form->getFieldAsGroup( FBField::CUSTOM );
if ( count( $fields ) ) :
	$fields = FBHelper::reOrderCustomField( $fields );
	ob_start();
	foreach ( $fields as $index => $field ) {
		$field = new FBField( $field );
		if ( ! $field->isSingleViewAble() ) {
			continue;
		}
		$value = $field->getFormattedCustomFieldValue( $listing_id );

		if ( empty( $value ) ) {
			continue;
		}
		$icon = $field->getIconData();
		?>
		<div class="rtcl-cfp-item rtcl-cfp-<?php echo esc_attr( $field->getElement() ); ?>" data-name="<?php echo esc_attr( $field->getName() ); ?>"
			 data-uuid="<?php echo esc_attr( $field->getUuid() ); ?>">
			<?php
			if ( $field->getElement() === 'url' ) {
				$nofollow = ! empty( $field->getNofollow() ) ? ' rel="nofollow"' : '';
				?>
				<a href="<?php echo esc_url( $value ); ?>"
				   target="<?php echo esc_attr( $field->getTarget() ); ?>"<?php echo esc_html( $nofollow ); ?>><?php echo esc_html( $field->getLabel() ); ?></a>
				<?php
			} else {
				if ( ( ! empty( $icon['type'] ) && 'class' === $icon['type'] && ! empty( $icon['class'] ) ) || ! empty( $field->getLabel() ) ) {
					?>
					<div class="rtcl-cfp-label-wrap">
						<?php
						if ( ! empty( $icon['type'] ) && 'class' === $icon['type'] && ! empty( $icon['class'] ) ) {
							?>
							<div class="rtcl-field-icon"><i class="<?php echo esc_attr( $icon['class'] ); ?>"></i></div>
							<?php
						}
						if ( ! empty( $field->getLabel() ) ) {
							?>
							<div class='cfp-label'><?php echo esc_html( $field->getLabel() ); ?></div>
							<?php
						}
						?>
					</div>
				<?php } ?>
				<div class="cfp-value">
					<?php
					if ( 'repeater' === $field->getElement() ) {
						$repeaterFields = $field->getData( 'fields', [] );
						if ( ! empty( $repeaterFields ) && is_array( $value ) ) {
							$repeaterItemFields = $field->getField();
							$is_collapsable     = isset( $repeaterItemFields['collapsable'] ) && $repeaterItemFields['collapsable'] == 'yes' ? 'rtcl-is-collapsable' : 'rtcl-not-collapsable';
							$layout             = isset( $repeaterItemFields['layout'] ) ? 'layout_' . $repeaterItemFields['layout'] : '';
							?>
							<div class="rtcl-cfp-repeater-items <?php echo esc_attr( $is_collapsable . ' ' . $layout ); ?>">
								<?php
								foreach ( $value as $rValueIndex => $rValues ) {
									?>
									<div class="rtcl-cfp-repeater-item">
										<?php
										foreach ( $repeaterFields as $repeaterField ) {
											$rField = new FBField( $repeaterField );
											$rValue = 'file' === $rField->getElement() ? ( ! empty( $rValues[ $rField->getName() ] )
																						   && is_array( $rValues[ $rField->getName() ] )
												? FBHelper::getFieldAttachmentFiles( $listing_id, $rField->getField(), $rValues[ $rField->getName() ], true )
												: [] ) : ( $rValues[ $rField->getName() ] ?? '' );
											?>
											<div class="rtcl-cfp-repeater-field <?php echo esc_attr( $rField->getElement() ) ?>" data-name="<?php echo esc_attr( $field->getName() ); ?>"
												 data-uuid="<?php echo esc_attr( $field->getUuid() ); ?>">
												<?php
												$rIcon = $rField->getIconData();
												if ( ( ! empty( $rIcon['type'] ) && 'class' === $rIcon['type'] && ! empty( $rIcon['class'] ) )
													 || ! empty( $rField->getLabel() )
												) {
													?>
													<div class="rtcl-cfp-label-wrap">
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
																class='cfp-label'><?php echo esc_html( $rField->getLabel() ); ?></div>
															<?php
														}
														?>
													</div>
												<?php } ?>
												<div class="cfp-value">
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
					} else {
						Functions::print_html( FBHelper::getFormattedFieldHtml( $value, $field ) );
					}
					?>
				</div>
			<?php } ?>
		</div>
		<?php
	}
	$fieldData = ob_get_clean();
	if ( $fieldData ) :
		?>
		<div class="rtcl-single-custom-fields">
			<div class="rtcl-section-heading">
				<h3><?php echo esc_html( apply_filters( 'rtcl_custom_fields_section_title', __( 'Overview', 'classified-listing' ) ) ); ?></h3>
			</div>
			<div class="rtcl-cf-properties">
				<?php Functions::print_html( $fieldData, true ); ?>
			</div>
		</div>
	<?php
	endif;
	?>
<?php
endif;
