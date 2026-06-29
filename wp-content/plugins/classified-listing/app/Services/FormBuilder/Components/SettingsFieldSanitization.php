<?php

namespace Rtcl\Services\FormBuilder\Components;

use Rtcl\Services\FormBuilder\AvailableFields;

class SettingsFieldSanitization {

	public $fields = [];
	public $values = [];

	public function __construct( $values ) {
		$this->values = !empty( $values ) ? $values : [];
		$this->fields = AvailableFields::settings();
	}

	public function validated() {
		return $this->fields;
	}

	public function get(): array {
		$values = [];
		if ( !empty( $this->values ) ) {
			foreach ( $this->values as $fieldKey => $fieldValue ) {
				$sanitizeField = $this->sanitizeField( $fieldKey, $fieldValue );
				if ( !empty( $sanitizeField ) ) {
					$values[$fieldKey] = $sanitizeField;
				}
			}
			$this->values = $values;
		} else {
			$this->values = [];
		}

		return $this->values;
	}


	private function sanitizeField( $fieldKey, $fieldValue ) {
		if ( empty( $this->fields[$fieldKey] ) ) {
			return null;
		}
		$field = $this->fields[$fieldKey];
		$value = null;
		if ( 'icon' === $field['type'] ) {
			if ( is_array( $fieldValue ) ) {
				$value = array_filter( array_map( 'sanitize_text_field', $fieldValue ) );
			}
		} elseif ( 'textarea' === $field['type'] ) {
			$value = stripslashes( wp_kses_post( $fieldValue ) );
		} elseif ( $field['type'] === 'select' ) {
			if ( !empty( $field['option'] ) && is_array( $field['option'] ) && array_key_exists( $fieldValue, $field['option'] ) ) {
				$value = $fieldValue;
			}
		} elseif ( 'switch' === $field['type'] ) {
			if ( in_array( $fieldValue, [ 'true', 'false' ], true ) ) {
				$value = $fieldValue === 'true';
			}
		} elseif ( 'category_filter' === $field['type'] ) {
			if ( is_array( $fieldValue ) ) {
				$categories = !empty( $fieldValue['categories'] ) ? array_filter( array_map( 'absint', $fieldValue['categories'] ) ) : [];
				if ( !empty( $categories ) ) {
					$value = [
						'categories' => $categories,
						'mode'       => !empty( $fieldValue['mode'] ) && in_array( $fieldValue['mode'], [ 'include', 'exclude' ] ) ? $fieldValue['mode'] : 'include',
					];
				}
			}
		} else {
			$value = sanitize_text_field( wp_unslash( $fieldValue ) );
		}

		return $value;
	}
}