<?php
/**
 * @author        RadiusTheme
 * @package       classified-listing/templates/listing
 * @version       1.0.0
 *
 * @var array $fields
 * @var int $listing_id
 */

use Rtcl\Helpers\Functions;
use Rtcl\Models\RtclCFGField;
use RadiusTheme\Petslist\Options;

$group_ids = isset( Options::$options['custom_group_individual'] ) ? Options::$options['custom_group_individual'] : [];

if ( ! empty( $group_ids ) ) :
	ob_start();

	$fields = [];
	foreach ( $group_ids as $id) {
		$field_list = Functions::get_cf_ids_by_cfg_id( $id );
		$fields  = array_merge( $fields, $field_list ) ; 
	}
	?>
        <li class="list-group-item">
			<span class="cfp-label">
				<i class="rtcl-icon rtcl-icon flaticon-tag"></i>
				<span><?php echo esc_html__( "ID:", "petslist" ) ?></span>
			</span>
            <span class="cfp-value"><?php echo esc_html( $listing_id ) ?></span>
        </li>
	<?php

	foreach ( $fields as $field )  :
		$field = new RtclCFGField( $field );
		$value = $field->getFormattedCustomFieldValue( $listing_id );

		$field_icon = $field->getIconClass();

		if ( ! empty( $value ) ) : ?>
            <li class="list-group-item rtcl-field-<?php echo esc_attr( $field->getType() ) ?> rtcl-field-slug-<?php echo esc_attr($field->getSlug()) ?>">
				<span class="cfp-label">
					<span><?php echo esc_html( $field->getLabel() ) ?>:</span>
				</span>
				<span class="cfp-value">
					<?php if ( $field->getType() === 'url' ):
						$nofollow = ! empty( $field->getNofollow() ) ? ' rel="nofollow"' : ''; ?>
						<a href="<?php echo esc_url( $value ); ?>" target="<?php echo esc_attr( $field->getTarget() ) ?>"<?php echo esc_html( $nofollow ) ?>><?php echo esc_url( $value ); ?></a>
						<?php elseif($field->getType() === 'checkbox') : ?>
							<?php
								$c_val = explode( ',', $value );
								foreach ( $c_val as $val ) {
									echo "<span><i class='fas fa-check-circle'></i>" . esc_html( $val ) . "</span>";
								}
							?>
					<?php else : ?>
						<?php Functions::print_html( $value ); ?>
					<?php endif; ?>
				</span>
            </li>
		<?php endif; ?>
	<?php endforeach;
	$fieldData = ob_get_clean();
	?>
	<?php
	if ( $fieldData ) :
		printf( '<ul class="list-group custom-field-wrapper">%s</ul>', $fieldData );
	endif; ?>
<?php endif;
