<?php

namespace Rtcl\Controllers\Ajax;

use Rtcl\Helpers\FnCall;
use Rtcl\Helpers\Functions;
use Rtcl\Models\RtclLicense;
use Rtcl\Resources\Options;
use Rtcl\Traits\SingletonTrait;
use stdClass;

class AjaxSettings {

	use SingletonTrait;

	public static function init() {
		add_action( 'wp_ajax_rtcl_get_media_by_media_id', [ __CLASS__, 'getMediaById' ] );

		add_action( 'wp_ajax_rtcl_save_setting_options', [ __CLASS__, 'save_setting_options' ] );
	}

	public static function save_setting_options() {

		if ( !Functions::verify_nonce() ) {
			wp_send_json_error( esc_html__( 'Session error !!', 'classified-listing' ) );

			return;
		}
		if ( !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission to save settings.', 'classified-listing' ) );

			return;
		}
		$dirtyData = [];
		if ( !empty( $_POST['dirtyData'] ) ) {
			$decoded = json_decode( wp_unslash( $_POST['dirtyData'] ), true );
			if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
				$dirtyData = $decoded;
			} else {
				wp_send_json_error( 'JSON decode failed: ' . json_last_error_msg() );

				return;
			}
		}

		$errors = [];
		if ( !empty( $dirtyData ) ) {
			$items = Options::option_items();
			$licenses = apply_filters( 'rtcl_licenses', [] );
			$licenseFields = [];
			foreach ( $dirtyData as $pKey => $pVal ) {
				if ( !empty( $items[$pKey] ) ) {
					$_item = $items[$pKey];
					if ( !empty( $_item['children'] ) && !empty( $pVal ) && is_array( $pVal ) ) {
						// First level
						foreach ( $pVal as $pcKey => $pcValue ) {
							if ( !empty( $_item['children'][$pcKey] ) ) {
								if ( isset( $_item['children'][$pcKey]['is_external'] ) && isset( $_item['children'][$pcKey]['set_data_fn'] ) ) {
									FnCall::call( $_item['children'][$pcKey]['set_data_fn']( $pcKey, $pcValue, $_item['children'][$pcKey]['fields'] ) );
								} else {
									$options = get_option( $pcKey, [] );
									foreach ( $pcValue as $cKey => $cValue ) {
										if ( !empty( $_item['children'][$pcKey]['fields'][$cKey] ) ) {
											$field = $_item['children'][$pcKey]['fields'][$cKey];
											if ( !empty( $field['is_external'] ) && !empty( $field['set_data_fn'] ) ) {
												FnCall::call( $field['set_data_fn']( $cKey, $cValue, $field ) );
											} else {
												$options[$cKey] = self::sanitizeOptionField( $field, $cValue );
											}
										}
									}
									update_option( $pcKey, apply_filters( 'rtcl_update_options_' . $pcKey, $options, $pcValue, $_item['children'][$pcKey]['fields'] ) );
								}
							}
						}
					} else {
						if ( !empty( $_item['fields'] ) && !empty( $pVal ) && is_array( $pVal ) ) {
							if ( isset( $_item['is_external'] ) && isset( $_item['set_data_fn'] ) ) {
								FnCall::call( $_item['set_data_fn']( $pKey, $pVal, $_item['fields'] ) );
							} else {
								$options = get_option( $pKey, [] );
								foreach ( $pVal as $cKey => $cValue ) {
									if ( isset( $_item['fields'][$cKey] ) ) {
										$field = $_item['fields'][$cKey];
										if ( !empty( $field['is_external'] ) && !empty( $field['set_data_fn'] ) ) {
											FnCall::call( $field['set_data_fn']( $cKey, $cValue, $field ) );
										} else {
											/*if ( !empty( $field['type'] ) && $field['type'] == 'license' && !empty( $licenses ) && is_array( $licenses ) ) {
												$oldValue = !empty( $_options[$cKey] ) ? $_options[$cKey] : '';
												if ( $oldValue !== $cValue ) {
													$index = array_search( $cKey, array_column( array_column( $licenses, 'api_data' ), 'key_name' ) );
													$license = $index !== false ? $licenses[$index] : null;
													if ( $license ) {
														if ( empty( $license['plugin_file'] ) || empty( $license['api_data'] ) || empty( $license['settings'] ) ) {
															return;
														}
														$licenseFields[] = [
															'parentId'  => $pKey,
															'optionId'  => $pKey,
															'fieldId'   => $cKey,
															'field'     => $field,
															'core_data' => $license
														];
													}
												}
											}*/
											$options[$cKey] = self::sanitizeOptionField( $field, $cValue );
										}
									}
								}
								update_option( $pKey, apply_filters( 'rtcl_update_options_' . $pKey, $options, $pVal, $_item['fields'] ) );
							}
						}
					}
				}

			}

			/*if ( !empty( $licenseFields ) ) {
				foreach ( $licenseFields as $_i => $_lField ) {
					$rtLicense = new RtclLicense( $_lField['core_data']['plugin_file'], $_lField['core_data']['api_data'], $_lField['core_data']['settings'] );
					$result = $rtLicense->force_update_licensing_status( 'rtcl_tools_settings' );
					if ( empty( $result['success'] ) && !empty( $result['message'] ) ) {
						if ( !empty( $_lField['optionId'] ) ) {
							if ( empty( $_lField['parentId'] ) || $_lField['parentId'] === $_lField['optionId'] ) {
								$errors[$_lField['optionId']][$_lField['fieldId']] = [ 'license' => $result['message'] ];
							} else {
								$errors[$_lField['parentId']][$_lField['optionId']][$_lField['fieldId']] = [ 'license' => $result['message'] ];
							}
						}
					}
				}
			}*/
		}
		update_option( 'rtcl_queue_flush_rewrite_rules', 'yes' );
		rtcl()->query->init_query_vars();
		rtcl()->query->add_endpoints();
		wp_send_json_success( [
			'message'     => esc_html__( 'Your settings have been saved successfully', 'classified-listing' ),
			'optionsData' => Functions::getOptionsData(),
			'errors'      => empty( $errors ) ? null : $errors,
			'items'       => Options::option_items(),
		] );
	}

	private static function sanitizeOptionField( $field, $value ) {
		if ( !isset( $value ) ) {
			return $value;
		}

		if ( !empty( $field['type'] ) ) {
			if ( $field['type'] === 'number' ) {
				$value = is_numeric( $value ) ? $value + 0 : 0;
			} else if ( $field['type'] === 'image' ) {
				$value = $value ? absint( $value ) : '';
			} else if ( $field['type'] === 'textarea' ) {
				$value = sanitize_textarea_field( wp_unslash( $value ) );
			} else if ( in_array( $field['type'], [ 'text', 'password', 'color' ] ) ) {
				$value = sanitize_text_field( wp_unslash( $value ) );
			} else if ( $field['type'] === 'wysiwyg' ) {
				$value = wp_kses_post( wp_unslash( $value ) );
				// in future we will remove multi_checkbox field type
			} else if ( in_array( $field['type'], [ 'checkbox', 'multi_checkbox', 'select', 'multiselect' ] ) && !empty( $field['options'] ) ) {
				if ( $field['type'] === 'select'  && empty( $field['multiselect'] ) ) {
					$value = sanitize_text_field( wp_unslash( $value ) );
					$value = $value && array_key_exists( $value, $field['options'] ) ? $value : '';
				} else {
					$values = [];
					if ( !empty( $value ) && is_array( $value ) ) {
						foreach ( $value as $v ) {
							$_value = sanitize_text_field( wp_unslash( $v ) );
							if ( $_value && array_key_exists( $_value, $field['options'] ) ) {
								$values[] = $_value;
							}
						}
					}
					$value = $values;
				}
			} else if ( $field['type'] === 'switch' || ( $field['type'] === 'checkbox' && empty( $field['options'] ) ) ) {
				$value = $value === 'yes' ? 'yes' : '';
			} else if ( $field['type'] === 'radio' || $field['type'] === 'select' ) {
				$_value = sanitize_text_field( wp_unslash( $value ) );
				if ( !is_array( $field['options'] ) && !empty( $field['options'] ) ) {
					if ( in_array( $_value, array_keys( $field['options'] ) ) ) {
						$value = $_value;
					} else {
						$value = '';
					}
				}
				wp_kses_post( $value );
			} else if ( $field['type'] === 'listingsPerRow' ) {
				if ( is_array( $value ) && !empty( $value ) ) {
					$value = [
						'desktop' => !empty( $value['desktop'] ) ? absint( $value['desktop'] ) : 1,
						'tablet'  => !empty( $value['tablet'] ) ? absint( $value['tablet'] ) : 1,
						'mobile'  => !empty( $value['mobile'] ) ? absint( $value['mobile'] ) : 1
					];
				} else {
					$value = [ 'desktop' => 1, 'tablet' => 1, 'mobile' => 1 ];
				}
			} else if ( $field['type'] === 'image_size' ) {
				if ( is_array( $value ) && !empty( $value ) ) {
					$value = [
						'width'  => !empty( $value['width'] ) ? absint( $value['width'] ) : 0,
						'height' => !empty( $value['height'] ) ? absint( $value['height'] ) : 0,
						'crop'   => ( !empty( $value['crop'] ) && $value['crop'] === 'yes' ) ? 'yes' : '',
					];
				} else {
					$value = [ 'width' => 0, 'height' => 0, 'crop' => 'no', ];
				}
			} else if ( $field['type'] === 'email' ) {
				$value = sanitize_email( wp_unslash( $value ) );
			} else if ( $field['type'] === 'multiselect' ) {
				if ( is_array( $value ) ) {
					$value = array_map( 'sanitize_text_field', wp_unslash( $value ) );
				} else {
					$value = [];
				}
			} else if ( $field['type'] === 'sidebarWidth' ) {
				if ( is_array( $value ) && !empty( $value ) ) {
					$size = !empty( $value['size'] ) ? absint( $value['size'] ) : 0;
					$unit = !empty( $value['unit'] ) && in_array( $value['unit'], [ 'px', '%' ], true )
						? $value['unit']
						: 'px';
					$value = [ 'size' => $size, 'unit' => $unit, ];
				} else {
					$value = [ 'size' => 0, 'unit' => 'px', ];
				}
			} else if ( $field['type'] === 'mapCenter' ) {
				if ( is_array( $value ) && !empty( $value ) ) {
					$address = !empty( $value['address'] ) ? sanitize_text_field( wp_unslash( $value['address'] ) ) : '';
					$lat = isset( $value['lat'] ) ? floatval( $value['lat'] ) : 0;
					$lng = isset( $value['lng'] ) ? floatval( $value['lng'] ) : 0;
					$value = [ 'address' => $address, 'lat' => $lat, 'lng' => $lng, ];
				} else {
					$value = [ 'address' => '', 'lat' => 0, 'lng' => 0, ];
				}
			}
		}

		return $value;
	}

	public static function getMediaById() {

		if ( !isset( $_POST['nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rt_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Session error !!', 'classified-listing' ) ] );

			return;
		}
		if ( !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to access media.', 'classified-listing' ) ] );

			return;
		}

		$mediaId = !empty( $_POST['mediaId'] ) ? absint( $_POST['mediaId'] ) : 0;
		if ( !$mediaId ) {
			wp_send_json_error( 'No media id found' );
		}
		$image_sizes = [ 'thumbnail', 'medium', 'large', 'full' ];
		$imageData = [
			'id' => $mediaId
		];

		foreach ( $image_sizes as $size ) {
			$src = wp_get_attachment_image_src( $mediaId, $size );
			if ( $src ) {
				$imageData['sizes'][$size] = [
					'url'    => $src[0],
					'width'  => $src[1],
					'height' => $src[2],
				];
			}
		}

		// Also include full size
		$full = wp_get_attachment_image_src( $mediaId, 'full' );
		if ( $full ) {
			$imageData['sizes']['full'] = [
				'url'    => $full[0],
				'width'  => $full[1],
				'height' => $full[2],
			];
		}

		wp_send_json_success( $imageData );
	}
}
