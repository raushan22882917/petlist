<?php
/**
 * The template for displaying single listing builder
 *
 * This template can be overridden by copying it to yourtheme/classified-listing/single-layout/builder.php.
 *
 * @package ClassifiedListing/Templates
 * @version 5.2.0
 * @var array $singleLayout
 * @var Form $form
 */

use Rtcl\Helpers\Functions;
use Rtcl\Models\Form\Form;
use Rtcl\Services\FormBuilder\FBField;

defined( 'ABSPATH' ) || exit;
global $listing;

$singleLayout       = $form->getSingleLayout();
$singleLayoutFields = $form->getSingleLayoutFields();
$fields             = $form->getFields();

do_action( 'rtcl_before_single_listing' );
?>
	<div id="rtcl-listing-<?php the_ID(); ?>" <?php Functions::listing_class( '', $listing ); ?>>
		<div class="rtcl-sl-containers">
			<?php foreach ( $singleLayout['containers'] as $containerIndex => $container ) { ?>
				<div
					class="rtcl-sl-container<?php echo ! empty( $container['uuid'] ) ? ' rtcl-sl-container-' . esc_attr( $container['uuid'] ) : '' ?><?php echo ! empty( $container['container_class'] ) ? ' ' . esc_attr( $container['container_class'] ) : '' ?>"
					id="<?php echo ! empty( $container['id'] ) ? esc_attr( $container['id'] ) : '' ?>"
					data-index="<?php echo absint( $containerIndex ) ?>">
					<?php
					if ( ! empty( $container['columns'] ) && is_array( $container['columns'] ) ) {
						foreach ( $container['columns'] as $cColumnIndex => $cColumn ) { ?>
							<div class="rtcl-sl-container-column" data-index="<?php echo absint( $cColumnIndex ) ?>"
								 style="<?php echo ! empty( $cColumn['width'] ) ? 'width:' . absint( $cColumn['width'] ) . '%;' : '' ?>">
								<?php
								if ( ! empty( $cColumn['sections'] ) && is_array( $cColumn['sections'] ) ) {
									foreach ( $cColumn['sections'] as $sectionIndex => $section ) { ?>
										<div
											class="rtcl-sl-section<?php echo ! empty( $section['uuid'] ) ? ' rtcl-sl-section-' . esc_attr( $section['uuid'] ) : '' ?><?php echo ! empty( $section['container_class'] ) ? ' ' . esc_attr( $section['container_class'] ) : '' ?>"
											id="<?php echo ! empty( $section['id'] ) ? esc_attr( $section['id'] ) : '' ?>"
											data-index="<?php echo absint( $sectionIndex ) ?>">
											<?php if ( empty( $section['hide_title'] ) && ( ! empty( $section['title'] ) || ! empty( $section['icon']['class'] ) ) ) { ?>
												<div class="rtcl-sl-section-header">
													<?php if ( ! empty( $section['icon']['class'] ) ) { ?>
														<div class="rtcl-sl-section-icon">
															<i class="<?php echo esc_attr( $section['icon']['class'] ) ?>"></i>
														</div> <?php } // End Section icon ?>
													<?php if ( ! empty( $section['title'] ) ) { ?>
														<h3 class="rtcl-sl-section-title">
															<?php echo esc_html( $section['title'] ) ?>
														</h3> <?php } // End Section title ?>
												</div>
											<?php } // End Section title wrap
											if ( ! empty( $section['columns'] ) && is_array( $section['columns'] ) ) {
												$gap     = $section['gap'] ?? '';
												$presets = $section['presets'] ?? '';
												?>
												<div class="rtcl-sl-section-columns">
													<?php foreach ( $section['columns'] as $sColumnIndex => $sColumn ) { ?>
														<div class="rtcl-sl-section-column <?php echo esc_attr( $presets ) ?>"
															 data-index="<?php echo absint( $sColumnIndex ) ?>"
															 style="<?php echo ! empty( $sColumn['width'] ) ? 'flex:0 0 ' . absint( $sColumn['width'] ) . '%;' : '' ?>  <?php echo $gap ? 'gap:' . esc_attr( $gap ) . 'px' : ''; ?>">
															<?php if ( ! empty( $sColumn['fields'] ) && is_array( $sColumn['fields'] ) ) {
																foreach ( $sColumn['fields'] as $fieldIndex => $fieldUuid ) {
																	$field             = ! empty( $fields[ $fieldUuid ] ) ? new FBField( $fields[ $fieldUuid ] ) : null;
																	$singleLayoutField = ! empty( $singleLayoutFields[ $fieldUuid ] ) ? $singleLayoutFields[ $fieldUuid ] : null;
																	if ( ! $field && $singleLayoutField ) {
																		$field = new FBField( $singleLayoutField );
																	} elseif ( $field && $singleLayoutField ) {
																		$field->setSlField( $singleLayoutField );
																	}

																	if ( ! empty( $field ) ) {
																		Functions::get_template( 'single-layout/render-element', [ 'form' => $form, 'field' => $field, 'fieldUuid' => $fieldUuid ] );
																	} // End field exist
																} // End Sections fields loop
															} // End Sections fields exist?>
														</div>
													<?php } // End Sections columns loop ?>
												</div>
											<?php } // End Sections columns exist ?>
										</div>
										<?php
									} // End Sections loop
								}  // End Sections exist
								?>
							</div>
							<?php
						}  // End container column loop
					} // End container column exist
					?>
				</div>
			<?php } // End container loop ?>

			<!-- Related Listing -->
			<?php $listing->the_related_listings(); ?>

			<!-- Review  -->
			<?php do_action( 'rtcl_single_listing_review' ) ?>
		</div>
	</div>
<?php
do_action( 'rtcl_after_single_listing' );