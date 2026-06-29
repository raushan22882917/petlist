<?php

namespace Rtcl\Services\FormBuilder\Components;

use Rtcl\Services\FormBuilder\AvailableFields;
use Rtcl\Services\FormBuilder\ElementCustomization;

class FieldSanitization {

	public $fields = [];

	public function __construct( $fields ) {
		$this->fields = !empty( $fields ) ? $fields : [];
	}

	public function validated() {
		return $this->fields;
	}

	public function get(): array {
		if ( !empty( $this->fields ) ) {
			$fields = [];
			foreach ( $this->fields as $fieldId => $field ) {
				if ( empty( $field['element'] ) ) {
					continue;
				}
				$sanitizeField = $this->sanitizeField( $field );
				if ( !empty( $sanitizeField ) ) {
					$fields[$fieldId] = $sanitizeField;
				}
			}
			$this->fields = $fields;
		}

		return $this->fields;
	}

	private function sanitizeField( $rawField ): array {

		$availableFields = AvailableFields::get();
		$defaultValues = !empty( $availableFields[$rawField['element']] ) ? $availableFields[$rawField['element']] : null;
		if ( empty( $defaultValues ) ) {
			return [];
		}
		$field = wp_parse_args( $rawField, $defaultValues );
		if ( isset( $defaultValues['validation'] ) ) {
			$tmpValidationRules = wp_parse_args( $field['validation'], $defaultValues['validation'] );
			$validationRules = [];
			foreach ( $tmpValidationRules as $ruleKey => $ruleValues ) {
				if ( $ruleKey === 'required' ) {
					$ruleValues['value'] = is_string( $ruleValues['value'] ) ? $ruleValues['value'] === 'true' : $ruleValues['value'];
				} elseif ( $ruleKey === 'max_file_count' ) {
					$ruleValues['value'] = isset( $ruleValues['value'] ) && $ruleValues['value'] !== '' ? absint( $ruleValues['value'] ) : '';
				} elseif ( $ruleKey === 'max_file_size' ) {
					$ruleValues['value'] = isset( $ruleValues['value'] ) && $ruleValues['value'] !== '' ? (int)$ruleValues['value'] : '';
				}
				$validationRules[$ruleKey] = $ruleValues;
			}

			$field['validation'] = $validationRules;
		}

		if ( isset( $field['editor'] ) ) {
			unset( $field['editor'] );
		}
		$depensOnField = null;
		foreach ( $field as $fieldKey => $value ) {
			if ( in_array( $fieldKey, [ 'label', 'id', 'class', 'container_class', 'placeholder', 'order', 'help_message', 'btn_text' ] ) ) {
				$field[$fieldKey] = sanitize_text_field( wp_unslash( $value ) );
			} elseif ( $fieldKey === 'fields' ) {
				if ( !empty( $value ) && is_array( $value ) ) {
					$field['fields'] = array_map( function ( $_field ) {
						return $this->sanitizeField( $_field );
					}, $value );
				}
			} elseif ( $fieldKey === 'tnc_html' ) {
				$field[$fieldKey] = stripslashes( wp_kses( $value, ElementCustomization::allowedHtml( $fieldKey ) ) );
			} elseif ( $fieldKey === 'html_codes' ) {
				$field[$fieldKey] = stripslashes( wp_kses_post( $value ) );
			} elseif ( $fieldKey === 'top_level_ids' ) {
				if ( !empty( $value ) && is_array( $value ) ) {
					$field[$fieldKey] = array_map( 'absint', $value );
				}
			} elseif ( $fieldKey === 'price_unit_catIds' ) {
				if ( !empty( $value ) && is_array( $value ) ) {
					$unitCatIds = [];
					foreach ( $value as $unitKey => $catIds ) {
						$catIds = array_filter( array_map( 'absint', $catIds ) );
						if ( !empty( $catIds ) ) {
							$unitCatIds[$unitKey] = $catIds;
						}
					}
					if ( !empty( $unitCatIds ) ) {
						$field[$fieldKey] = $unitCatIds;
					} else {
						unset( $field[$fieldKey] );
					}
				} else {
					unset( $field[$fieldKey] );
				}
			} elseif ( $fieldKey === 'filter' ) {
				if ( !empty( $value ) && is_array( $value ) ) {
					$ids = !empty( $value['ids'] ) ? array_filter( array_map( 'absint', $value['ids'] ) ) : [];
					if ( !empty( $ids ) ) {
						$field[$fieldKey] = [
							'ids'  => $ids,
							'mode' => !empty( $value['mode'] ) && in_array( $value['mode'], [ 'include', 'exclude' ] ) ? $value['mode'] : 'include',
						];
					}
				}
			} elseif ( $fieldKey === 'logics' ) {
				if ( isset( $value['status'] ) && in_array( $value['status'], [ 'true', 'false' ], true ) ) {
					if ( $value['status'] === 'true' ) {
						$value['status'] = true;
					} else {
						$value = '';
					}
				}
				if ( !empty( $value['status'] ) && !empty( $value['conditions'] ) ) {
					$conditions = [];
					foreach ( $value['conditions'] as $condition ) {
						if ( !empty( $condition['fieldId'] ) && !empty( $condition['operator'] ) ) {
							$conditions[] = $condition;
						}
					}
					if ( empty( $conditions ) ) {
						$value = '';
					}
				}
				$field[$fieldKey] = $value;
			} else if ( $fieldKey === 'default_value' ) {
				if ( in_array( $field['element'], [ 'checkbox', 'radio' ] ) ) {
					$value = $field['default_value'];
					$setDefaultValue = false;
					if ( !empty( $field['options'] ) && is_array( $field['options'] ) && !empty( $value ) ) {
						$optionValues = array_column( $field['options'], 'value' );
						if ( $field['element'] === 'checkbox' ) {
							if ( is_array( $value ) ) {
								$_defaultValues = [];
								foreach ( $value as $k => $v ) {
									if ( in_array( $v, $optionValues ) ) {
										$_defaultValues[] = sanitize_text_field( wp_unslash( $v ) );
									}
								}
								if ( !empty( $_defaultValues ) ) {
									$setDefaultValue = true;
									$field['default_value'] = $_defaultValues;
								}
							}
						} else {
							if ( in_array( $value, $optionValues ) ) {
								$setDefaultValue = true;
								$field['default_value'] = sanitize_text_field( wp_unslash( $value ) );
							}
						}
					}
					if ( !$setDefaultValue ) {
						unset( $field['default_value'] );
					}
				} else {
					$field['default_value'] = sanitize_text_field( wp_unslash( $value ) );
				}
			} elseif ( 'option_depends_on' === $fieldKey ) { // Sanitize option_depends_on
				if ( $value && $this->fields[$value] && in_array( $field['element'], [ 'checkbox', 'radio', 'select' ] ) ) {
					$field[$fieldKey] = $value;
					$depensOnField = $this->fields[$field['option_depends_on']];
				} else {
					unset( $field['option_depends_on'] );
				}
			} else {
				if ( in_array( $value, [ 'true', 'false' ], true ) ) {
					$field[$fieldKey] = $value === 'true';
				}
			}
		}

		// options field refine for option_depends_on field
		if ( in_array( $field['element'], [ 'checkbox', 'radio', 'select' ] ) ) {
			if ( !empty( $field['options'] ) && is_array( $field['options'] ) ) {
				$dependsOnFieldOptionKeys = [];

				if ( !empty( $field['option_depends_on'] ) && $depensOnField && !empty( $depensOnField['options'] ) && is_array( $depensOnField['options'] ) ) {
					$dependsOnFieldOptionKeys = array_column( $depensOnField['options'], 'value' );
				}

				$field['options'] = array_map( function ( $option ) use ( $dependsOnFieldOptionKeys ) {
					if ( isset( $option['depends_on'] ) ) {
						if ( empty( $dependsOnFieldOptionKeys ) || !is_array($option['depends_on']) ) {
							unset( $option['depends_on'] );
						}else{
							$matched = array_intersect($option['depends_on'], $dependsOnFieldOptionKeys); 
							if( !empty( $matched ) ) {
								$option['depends_on'] = $matched;
							}else{
								unset( $option['depends_on'] );
							}
						}
					}
					return $option;
				}, $field['options'] );
			}
		}

		return $field;
	}
}