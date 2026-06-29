<?php

namespace Rtcl\Models\Form;

use Rtcl\Database\Eloquent\Model;
use Rtcl\Services\FormBuilder\AvailableFields;
use Rtcl\Services\FormBuilder\ElementCustomization;
use Rtcl\Services\FormBuilder\FBField;

/**
 * This is the model class for table "client".
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $status
 * @property string|null $appearance_settings
 * @property array|null $single_layout
 * @property array|null $sections
 * @property array|null $settings
 * @property object|null $fields
 * @property array|null $translations
 * @property string $type
 * @property object|null $conditions
 * @property string|null $created_by
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Form extends Model {

	protected $timestamps = false;

	protected $table = 'rtcl_forms';

	protected $casts = [
		'id'            => 'absint',
		'default'       => 'boolean',
		'settings'      => 'array',
		'single_layout' => 'array',
		'sections'      => 'array',
		'fields'        => 'array',
		'translations'  => 'array'
	];

	/**
	 * @param string $type enum[ 'name', 'uuid']
	 * @param string $value
	 *
	 * @return mixed|null
	 */
	public function getFieldBy( $type, $value ) {
		$type = in_array( $type, [ 'name', 'uuid', 'element', 'id' ] ) ? $type : 'uuid';
		if ( empty( $value ) ) {
			return null;
		}

		if ( $type === 'uuid' ) {
			return !empty( $this->fields[$value] ) ? $this->fields[$value] : null;
		}
		$fields = $this->fields;
		if ( !empty( $fields ) ) {
			foreach ( $fields as $field ) {
				if ( !empty( $field[$type] ) && $field[$type] === $value ) {
					return $field;
				}
			}
		}

		return null;
	}


	/**
	 * @param string $name
	 *
	 * @return mixed|null
	 */
	public function getFieldByName( $name ) {
		return $this->getFieldBy( 'name', $name );
	}


	/**
	 * @param string $uuid
	 *
	 * @return mixed|null
	 */
	public function getFieldByUuid( $uuid ) {
		return $this->getFieldBy( 'uuid', $uuid );
	}

	/**
	 * @param string $uuid
	 *
	 * @return mixed|null
	 */
	public function getFieldById( $uuid ) {
		return $this->getFieldBy( 'id', $uuid );
	}


	/**
	 * @param string $element
	 *
	 * @return mixed|null
	 */
	public function getFieldByElement( $element ) {
		return $this->getFieldBy( 'element', $element );
	}

	public function getFields() {
		return $this->fields;
	}

	public function getSingleLayout() {
		return  $this->single_layout;
	}

	/**
	 * @return array|mixed
	 */
	public function getSingleLayoutFields() {
		return  !empty( $this->single_layout['fields'] ) ? $this->single_layout['fields'] : [];
	}

	public function getSections() {
		return $this->sections;
	}

	/**
	 * @param string $uuid
	 *
	 * @return mixed|null
	 */
	public function getSectionByUuid( $uuid ) {
		return $this->getSectionBy( 'uuid', $uuid );
	}


	/**
	 * @param string $id
	 *
	 * @return mixed|null
	 */
	public function getSectionById( $id ) {
		return $this->getSectionBy( 'id', $id );
	}

	/**
	 * @param string $type enum[ 'id', 'uuid']
	 * @param string $value
	 *
	 * @return mixed|null
	 */
	public function getSectionBy( $type, $value ) {
		$type = in_array( $type, [ 'uuid', 'id' ] ) ? $type : 'uuid';
		if ( empty( $value ) ) {
			return null;
		}
		if ( $type === 'uuid' ) {
			return !empty( $this->sections[$value] ) ? $this->sections[$value] : null;
		}
		$sections = $this->sections;
		if ( !empty( $sections ) ) {
			foreach ( $sections as $section ) {
				if ( !empty( $section[$type] ) && $section[$type] === $value ) {
					return $section;
				}
			}
		}

		return null;
	}

	/**
	 * @param string $type can be preset, custom
	 *
	 * @return array|array[]|mixed
	 */
	public function getFieldAsGroup( $type = '' ) {
		$data = [ FBField::PRESET => [], FBField::CUSTOM => [] ];
		$fields = $this->fields;
		if ( !empty( $fields ) ) {
			foreach ( $fields as $fieldId => $field ) {
				$name = !empty( $field['name'] ) ? $field['name'] : '';
				if ( !$name ) {
					continue;
				}
				if ( isset( $field['preset'] ) && $field['preset'] == 1 ) {
					$data['preset'][$name] = $field;
				} else {
					$data['custom'][$name] = $field;
				}
			}
		}

		if ( in_array( $type, [ FBField::PRESET, FBField::CUSTOM, FBField::SECTIONS ] ) ) {
			return $data[$type];
		}

		return $data;
	}

	/**
	 * @return array|array[]|mixed
	 */
	public function getListableFields() {
		return $this->getArchiveViewAbleFields();
	}

	/**
	 * @return array|array[]|mixed
	 */
	public function getArchiveViewAbleFields() {
		$fields = $this->getFieldAsGroup( FBField::CUSTOM );
		$listableFields = [];
		if ( !empty( $fields ) ) {
			$listableFields = array_filter( $fields, function ( $field ) {
				return !empty( $field['archive_view'] );
			} );
		}

		return $listableFields;
	}

	/**
	 * @param string $language_code language code to translate
	 *
	 * @return void
	 */
	public function translatedForm( $language_code ) {
		if ( !empty( $this->translations[$language_code] ) ) {
			$translations = $this->translations[$language_code];
			$tempTranslations = $translations;

			// Form root fields
			$formFields = AvailableFields::translatableFormFields();
			if ( !empty( $formFields ) && is_array( $formFields ) ) {
				foreach ( $formFields as $field ) {
					if ( !empty( $field['id'] ) && !empty( $translations['form'][$field['id']] ) ) {
						$this->{$field['id']} = $translations['form'][$field['id']];
					}
				}
			}

			$formSettingFields = AvailableFields::settings();
			if ( !empty( $formSettingFields ) && is_array( $formSettingFields ) ) {
				$buttonKeys = [ 'submit_btn_text', 'update_btn_text' ];
				$settings = $this->settings;
				foreach ( $formSettingFields as $key => $formSettingField ) {
					if ( !empty( $translations['settings'][$key] ) ) {
						$settings[$key] = $translations['settings'][$key];
					} else if ( in_array( $key, $buttonKeys ) ) {
						$settings[$key] = '';
					}
				}
				$this->settings = $settings;
			}

			$sections = $this->sections;
			$formFields = $this->fields;
			if ( !empty( $sections ) ) {
				foreach ( $sections as $sectionIndex => $section ) {
					if ( !empty( $section['uuid'] ) && !empty( $translations[$section['uuid']] ) && is_array( $translations[$section['uuid']] ) ) {
						$sections[$sectionIndex] = $this->getTranslatedField( $translations[$section['uuid']], $section );
						unset( $tempTranslations[$section['uuid']] );
					}
					$sections[$sectionIndex]['logics'] = $this->getTranslatedConditionFieldValueForTaxonomy( $section['logics'], $formFields );
				}
			}
			$this->sections = $sections;


			if ( !empty( $formFields ) && !empty( $tempTranslations ) ) {
				foreach ( $tempTranslations as $uuid => $trValues ) {
					if ( !empty( $formFields[$uuid] ) ) {
						$formFields[$uuid] = $this->getTranslatedField( $trValues, $formFields[$uuid] );
					}
					if ( !empty( $formFields[$uuid]['logics'] ) ) {
						$formFields[$uuid]['logics'] = $this->getTranslatedConditionFieldValueForTaxonomy( $formFields[$uuid]['logics'], $formFields );
					}
				}
			}

			// Translate category ids
			if ( !empty( $formFields ) ) {
				foreach ( $formFields as $fieldId => $field ) {
					if ( !empty( $field['element'] ) && 'category' === $field['element'] && !empty( $field['top_level_ids'] ) && is_array( $field['top_level_ids'] ) ) {
						$formFields[$fieldId]['top_level_ids'] = array_map( function ( $categoryId ) {
							return apply_filters( 'wpml_object_id', $categoryId, rtcl()->category );
						}, $field['top_level_ids'] );
						break;
					}
				}
			}

			$this->fields = $formFields;
		}
	}

	/**
	 * @param $logics
	 * @param $formFields
	 * @return array
	 */
	private function getTranslatedConditionFieldValueForTaxonomy( $logics, $formFields ) {
		if ( empty( $logics['status'] ) || empty( $logics['conditions'] ) ) {
			return $logics;
		}

		$updatedLogics = $logics;
		foreach ( $logics['conditions'] as $conditionIndex => $condition ) {
			if ( empty( $condition['fieldId'] ) || empty( $formFields[$condition['fieldId']] ) || empty( $condition['value'] ) || empty( $formFields[$condition['fieldId']]['element'] ) || !in_array( $formFields[$condition['fieldId']]['element'], [ 'category', 'location', 'tag' ] ) ) {
				continue;
			}

			$field = $formFields[$condition['fieldId']];
			if ( $field['element'] === 'location' ) {
				$trId = apply_filters( 'wpml_object_id', $condition['value'], rtcl()->location );
			} else if ( $field['element'] === 'tag' ) {
				$trId = apply_filters( 'wpml_object_id', $condition['value'], rtcl()->tag );
			} else {
				$trId = apply_filters( 'wpml_object_id', $condition['value'], rtcl()->category );
			}
			$updatedLogics['conditions'][$conditionIndex]['value'] = $trId;
		}

		return $updatedLogics;
	}

	/**
	 * @param array $trData
	 * @param array $field
	 *
	 * @return array
	 */
	private function getTranslatedField( $trData, $field ) {

		foreach ( $trData as $fieldKey => $_translation ) {
			if ( isset( $field[$fieldKey] ) && !empty( $_translation ) ) {
				if ( $fieldKey === 'fields' && $field['element'] === 'repeater' ) {
					if ( !is_array( $_translation ) ) {
						continue;
					}

					if ( !empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
						foreach ( $field['fields'] as $repeaterFieldIndex => $repeaterField ) {

							if ( empty( $_translation[$repeaterField['uuid']] ) ) {
								continue;
							}
							foreach ( $_translation[$repeaterField['uuid']] as $innerFieldKey => $innerTr ) {
								if ( empty( $repeaterField[$innerFieldKey] ) || empty( $innerTr ) ) {
									continue;
								}
								$originalValue = $repeaterField[$innerFieldKey];
								$value = $this->getSanitizedTr( $originalValue, $innerTr, $innerFieldKey, $repeaterField );
								if ( $value !== '' ) {
									$field['fields'][$repeaterFieldIndex][$innerFieldKey] = $value;
								}
							}

						}
					}
				} else {
					$originalValue = !empty( $field[$fieldKey] ) ? $field[$fieldKey] : '';
					$value = $this->getSanitizedTr( $originalValue, $_translation, $fieldKey, $field );
					if ( !empty( $value ) ) {
						$field[$fieldKey] = $value;
					}
				}
			}
		}


		return $field;
	}

	/**
	 * @param $originalValue
	 * @param $_trValue
	 * @param $fieldKey
	 * @param $field
	 *
	 * @return array|mixed|string
	 */
	private function getSanitizedTr( $originalValue, $_trValue, $fieldKey, $field ) {
		if ( $fieldKey === 'options' || $fieldKey === 'advanced_options' ) {
			if ( is_array( $_trValue ) && is_array( $originalValue ) ) {
				foreach ( $_trValue as $index => $option ) {
					if ( !empty( $option['label'] ) ) {
						$originalValue[$index]['label'] = sanitize_text_field( $option['label'] );
					}
				}
			}
		} else if ( $fieldKey === 'validation' ) {
			if ( is_array( $_trValue ) ) {
				foreach ( $_trValue as $ruleKey => $_validation ) {
					if ( !empty( $_validation['message'] ) ) {
						$originalValue[$ruleKey]['message'] = sanitize_text_field( $_validation['message'] );
					}
				}
			}
		} elseif ( $fieldKey === 'tnc_html' ) {
			$originalValue = stripslashes( wp_kses( $_trValue, ElementCustomization::allowedHtml( $fieldKey ) ) );
		} elseif ( in_array( $fieldKey, [ 'tnc_html', 'html_codes' ] ) ) {
			$originalValue = stripslashes( wp_kses_post( $_trValue ) );
		} elseif ( $fieldKey === 'help_message' ) {
			$originalValue = sanitize_textarea_field( $_trValue );
		} else {
			$originalValue = sanitize_text_field( $_trValue );
		}

		return $originalValue;
	}

}