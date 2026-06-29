<?php

namespace Rtcl\Services\FormBuilder\Components;

use Rtcl\Services\FormBuilder\AvailableFields;

class SectionSanitization {

	public $sections = [];
	public $fields   = [];

	public function __construct( $sections, $fields ) {
		$this->sections = ! empty( $sections ) ? $sections : [];
		$this->fields   = $fields;
	}

	public function validated() {
		return $this->sections;
	}

	public function get(): array {
		if ( ! empty( $this->sections ) ) {
			$sections = [];
			foreach ( $this->sections as $section ) {
				if ( empty( $section['element'] ) ) {
					continue;
				}
				$sanitizeSection = $this->sanitizeSection( $section );
				if ( ! empty( $sanitizeSection ) ) {
					$sections[] = $sanitizeSection;
				}
			}

			$this->sections = $sections;
		}

		return $this->sections;
	}

	private function sanitizeSection( $rawSection ): array {
		$defaultValues = AvailableFields::getSectionField();
		if ( empty( $defaultValues ) ) {
			return [];
		}
		$section = wp_parse_args( $rawSection, $defaultValues );

		if ( isset( $section['editor'] ) ) {
			unset( $section['editor'] );
		}
		if ( ! empty( $section['columns'] ) ) {
			foreach ( $section['columns'] as $columnIndex => $column ) {
				if($column['width']){
					$section['columns'][ $columnIndex ]['width'] = absint( $column['width'] );
				}
				if ( ! empty( $column['fields'] ) ) {
					foreach ( $column['fields'] as $fieldIndex => $fieldId ) {
						$_fieldId = sanitize_text_field( $fieldId );
						if ( ! empty( $this->fields[ $_fieldId ] ) ) {
							$section['columns'][ $columnIndex ]['fields'][ $fieldIndex ] = $_fieldId;
						}
					}
				} else {
					$section['columns'][ $columnIndex ]['fields'] = [];
				}
			}
		}

		foreach ( $section as $sectionKey => $value ) {
			if ( $sectionKey === 'logics' ) {
				if ( isset( $value['status'] ) && in_array( $value['status'], [ 'true', 'false' ], true ) ) {
					if ( $value['status'] === 'true' ) {
						$value['status'] = true;
					} else {
						$value = '';
					}
				}
				if ( ! empty( $value['status'] ) && ! empty( $value['conditions'] ) ) {
					$conditions = [];
					foreach ( $value['conditions'] as $condition ) {
						if ( ! empty( $condition['fieldId'] ) && ! empty( $condition['operator'] ) ) {
							$conditions[] = $condition;
						}
					}
					if ( empty( $conditions ) ) {
						$value = '';
					}
				}
				$section[ $sectionKey ] = $value;
			} if ( in_array( $sectionKey, [ 'title', 'uuid', 'id', 'container_class' ] ) ) {
				$section[ $sectionKey ] = sanitize_text_field( wp_unslash( $value ) );
			} else {

				if ( in_array( $value, [ 'true', 'false' ], true ) ) {
					$section[ $sectionKey ] = $value === 'true';
				}
			}
		}

		return $section;
	}
}