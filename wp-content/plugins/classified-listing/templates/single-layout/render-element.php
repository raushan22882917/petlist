<?php
/**
 *
 * @package ClassifiedListing/Templates
 * @version 5.2.0
 * @var Form $form
 * @var string $fieldUuid
 * @var FBField $field
 */

use Rtcl\Helpers\Functions;
use Rtcl\Models\Form\Form;
use Rtcl\Services\FormBuilder\FBField;

defined( 'ABSPATH' ) || exit;
if ( !is_a( $field, FBField::class ) || in_array( $field->getElement(), [ 'custom_html', 'input_hidden', 'terms_and_condition', 'view_count' ] ) ) {
	return;
}
$elementFile = str_replace( [ '_' ], [ '-' ], strtolower( $field->getElement() ) );

$container_classes = [
	'rtcl-sl-element-wrap',
	'rtcl-sl-element-' . esc_attr( $fieldUuid ),
];

if ( ! empty( $field->getField()['container_class'] ) ) {
	$container_classes[] = esc_attr( $field->getField()['container_class'] );
}

$element_attr_id = ! empty( $field->getField()['id'] )
	? ' id="' . esc_attr( $field->getField()['id'] ) . '"'
	: '';

$data_element = esc_attr( $field->getElement() );
?>

<div
	class="<?php echo implode( ' ', $container_classes ); ?>"
	data-element="<?php echo $data_element; ?>"<?php echo $element_attr_id; ?>>
	<?php
	Functions::get_template(
		'single-layout/elements/' . $elementFile,
		[
			'form'      => $form,
			'field'     => $field,
			'fieldUuid' => $fieldUuid,
		]
	);
	?>
</div>