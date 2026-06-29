<?php

namespace Rtcl\Controllers\Admin;

use Rtcl\Models\Form\Form;
use Rtcl\Services\FormBuilder\EditorShortcodeParser;
use Rtcl\Traits\SingletonTrait;

class FormBuilderController {

	use SingletonTrait;

	public function __construct() {
		add_action( 'rtcl/fb/parse_default_value', [ $this, 'parse_default_value' ], 10, 3 );
	}

	/**
	 * @param mixed $value
	 * @param array $field
	 * @param Form  $form
	 *
	 * @return mixed
	 */
	public function parse_default_value( $value, $field, $form ) {
		return EditorShortcodeParser::filter( $value, $field, $form );
	}
}