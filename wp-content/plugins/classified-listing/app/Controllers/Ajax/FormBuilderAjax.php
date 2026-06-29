<?php

namespace Rtcl\Controllers\Ajax;

use Exception;
use Rtcl\Controllers\AIServiceFactory;
use Rtcl\Controllers\Hooks\Filters;
use Rtcl\Helpers\Functions;
use Rtcl\Models\Form\Form;
use Rtcl\Resources\Gallery;
use Rtcl\Resources\Options;
use Rtcl\Services\FormBuilder\FBHelper;
use Rtcl\Traits\SingletonTrait;
use WP_Error;

class FormBuilderAjax {

	use SingletonTrait;

	public function init(): void {
		add_action( 'wp_ajax_rtcl_fb_get_category', [ $this, 'get_category' ] );
		add_action( 'wp_ajax_rtcl_get_terms', [ $this, 'get_terms_callback' ] );
		add_action( 'wp_ajax_rtcl_fb_filtered_get_categories', [ $this, 'get_filtered_categories' ] );
		add_action( 'wp_ajax_rtcl_fb_get_location', [ $this, 'get_location' ] );

		add_action( 'wp_ajax_rtcl_fb_gallery_image_upload', [ $this, 'gallery_image_upload' ] );
		add_action( 'wp_ajax_rtcl_fb_gallery_image_update_as_feature', [ $this, 'gallery_image_update_as_feature' ] );
		add_action( 'wp_ajax_rtcl_fb_gallery_image_delete', [ $this, 'gallery_image_delete' ] );
		add_action( 'wp_ajax_rtcl_fb_gallery_image_update_order', [ $this, 'gallery_image_update_order' ] );
		add_action( 'wp_ajax_rtcl_fb_get_attachment_details', [ $this, 'get_attachment_details' ] );
		add_action( 'wp_ajax_rtcl_fb_update_attachment_details', [ $this, 'update_attachment_details' ] );
		add_action( 'wp_ajax_rtcl_fb_file_upload', [ $this, 'file_upload' ] );
		add_action( 'wp_ajax_rtcl_fb_file_delete', [ $this, 'file_delete' ] );

		add_action( 'wp_ajax_rtcl_fb_get_tags', [ $this, 'get_tags' ] );
		add_action( 'wp_ajax_rtcl_fb_add_new_tag', [ $this, 'add_new_tag' ] );

		add_action( 'wp_ajax_rtcl_update_listing', [ $this, 'update_listing' ] );
		add_action( 'wp_ajax_rtcl_fb_write_with_ai', [ $this, 'write_with_ai' ] );

		if ( ! is_user_logged_in() && Functions::is_enable_post_for_unregister() ) {
			add_action( 'wp_ajax_nopriv_rtcl_get_terms', [ $this, 'get_terms_callback' ] );
			add_action( 'wp_ajax_nopriv_rtcl_fb_get_category', [ $this, 'get_category' ] );
			add_action( 'wp_ajax_nopriv_rtcl_fb_get_location', [ $this, 'get_location' ] );

			add_action( 'wp_ajax_nopriv_rtcl_fb_gallery_image_upload', [ $this, 'gallery_image_upload' ] );
			add_action( 'wp_ajax_nopriv_rtcl_fb_gallery_image_update_as_feature', [
				$this,
				'gallery_image_update_as_feature',
			] );
			add_action( 'wp_ajax_nopriv_rtcl_fb_gallery_image_delete', [ $this, 'gallery_image_delete' ] );
			add_action( 'wp_ajax_nopriv_rtcl_fb_file_upload', [ $this, 'file_upload' ] );
			add_action( 'wp_ajax_nopriv_rtcl_fb_file_delete', [ $this, 'file_delete' ] );

			add_action( 'wp_ajax_nopriv_rtcl_update_listing', [ $this, 'update_listing' ] );

			add_action( 'wp_ajax_nopriv_rtcl_fb_write_with_ai', [ $this, 'write_with_ai' ] );

			add_action( 'wp_ajax_nopriv_rtcl_fb_get_tags', [ $this, 'get_tags' ] );
		}
	}

	/**
	 * Update listing
	 */
	public static function update_listing(): void {
		Functions::clear_notices();// Clear previous notice

		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session error !!', 'classified-listing' ) );

			return;
		}

		$isAdminEnd  = ! empty( $_POST['isAdminEnd'] );
		$postingType = 'new';
		$listing_id  = ! empty( $_POST['listingId'] ) ? absint( $_POST['listingId'] ) : 0;
		$listing     = null;
		if ( ( $listing_id && ( ! ( $listing = rtcl()->factory->get_listing( $listing_id ) ) || ( $isAdminEnd && ! current_user_can( 'edit_rtcl_listing', $listing_id ) ) || ( ! $isAdminEnd && ! Functions::current_user_can( 'edit_' . rtcl()->post_type, $listing_id ) ) ) ) || ( ! is_user_logged_in() && ! Functions::is_enable_post_for_unregister() ) ) {
			wp_send_json_error( apply_filters( 'rtcl_fb_not_found_error_message', __( 'You do not have sufficient permissions to access this page.', 'classified-listing' ), $_REQUEST, 'permission_error' ) );

			return;
		}

		if ( ! $listing || $listing->get_status() === 'rtcl-temp' ) {
			$form_id = ! empty( $_POST['formId'] ) ? absint( $_POST['formId'] ) : 0;
		} else {
			$form_id = absint( get_post_meta( $listing_id, '_rtcl_form_id', true ) );
		}

		if ( ( $isAdminEnd || ( $listing && ! $form_id ) ) && ! empty( $_POST['formId'] ) ) {
			$form_id = absint( $_POST['formId'] );
		}

		if ( empty( $form_id ) || ! $form = Form::query()->find( $form_id ) ) {
			wp_send_json_error( apply_filters( 'rtcl_fb_not_found_error_message', esc_html__( 'Form not found !!', 'classified-listing' ) ), $_REQUEST );

			return;
		}

		if ( ! empty( $_POST['formData'] ) ) {
			parse_str( $_POST['formData'], $formData );
		} else {
			$formData = [];
		}

		$sections = $form->sections;
		$fields   = $form->fields;
		if ( empty( $sections ) || empty( $fields ) ) {
			wp_send_json_error( apply_filters( 'rtcl_error_update_listing', __( 'Missing form field', 'classified-listing' ) ) );

			return;
		}
		$errors = FBHelper::formDataValidation( $formData, $form, $listing );

		if ( ! empty( $errors ) ) {
			wp_send_json_error( apply_filters( 'rtcl_error_validation_update_listing', [ 'errors' => $errors ], $formData, $sections ) );

			return;
		}

		$extraErrors = new WP_Error();

		$extraErrors = apply_filters( 'rtcl_fb_extra_form_validation', $extraErrors, $form );

		if ( $extraErrors instanceof WP_Error && $extraErrors->has_errors() ) {
			wp_send_json_error( apply_filters( 'rtcl_error_validation_update_listing', [ 'extraErrors' => $extraErrors->errors ], $formData, $sections ) );

			return;
		}

		// Data prepare
		$user_id             = get_current_user_id();
		$post_for_unregister = Functions::is_enable_post_for_unregister();
		if ( ! is_user_logged_in() && $post_for_unregister ) {
			if ( empty( $formData['email'] ) ) {
				wp_send_json_error( apply_filters( 'rtcl_error_update_listing', [ 'missing_required_email' => __( 'Missing required email to register user', 'classified-listing' ) ] ) );

				return;
			}
			$new_user_id = Functions::do_registration_from_listing_form( [ 'email' => $formData['email'] ] );
			if ( $new_user_id && is_numeric( $new_user_id ) ) {
				$user_id = $new_user_id;
				/* translators:  new account email*/
				Functions::add_notice( apply_filters( 'rtcl_listing_new_registration_success_message', sprintf( esc_html__( 'A new account is registered, password is sent to your email(%s).', 'classified-listing' ), esc_html( $formData['email'] ) ), $formData['email'] ) );
			}
			$message = Functions::get_notices( 'error' );
			if ( $message ) {
				wp_send_json_error( apply_filters( 'rtcl_error_update_listing', [ 'registration_error' => $message ] ) );

				return;
			}
		}

		$metaData           = [];
		$taxonomy           = [
			'category' => [],
			'location' => [],
		];
		$post_arg           = [];
		$new_listing_status = Functions::get_option_item( 'rtcl_general_settings', 'new_listing_status', 'pending' );
		if ( $listing ) {
			$post_arg['ID'] = $listing->get_id();
			if ( ( $listing->get_listing()->post_author > 0 && ( ( 'rtcl-temp' === $listing->get_listing()->post_status && $listing->get_listing()->post_author === get_current_user_id() ) || $listing->get_listing()->post_author == absint( apply_filters( 'rtcl_listing_post_user_id', get_current_user_id() ) ) ) ) || ( $listing->get_listing()->post_author == 0 && $post_for_unregister ) ) {
				if ( 'rtcl-temp' === $listing->get_listing()->post_status ) {
					$post_arg['post_status'] = $new_listing_status;
					$post_arg['post_author'] = $user_id;
				} else {
					$postingType       = 'update';
					$status_after_edit = Functions::get_option_item( 'rtcl_general_settings', 'edited_listing_status' );
					if ( 'publish' === $listing->get_listing()->post_status && $status_after_edit && $listing->get_listing()->post_status !== $status_after_edit ) {
						$post_arg['post_status'] = $status_after_edit;
					}
				}

				if ( $listing->get_listing()->post_author == 0 && $post_for_unregister ) {
					$post_arg['post_author'] = $user_id;
				}
			} else {
				if ( current_user_can( 'manage_options' ) && ! in_array( $listing->get_listing()->post_author, [ apply_filters( 'rtcl_listing_post_user_id', get_current_user_id() ), get_current_user_id() ] ) ) {
					$postingType = 'update';
				}
			}
		} else {
			$post_arg = [
				'post_status' => $new_listing_status,
				'post_author' => $user_id,
				'post_type'   => rtcl()->post_type,
			];
		}

		foreach ( $fields as $fieldId => $field ) {
			$name     = ! empty( $field['name'] ) ? $field['name'] : '';
			$element  = $field['element'];
			$rawValue = $formData[ $name ] ?? '';
			if ( isset( $field['preset'] ) && 1 == $field['preset'] ) {
				if ( 'title' === $element ) {
					if ( ! $isAdminEnd ) {
						$post_arg['post_title'] = FBHelper::sanitizeFieldValue( $rawValue, $field );
					}
				} elseif ( 'description' === $element ) {
					if ( ! $isAdminEnd ) {
						$post_arg['post_content'] = FBHelper::sanitizeFieldValue( $rawValue, $field );
					}
				} elseif ( 'listing_type' === $element ) {
					$metaData[] = [
						'name'  => 'ad_type',
						'field' => $field,
						'value' => $rawValue,
					];
				} elseif ( 'excerpt' === $element ) {
					$post_arg['post_excerpt'] = FBHelper::sanitizeFieldValue( $rawValue, $field );
				} elseif ( 'category' === $element ) {
					$taxonomy['category'] = is_array( $rawValue ) ? array_filter( array_map( function ( $tag ) {
						return ! empty( $tag['term_id'] ) ? absint( $tag['term_id'] ) : '';
					}, $rawValue ) ) : [];
				} elseif ( 'location' === $element ) {
					$taxonomy['location'] = is_array( $rawValue ) ? array_filter( array_map( function ( $tag ) {
						return ! empty( $tag['term_id'] ) ? absint( $tag['term_id'] ) : '';
					}, $rawValue ) ) : [];
				} elseif ( 'tag' === $element ) {
					$taxonomy['tag'] = is_array( $rawValue ) ? array_filter( array_map( function ( $tag ) {
						return ! empty( $tag['term_id'] ) ? absint( $tag['term_id'] ) : '';
					}, $rawValue ) ) : [];
				} elseif ( 'zipcode' === $element ) {
					$metaData[] = [
						'name'  => 'zipcode',
						'field' => $field,
						'value' => Functions::sanitize( $rawValue ),
					];
				} elseif ( 'view_count' === $element ) {
					$metaData[] = [
						'name'  => '_views',
						'field' => $field,
						'value' => Functions::sanitize( $rawValue ),
					];
				} elseif ( 'address' === $element ) {
					$metaData[] = [
						'name'  => 'address',
						'field' => $field,
						'value' => FBHelper::sanitizeFieldValue( $rawValue, $field ),
					];
				} elseif ( 'geo_location' === $element ) {
					$mapData    = $formData['map'] ?? [];
					$metaData[] = [
						'name'  => '_rtcl_geo_address',
						'field' => $field,
						'value' => Functions::sanitize( $rawValue ),
					];
					$metaData[] = [
						'name'  => 'latitude',
						'field' => $field,
						'value' => isset( $mapData['latitude'] ) ? Functions::sanitize( $mapData['latitude'] ) : '',
					];
					$metaData[] = [
						'name'  => 'longitude',
						'field' => $field,
						'value' => isset( $mapData['longitude'] ) ? Functions::sanitize( $mapData['longitude'] ) : '',
					];
				} elseif ( 'phone' === $element ) {
					$metaData[] = [
						'name'  => 'phone',
						'field' => $field,
						'value' => Functions::sanitize( $rawValue ),
					];
				} elseif ( 'whatsapp' === $element ) {
					$metaData[] = [
						'name'  => '_rtcl_whatsapp_number',
						'field' => $field,
						'value' => Functions::sanitize( $rawValue ),
					];
				} elseif ( 'email' === $element ) {
					$metaData[] = [
						'name'  => 'email',
						'field' => $field,
						'value' => Functions::sanitize( $rawValue, 'email' ),
					];
				} elseif ( 'website' === $element ) {
					$metaData[] = [
						'name'  => 'website',
						'field' => $field,
						'value' => FBHelper::sanitizeFieldValue( $rawValue, $field ),
					];
				} elseif ( 'social_profiles' === $element ) {
					$metaData[] = [
						'name'  => '_rtcl_social_profiles',
						'field' => $field,
						'value' => FBHelper::sanitizeFieldValue( $rawValue, $field ),
					];
				} elseif ( 'pricing' === $element ) {
					$pricing = $formData[ $name ];
					if ( ! empty( $field['options'] ) && in_array( 'pricing_type', $field['options'] ) && isset( $pricing['pricing_type'] ) ) {
						$pricing_type = in_array( $pricing['pricing_type'], array_keys( Options::get_listing_pricing_types() ) ) ? $pricing['pricing_type'] : 'price';
						$metaData[]   = [
							'name'  => '_rtcl_listing_pricing',
							'field' => $field,
							'value' => Functions::sanitize( $pricing_type ),
						];
						if ( 'range' === $pricing_type && isset( $pricing['max_price'] ) ) {
							$metaData[] = [
								'name'  => '_rtcl_max_price',
								'field' => $field,
								'value' => Functions::format_decimal( $pricing['max_price'] ),
							];
						}
					}

					if ( ! empty( $field['options'] ) && in_array( 'price_type', $field['options'] ) && isset( $pricing['price_type'] ) ) {
						$metaData[] = [
							'name'  => 'price_type',
							'field' => $field,
							'value' => Functions::sanitize( $pricing['price_type'] ),
						];
					}
					if ( ! empty( $field['options'] ) && in_array( 'price_unit', $field['options'] ) && isset( $pricing['price_unit'] ) ) {
						$metaData[] = [
							'name'  => '_rtcl_price_unit',
							'field' => $field,
							'value' => Functions::sanitize( $pricing['price_unit'] ),
						];
					}

					if ( isset( $pricing['price'] ) ) {
						$metaData[] = [
							'name'  => 'price',
							'field' => $field,
							'value' => Functions::format_decimal( $pricing['price'] ),
						];
					}
				} elseif ( 'map' === $element ) {
					$mapData    = $formData[ $name ];
					$metaData[] = [
						'name'  => 'latitude',
						'field' => $field,
						'value' => isset( $mapData['latitude'] ) ? Functions::sanitize( $mapData['latitude'] ) : '',
					];
					$metaData[] = [
						'name'  => 'longitude',
						'field' => $field,
						'value' => isset( $mapData['longitude'] ) ? Functions::sanitize( $mapData['longitude'] ) : '',
					];
					$metaData[] = [
						'name'  => 'hide_map',
						'field' => $field,
						'value' => ! empty( $mapData['hide_map'] ) ? 1 : null,
					];
				} elseif ( 'terms_and_condition' === $element ) {
					if ( isset( $formData[ $name ] ) ) {
						$metaData[] = [
							'name'  => 'rtcl_agree',
							'field' => $field,
							'value' => ! empty( $formData[ $name ] ) ? 1 : null,
						];
					}
				} elseif ( 'business_hours' === $element ) {
					$bshValues  = FBHelper::sanitizeFieldValue( $rawValue, $field );
					$metaData[] = [
						'name'  => '_rtcl_bhs',
						'field' => $field,
						'value' => $bshValues,
					];
				} elseif ( 'video_urls' === $element ) {
					$videoUrls  = FBHelper::sanitizeFieldValue( $rawValue, $field );
					$metaData[] = [
						'name'  => '_rtcl_video_urls',
						'field' => $field,
						'value' => $videoUrls,
					];
				} else {
					$sanitizedValue = FBHelper::sanitizeFieldValue( $rawValue, $field, $listing );
					$metaData[]     = [
						'name'  => $name,
						'field' => $field,
						'value' => $sanitizedValue,
					];
				}
			} else {
				if ( 'file' !== $element ) {
					$sanitizedValue    = FBHelper::sanitizeFieldValue( $rawValue, $field, $listing );
					$metaData[ $name ] = [
						'name'  => $name,
						'field' => $field,
						'value' => $sanitizedValue,
					];
				}
			}
		}

		if ( $listing ) {
			if ( 'rtcl-temp' === $listing->get_listing()->post_status && ! empty( $post_arg['post_title'] ) ) {
				$post_arg['post_name'] = $post_arg['post_title'];
			}
			$listingUpdate = wp_update_post( apply_filters( 'rtcl_listing_save_update_args', $post_arg, $postingType ) );
			if ( is_wp_error( $listingUpdate ) ) {
				wp_send_json_error( apply_filters( 'rtcl_error_update_listing', [ 'wp_update_post_error' => $listingUpdate->get_error_message() ] ) );

				return;
			}
		} else {
			$listing_id = wp_insert_post( apply_filters( 'rtcl_listing_save_update_args', $post_arg, $postingType ) );
			if ( is_wp_error( $listing_id ) ) {
				wp_send_json_error( apply_filters( 'rtcl_error_update_listing', [ 'wp_insert_post_error' => $listing_id->get_error_message() ] ) );

				return;
			}
		}

		$listing    = rtcl()->factory->get_listing( $listing_id );
		$listing_id = $listing->get_id();

		$metaData[] = [
			'name'  => '_rtcl_form_id',
			'value' => $form_id,
		];

		if ( ! empty( $taxonomy['category'] ) && ( $isAdminEnd || $postingType === 'new' || ( $listing && $postingType === 'update' && empty( $listing->get_categories() ) ) ) ) {
			wp_set_object_terms( $listing_id, $taxonomy['category'], rtcl()->category );
		}

		if ( ! empty( $taxonomy['location'] ) ) {
			wp_set_object_terms( $listing_id, $taxonomy['location'], rtcl()->location );
		}

		wp_set_object_terms( $listing_id, ! empty( $taxonomy['tag'] ) ? $taxonomy['tag'] : null, rtcl()->tag );

		$metaData = apply_filters( 'rtcl_fb_metadata_fields_before_save', $metaData, $postingType );
		/* meta data */
		if ( ! empty( $metaData ) ) {
			foreach ( $metaData as $metaItem ) {
				if ( ! empty( $metaItem['name'] ) ) {
					$metaItemName = $metaItem['name'];
					if ( ! $isAdminEnd && ( $postingType === 'update' && 'ad_type' === $metaItemName && $listing->get_ad_type() ) ) {
						continue;
					}
					$metaItemValue = $metaItem['value'];
					if ( ! empty( $metaItem['field'] ) ) {
						if ( $metaItem['field']['element'] === 'date' ) {
							if ( is_array( $metaItemValue ) && ! empty( $metaItemValue ) ) {
								foreach ( $metaItemValue as $key => $v ) {
									update_post_meta( $listing_id, $metaItemName . '_' . $key, $v );
								}
							} else {
								update_post_meta( $listing_id, $metaItemName, $metaItemValue );
							}
						} elseif ( $metaItem['field']['element'] === 'checkbox' ) {
							delete_post_meta( $listing_id, $metaItemName );
							if ( is_array( $metaItemValue ) && ! empty( $metaItemValue ) ) {
								foreach ( $metaItemValue as $val ) {
									if ( $val ) {
										add_post_meta( $listing_id, $metaItemName, $val );
									}
								}
							}
						} elseif ( $metaItem['field']['element'] === 'social_profiles' ) {
							if ( ! empty( $metaItemValue ) ) {
								update_post_meta( $listing->get_id(), '_rtcl_social_profiles', $metaItemValue );
							} else {
								delete_post_meta( $listing->get_id(), '_rtcl_social_profiles' );
							}
						} else {
							if ( $metaItemValue === null ) {
								delete_post_meta( $listing_id, $metaItemName );
							} else {
								update_post_meta( $listing_id, $metaItemName, $metaItemValue );
							}
						}
					} else {
						update_post_meta( $listing_id, $metaItemName, $metaItemValue );
					}
				}
			}
		}

		if ( $postingType == 'new' ) {
			update_post_meta( $listing_id, '_views', 0 );
			$current_user_id = get_current_user_id();
			$ads             = absint( get_user_meta( $current_user_id, '_rtcl_ads', true ) );
			update_user_meta( $current_user_id, '_rtcl_ads', $ads + 1 );
			if ( 'publish' === $new_listing_status ) {
				Functions::add_default_expiry_date( $listing_id );
			}
			Functions::add_notice( apply_filters( 'rtcl_listing_success_message', esc_html__( "Thank you for submitting your ad!", "classified-listing" ), $listing_id, $postingType, $_REQUEST ) );
		} else {
			Functions::add_notice( apply_filters( 'rtcl_listing_success_message', esc_html__( "Successfully updated !!!", "classified-listing" ), $listing_id, $postingType, $_REQUEST ) );
		}

		//Issue: Listing new metadata missing at the hook
		//Fixed: Load listing metadata to the listing objects before sending to hook
		$listing = $listing ? rtcl()->factory->get_listing( $listing->get_id() ) : null;

		do_action( 'rtcl_listing_form_after_save_or_update', $listing, $postingType, end( $taxonomy['category'] ), $new_listing_status, [
			'data'  => $_REQUEST,
			'files' => $_FILES,
		] );

		$errorMessage = Functions::get_notices( 'error' );
		if ( $errorMessage ) {
			wp_send_json_error( apply_filters( 'rtcl_error_update_listing', [ 'common_error' => $errorMessage ] ) );

			return;
		}
		$message = Functions::get_notices( 'success' );
		Functions::clear_notices(); // Clear all notice created by checkin

		wp_send_json_success( apply_filters( 'rtcl_listing_form_after_save_or_update_responses', [
			'message'      => $message,
			'post_id'      => $listing_id,
			'listing_id'   => $listing_id,
			'posting_type' => $postingType,
			'redirect_url' => apply_filters(
				'rtcl_listing_form_after_update_responses_redirect_url',
				Functions::get_listing_redirect_url_after_edit_post( $postingType, $listing_id, true ),
				$postingType,
				$listing_id,
				true,
				$message,
			),
		] ) );
	}

	public function gallery_image_delete() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}
		$listing_id = absint( Functions::request( "listingId" ) );
		$listing    = rtcl()->factory->get_listing( $listing_id );

		if ( $listing && ! Functions::current_user_can( 'edit_' . rtcl()->post_type, $listing_id ) ) {
			wp_send_json_error( apply_filters( 'rtcl_fb_not_found_error_message', __( 'You do not have sufficient permissions to access this page.', 'classified-listing' ), $_REQUEST, 'permission_error' ) );

			return;
		}

		$attach_id = isset( $_POST["attach_id"] ) ? absint( $_POST["attach_id"] ) : 0;
		$attach    = get_post( $attach_id );
		if ( ! $attach ) {
			wp_send_json_error( __( "Attachment does not exist.", "classified-listing" ) );

			return;
		}

		if ( $attach->post_parent != absint( Functions::request( "listingId" ) ) ) {
			wp_send_json_error( __( "Incorrect attachment ID.", "classified-listing" ) );

			return;
		}

		$featureImageRemoved = false;
		if ( get_post_thumbnail_id( $listing->get_id() ) === $attach_id ) {
			$featureImageRemoved = true;
		}

		if ( ! wp_delete_attachment( $attach_id ) ) {
			wp_send_json_error( __( "File could not be deleted.", "classified-listing" ) );

			return;
		}

		if ( $featureImageRemoved ) {
			$attachmentIds = get_children( [
				'post_parent'    => $listing->get_id(),
				'fields'         => 'ids',
				'post_type'      => 'attachment',
				'posts_per_page' => - 1,
				'post_status'    => 'inherit',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				                      'relation' => 'OR',
				                      [
					                      'key'     => '_rtcl_attachment_type',
					                      'value'   => 'image',
					                      'compare' => '=',
				                      ],
				                      [
					                      'key'     => '_rtcl_attachment_type',
					                      'compare' => 'NOT EXISTS',
				                      ],
				],
			] );
			if ( ! empty( $attachmentIds ) ) {
				set_post_thumbnail( $listing_id, $attachmentIds[0] );
			}
		}
		wp_send_json_success();
	}

	public function gallery_image_update_as_feature() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}

		$attach_id = isset( $_POST["attach_id"] ) ? absint( $_POST["attach_id"] ) : 0;
		$attach    = get_post( $attach_id );
		if ( ! $attach ) {
			wp_send_json_error( __( "Attachment does not exist.", "classified-listing" ) );

			return;
		}

		$listingId = absint( Functions::request( "listingId" ) );

		if ( ( ! $listingId || ! $listing = rtcl()->factory->get_listing( $listingId ) || ! Functions::current_user_can( 'edit_' . rtcl()->post_type, $listingId ) ) || ( ! is_user_logged_in() && ! Functions::is_enable_post_for_unregister() ) ) {
			wp_send_json_error( __( 'You do not have sufficient permissions.', 'classified-listing' ) );

			return;
		}

		if ( $attach->post_parent !== $listingId ) {
			wp_send_json_error( __( "Incorrect attachment ID.", "classified-listing" ) );

			return;
		}

		if ( get_post_thumbnail_id( $listingId ) === $attach_id ) {
			wp_send_json_error( __( "File is already as featured.", "classified-listing" ) );
		}

		if ( ! set_post_thumbnail( $listingId, $attach_id ) ) {
			wp_send_json_error( __( "Error while making feature.", "classified-listing" ) );
		}

		wp_send_json_success( __( "Image successfully featured.", "classified-listing" ) );
	}


	public function gallery_image_update_order() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}

		$listingId = intval( Functions::request( "listingId" ) );
		if ( ( ! $listingId || ! $listing = rtcl()->factory->get_listing( $listingId ) || ! Functions::current_user_can( 'edit_' . rtcl()->post_type, $listingId ) ) || ( ! is_user_logged_in() && ! Functions::is_enable_post_for_unregister() ) ) {
			wp_send_json_error( __( 'You do not have sufficient permissions to set.', 'classified-listing' ) );

			return;
		}
		$attachmentIds = ! empty( $_POST["attachmentIds"] ) && is_array( $_POST["attachmentIds"] ) ? array_filter( array_map( 'absint', $_POST["attachmentIds"] ) ) : [];
		if ( empty( $attachmentIds ) ) {
			wp_send_json_error( __( "Attachment ids not exist.", "classified-listing" ) );

			return;
		}
		foreach ( $attachmentIds as $index => $attachment_id ) {
			wp_update_post( [
				'ID'         => $attachment_id,
				'menu_order' => $index,
			] );
		}
		wp_send_json_success();
	}


	public function gallery_image_upload() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( "Unauthorized access!", "classified-listing" ) );
		}

		if ( ! is_user_logged_in() && apply_filters( 'rtcl_is_disable_post_for_unregister', true ) ) {
			wp_send_json_error( __( 'Registration required to upload listing image.', 'classified-listing' ) );
		}

		if ( empty( $_FILES['image'] ) ) {
			wp_send_json_error( esc_html__( "Given file is empty to upload.", "classified-listing" ) );
		}

		// The ID of the post this attachment is for.
		$parent_post_id = isset( $_POST["listingId"] ) ? absint( $_POST["listingId"] ) : 0;

		// Verify that the current user owns (or can edit) the target listing before accepting the upload.
		// Without this check any user could upload files onto an arbitrary listing by supplying its ID.
		if ( $parent_post_id > 0 ) {
			$parent_listing = rtcl()->factory->get_listing( $parent_post_id );
			if ( ! $parent_listing ) {
				wp_send_json_error( esc_html__( 'Invalid listing ID.', 'classified-listing' ) );
			}

			$parent_post   = $parent_listing->get_listing();
			$post_author   = (int) $parent_post->post_author;
			$is_temp_guest = ( 'rtcl-temp' === $parent_post->post_status && 0 === $post_author && Functions::is_enable_post_for_unregister() );

			if ( ! $is_temp_guest && ! Functions::current_user_can( 'edit_' . rtcl()->post_type, $parent_post_id ) ) {
				wp_send_json_error( apply_filters( 'rtcl_fb_not_found_error_message', __( 'You do not have sufficient permissions to access this page.', 'classified-listing' ), $_REQUEST, 'permission_error' ) );
			}
		}

		Filters::beforeUpload();
		// you can use WP's wp_handle_upload() function:
		$status = wp_handle_upload( $_FILES['image'], [ 'test_form' => false ] );

		// Get the path to the upload directory.
		$wp_upload_dir = wp_upload_dir();

		if ( isset( $status['error'] ) ) {
			Filters::afterUpload();
			wp_send_json_error( $status['error'] );
		}

		// $filename should be the path to a file in the upload directory.
		$filename = $status['file'];

		// Check the type of tile. We'll use this as the 'post_mime_type'.
		$filetype = wp_check_filetype( basename( $filename ) );


		// Prepare an array of post data for the attachment.
		$attachment = [
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'meta_input'     => [
				'_rtcl_attachment_type' => 'image',
			],
		];

		// Create post if does not exist
		if ( $parent_post_id < 1 ) {
			$oldAttachmentCount = [];
			add_filter( "post_type_link", "__return_empty_string" );

			$parent_post_id = wp_insert_post( apply_filters( "rtcl_insert_temp_post_for_image", [
				'post_title'      => __( 'RTCL Auto Temp', "classified-listing" ),
				'post_content'    => '',
				'post_status'     => Functions::get_temp_listing_status(),
				'post_author'     => wp_get_current_user()->ID,
				'post_type'       => rtcl()->post_type,
				'comments_status' => 'closed',
			] ) );

			remove_filter( "post_type_link", "__return_empty_string" );
		} else {
			$oldAttachmentIds = get_children( [
				'post_parent'    => $parent_post_id,
				'fields'         => 'ids',
				'post_type'      => 'attachment',
				'posts_per_page' => - 1,
				'post_status'    => 'inherit',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				                      'relation' => 'OR',
				                      [
					                      'key'     => '_rtcl_attachment_type',
					                      'value'   => 'image',
					                      'compare' => '=',
				                      ],
				                      [
					                      'key'     => '_rtcl_attachment_type',
					                      'compare' => 'NOT EXISTS',
				                      ],
				],
			] );
		}

		// Insert the attachment.
		$attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
		if ( ! is_wp_error( $attach_id ) ) {
			wp_update_attachment_metadata( $attach_id, Functions::generate_attachment_metadata( $attach_id, $filename, Functions::get_image_sizes() ) );
			if ( ! has_post_thumbnail( $parent_post_id ) ) {
				set_post_thumbnail( $parent_post_id, $attach_id );
			}
		}

		Filters::afterUpload();

		if ( ! empty( $oldAttachmentIds ) ) {
			$oldAttachmentIds[] = $attach_id;
			foreach ( $oldAttachmentIds as $index => $attachment_id ) {
				wp_update_post( [
					'ID'         => $attachment_id,
					'menu_order' => $index,
				] );
			}
		}
		wp_send_json_success( Functions::upload_item_data( $attach_id ) );
	}

	public function get_attachment_details() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session error !!', 'classified-listing' ) );

			return;
		}

		$attachment_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$attachment    = get_post( $attachment_id );
		if ( ! $attachment ) {
			wp_send_json_error( __( 'Attachment does not exist.', 'classified-listing' ) );

			return;
		}

		$listingId = absint( Functions::request( 'listingId' ) );

		if ( $attachment->post_parent !== $listingId ) {
			wp_send_json_error( __( 'Incorrect attachment ID.', 'classified-listing' ) );

			return;
		}

		if ( ! Functions::current_user_can( 'edit_rtcl_listing', $listingId ) ) {
			wp_send_json_error( __( 'Unauthorized access', 'classified-listing' ) );

			return;
		}

		$attachmentUrl = wp_get_attachment_url( $attachment->ID );
		$file          = $attachment->to_array();
		$file['guid']  = $attachmentUrl;
		$file['meta']  = wp_get_attachment_metadata( $attachment->ID );
		wp_send_json_success( $file );
	}

	public function update_attachment_details() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session error !!', 'classified-listing' ) );

			return;
		}

		$attachment_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$attachment    = get_post( $attachment_id );
		if ( ! $attachment ) {
			wp_send_json_error( __( 'Attachment does not exist.', 'classified-listing' ) );

			return;
		}

		$listingId = absint( Functions::request( 'listingId' ) );

		if ( $attachment->post_parent !== $listingId ) {
			wp_send_json_error( __( 'Incorrect attachment ID.', 'classified-listing' ) );

			return;
		}

		if ( ! Functions::current_user_can( 'edit_rtcl_listing', $listingId ) ) {
			wp_send_json_error( __( 'Unauthorized access', 'classified-listing' ) );

			return;
		}

		$updatedId = wp_update_post( [
			'ID'           => $attachment->ID,
			'post_excerpt' => ! empty( $_POST['data']['caption'] ) ? trim( sanitize_text_field( $_POST['data']['caption'] ) ) : '',
			'post_content' => ! empty( $_POST['data']['content'] ) ? trim( sanitize_text_field( $_POST['data']['content'] ) ) : '',
		] );

		if ( is_wp_error( $updatedId ) ) {
			wp_send_json_error( $updatedId->get_error_message() );

			return;
		}
		$attachment         = get_post( $updatedId, ARRAY_A );
		$attachment['meta'] = wp_get_attachment_metadata( $updatedId );
		wp_send_json_success( $attachment );
	}

	public function file_upload() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session error !!', 'classified-listing' ) );

			return;
		}

		if ( ! is_user_logged_in() && apply_filters( 'rtcl_is_disable_post_for_unregister', true ) ) {
			wp_send_json_error( __( 'Registration required to upload listing file.', 'classified-listing' ) );
		}

		if ( empty( $_FILES['file'] ) ) {
			wp_send_json_error( esc_html__( 'Given file is empty to upload.', 'classified-listing' ) );

			return;
		}

		$form_id = ! empty( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		if ( empty( $form_id ) ) {
			wp_send_json_error( esc_html__( 'From id is empty to upload.', 'classified-listing' ) );

			return;
		}

		$repeater_uuid = Functions::request( 'repeater_uuid' );
		$field_uuid    = Functions::request( 'field_uuid' );
		$repeaterIndex = Functions::request( 'repeater_index' );
		$repeater      = null;
		$field         = null;
		if ( $repeater_uuid ) {
			if ( empty( $repeater_uuid ) || empty( $field_uuid ) ) {
				wp_send_json_error( esc_html__( 'Field id is empty to upload.', 'classified-listing' ) );

				return;
			}
			$repeater = FBHelper::getFormFieldByUuid( $repeater_uuid, '', $form_id );

			if ( empty( $repeater ) || empty( $repeater['element'] ) || 'repeater' !== $repeater['element'] || empty( $repeater['name'] ) ) {
				wp_send_json_error( esc_html__( 'No field found to upload.', 'classified-listing' ) );

				return;
			}

			if ( ! empty( $repeater['fields'] ) ) {
				foreach ( $repeater['fields'] as $_field ) {
					if ( ! empty( $_field['uuid'] ) && $_field['uuid'] === $field_uuid ) {
						$field = $_field;
						break;
					}
				}
			}
			if ( $field ) {
				$repeaterIndex = null !== $repeaterIndex ? absint( $repeaterIndex ) : null;
				if ( null === $repeaterIndex ) {
					wp_send_json_error( esc_html__( 'Repeater field index is not defined.', 'classified-listing' ) );

					return;
				}
			}
		} else {
			if ( empty( $field_uuid ) ) {
				wp_send_json_error( esc_html__( 'Field id is empty to upload.', 'classified-listing' ) );

				return;
			}

			$field = FBHelper::getFormFieldByUuid( $field_uuid, '', $form_id );
		}

		if ( empty( $field ) || empty( $field['element'] ) || 'file' !== $field['element'] ) {
			wp_send_json_error( esc_html__( 'No field found to upload.', 'classified-listing' ) );

			return;
		}

		$fileMetaKey = ! empty( $field['name'] ) ? $field['name'] : null;

		if ( empty( $fileMetaKey ) ) {
			wp_send_json_error( esc_html__( 'Field name is empty.', 'classified-listing' ) );

			return;
		}

		// The ID of the post this attachment is for.
		$listing_id = isset( $_POST['listingId'] ) ? absint( $_POST['listingId'] ) : 0;

		if ( $listing_id > 0 ) {
			$parent_listing = rtcl()->factory->get_listing( $listing_id );
			if ( ! $parent_listing ) {
				wp_send_json_error( esc_html__( 'Invalid listing ID.', 'classified-listing' ) );

				return;
			}

			$parent_post   = $parent_listing->get_listing();
			$post_author   = (int) $parent_post->post_author;
			$is_temp_guest = ( 'rtcl-temp' === $parent_post->post_status && 0 === $post_author && Functions::is_enable_post_for_unregister() );

			if ( ! $is_temp_guest && ! Functions::current_user_can( 'edit_' . rtcl()->post_type, $listing_id ) ) {
				wp_send_json_error( apply_filters( 'rtcl_fb_not_found_error_message', __( 'You do not have sufficient permissions to access this page.', 'classified-listing' ), $_REQUEST, 'permission_error' ) );

				return;
			}
		}

		if ( $listing_id ) {
			if ( $repeater ) {
				$repeaterValue = get_post_meta( $listing_id, $repeater['name'], true );
				$repeaterValue = ! is_array( $repeaterValue ) || empty( $repeaterValue ) ? [] : $repeaterValue;
				if ( ! empty( $repeaterValue[ $repeaterIndex ][ $fileMetaKey ] ) && is_array( $repeaterValue[ $repeaterIndex ][ $fileMetaKey ] ) ) {
					$attachment_ids = array_map( 'absint', $repeaterValue[ $repeaterIndex ][ $fileMetaKey ] );
				} else {
					$attachment_ids = [];
				}
			} else {
				$attachment_ids = get_post_meta( $listing_id, $fileMetaKey, true );
			}

			$attachment_ids = ! empty( $attachment_ids ) && is_array( $attachment_ids ) ? $attachment_ids : [];
			if ( ! empty( $attachment_ids ) ) {
				$check_attachment_ids = get_children( [
					'fields'         => 'ids',
					'post_parent'    => $listing_id,
					'post_type'      => 'attachment',
					'post__in'       => $attachment_ids,
					'orderby'        => 'post__in',
					'posts_per_page' => - 1,
					'post_status'    => 'inherit',
					'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					                      [
						                      'key'     => '_rtcl_attachment_type',
						                      'value'   => 'file',
						                      'compare' => '=',
					                      ],
					],
				] );
				if ( $attachment_ids !== $check_attachment_ids ) {
					if ( $repeater ) {
						if ( ! empty( $repeaterValue ) && is_array( $repeaterValue ) ) {
							$repeaterValue[ $repeaterIndex ][ $fileMetaKey ] = $check_attachment_ids;
							update_post_meta( $listing_id, $repeater['name'], $repeaterValue );
						}
					} else {
						update_post_meta( $listing_id, $fileMetaKey, $check_attachment_ids );
					}
					$attachment_ids = $check_attachment_ids;
				}
			}
		} else {
			$attachment_ids = [];
		}

		if ( ! empty( $field['validation']['max_file_count']['value'] ) ) {
			$maxFileCount = absint( $field['validation']['max_file_count']['value'] );
			if ( $maxFileCount && count( $attachment_ids ) >= $maxFileCount ) {
				$message = ! empty( $field['validation']['max_file_count']['message'] ) ? str_replace( '{value}', $maxFileCount, $field['validation']['max_file_count']['message'] ) : esc_html__( 'Your file upload limit is over.', 'classified-listing' );
				wp_send_json_error( $message );

				return;
			}
		}
		$file_cl_location = empty( $field['file_location'] ) || 'default' === $field['file_location'];
		if ( $file_cl_location ) {
			Filters::beforeUpload();
		}

		// you can use WP\'s wp_handle_upload() function:
		$status = wp_handle_upload( $_FILES['file'], [ 'test_form' => false ] );

		// Get the path to the upload directory.
		$wp_upload_dir = wp_upload_dir();

		if ( isset( $status['error'] ) ) {
			if ( $file_cl_location ) {
				Filters::afterUpload();
			}
			wp_send_json_error( $status['error'] );

			return;
		}

		// $filename should be the path to a file in the upload directory.
		$filename = $status['file'];

		// Check the type of tile. We'll use this as the 'post_mime_type'.
		$filetype = wp_check_filetype( basename( $filename ) );

		// Prepare an array of post data for the attachment.
		$attachment = [
			'guid'            => $wp_upload_dir['url'] . '/' . basename( $filename ),
			'post_mime_type'  => $filetype['type'],
			'post_title'      => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'    => '',
			'post_status'     => 'inherit',
			'comments_status' => 'closed',
			'meta_input'      => [
				'_rtcl_attachment_type' => 'file',
			],
		];

		// Create post if does not exist
		if ( $listing_id < 1 ) {
			add_filter( 'post_type_link', '__return_empty_string' );

			$listing_id = wp_insert_post( apply_filters( 'rtcl_insert_temp_post_for_file', [
				'post_title'      => __( 'RTCL Auto Temp', 'classified-listing' ),
				'post_content'    => '',
				'post_status'     => Functions::get_temp_listing_status(),
				'post_author'     => wp_get_current_user()->ID,
				'post_type'       => rtcl()->post_type,
				'comments_status' => 'closed',
			] ) );

			remove_filter( 'post_type_link', '__return_empty_string' );
		}

		// Insert the attachment.
		$attach_id = wp_insert_attachment( $attachment, $filename, $listing_id );
		if ( ! is_wp_error( $attach_id ) ) {
			wp_update_attachment_metadata( $attach_id, Functions::generate_attachment_metadata( $attach_id, $filename, Functions::get_default_image_sizes() ) );
		}

		$attachment_ids[] = $attach_id;
		if ( $repeater ) {
			$repeaterValue[ $repeaterIndex ][ $fileMetaKey ] = $attachment_ids;
			update_post_meta( $listing_id, $repeater['name'], $repeaterValue );
		} else {
			update_post_meta( $listing_id, $fileMetaKey, $attachment_ids );
		}

		Filters::afterUpload();

		wp_send_json_success( FBHelper::getAttachmentFile( $attach_id ) );
	}

	public function file_delete() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session error !!', 'classified-listing' ) );

			return;
		}

		$form_id = ! empty( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		if ( empty( $form_id ) ) {
			wp_send_json_error( esc_html__( 'From id is empty to delete.', 'classified-listing' ) );

			return;
		}
		$repeater_uuid = Functions::request( 'repeater_uuid' );
		$field_uuid    = Functions::request( 'field_uuid' );
		$repeaterIndex = Functions::request( 'repeater_index' );
		$repeater      = null;
		$field         = null;

		if ( $repeater_uuid ) {
			if ( empty( $repeater_uuid ) || empty( $field_uuid ) ) {
				wp_send_json_error( esc_html__( 'Field id is empty to delete.', 'classified-listing' ) );

				return;
			}
			$repeater = FBHelper::getFormFieldByUuid( $repeater_uuid, '', $form_id );

			if ( empty( $repeater ) || empty( $repeater['element'] ) || 'repeater' !== $repeater['element'] || empty( $repeater['name'] ) ) {
				wp_send_json_error( esc_html__( 'No field found to delete.', 'classified-listing' ) );

				return;
			}

			if ( ! empty( $repeater['fields'] ) ) {
				foreach ( $repeater['fields'] as $_field ) {
					if ( ! empty( $_field['uuid'] ) && $_field['uuid'] === $field_uuid ) {
						$field = $_field;
						break;
					}
				}
			}
			if ( $field ) {
				$repeaterIndex = null !== $repeaterIndex ? absint( $repeaterIndex ) : null;
				if ( null === $repeaterIndex ) {
					wp_send_json_error( esc_html__( 'Repeater field index is not defined.', 'classified-listing' ) );

					return;
				}
			}
		} else {
			if ( empty( $field_uuid ) ) {
				wp_send_json_error( esc_html__( 'Field id is empty to delete.', 'classified-listing' ) );

				return;
			}

			$field = FBHelper::getFormFieldByUuid( $field_uuid, '', $form_id );
		}

		if ( empty( $field ) || empty( $field['element'] ) || 'file' !== $field['element'] ) {
			wp_send_json_error( esc_html__( 'No field found to delete.', 'classified-listing' ) );

			return;
		}

		$fileMetaKey = ! empty( $field['name'] ) ? $field['name'] : null;

		if ( empty( $fileMetaKey ) ) {
			wp_send_json_error( esc_html__( 'Field name is empty.', 'classified-listing' ) );

			return;
		}

		$attach_id = isset( $_POST['attach_id'] ) ? absint( $_POST['attach_id'] ) : 0;
		$attach    = get_post( $attach_id );
		if ( ! $attach ) {
			wp_send_json_error( __( 'Attachment does not exist.', 'classified-listing' ) );

			return;
		}
		$listing_id = absint( Functions::request( "listingId" ) );
		$listing    = rtcl()->factory->get_listing( $listing_id );

		if ( $listing && ! Functions::current_user_can( 'edit_' . rtcl()->post_type, $listing_id ) ) {
			wp_send_json_error( apply_filters( 'rtcl_fb_not_found_error_message', __( 'You do not have sufficient permissions to access this page.', 'classified-listing' ), $_REQUEST, 'permission_error' ) );

			return;
		}

		if ( $attach->post_parent != $listing_id ) {
			wp_send_json_error( __( 'Incorrect attachment ID.', 'classified-listing' ) );

			return;
		}

		if ( $repeater ) {
			$repeaterValue = get_post_meta( $listing_id, $repeater['name'], true );
			$repeaterValue = ! is_array( $repeaterValue ) || empty( $repeaterValue ) ? [] : $repeaterValue;
			if ( ! empty( $repeaterValue[ $repeaterIndex ][ $fileMetaKey ] ) && is_array( $repeaterValue[ $repeaterIndex ][ $fileMetaKey ] ) ) {
				$attachment_ids = array_map( 'absint', $repeaterValue[ $repeaterIndex ][ $fileMetaKey ] );
			} else {
				$attachment_ids = [];
			}
		} else {
			$attachment_ids = get_post_meta( $listing_id, $fileMetaKey, true );
		}
		$attachment_ids = ! empty( $attachment_ids ) && is_array( $attachment_ids ) ? $attachment_ids : [];
		if ( ! empty( $attachment_ids ) ) {
			$check_attachment_ids = get_children( [
				'fields'         => 'ids',
				'post_parent'    => $listing_id,
				'post_type'      => 'attachment',
				'post__in'       => $attachment_ids,
				'orderby'        => 'post__in',
				'posts_per_page' => - 1,
				'post_status'    => 'inherit',
				'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				                      [
					                      'key'     => '_rtcl_attachment_type',
					                      'value'   => 'file',
					                      'compare' => '=',
				                      ],
				],
			] );
			if ( $attachment_ids !== $check_attachment_ids ) {
				if ( $repeater ) {
					if ( ! empty( $repeaterValue ) && is_array( $repeaterValue ) ) {
						$repeaterValue[ $repeaterIndex ][ $fileMetaKey ] = $check_attachment_ids;
						update_post_meta( $listing_id, $repeater['name'], $repeaterValue );
					}
				} else {
					update_post_meta( $listing_id, $fileMetaKey, $check_attachment_ids );
				}
				$attachment_ids = $check_attachment_ids;
			}
		}

		if ( empty( $attachment_ids ) || ! in_array( $attach->ID, $attachment_ids ) ) {
			wp_send_json_error( __( 'No file found to delete.', 'classified-listing' ) );

			return;
		}

		if ( ! wp_delete_attachment( $attach_id ) ) {
			wp_send_json_error( __( 'File could not be deleted.', 'classified-listing' ) );

			return;
		}

		$attachment_ids = array_filter( $attachment_ids,
			function ( $item_id ) use ( $attach ) {
				return $item_id !== $attach->ID;
			},
		);

		if ( $repeater ) {
			if ( ! empty( $repeaterValue ) && is_array( $repeaterValue ) ) {
				$repeaterValue[ $repeaterIndex ][ $fileMetaKey ] = $attachment_ids;
				update_post_meta( $listing_id, $repeater['name'], $repeaterValue );
			}
		} else {
			update_post_meta( $listing_id, $fileMetaKey, $attachment_ids );
		}

		wp_send_json_success();
	}

	public function get_category(): void {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session error !!', 'classified-listing' ) );

			return;
		}

		$parent_id  = ! empty( $_POST['parentId'] ) ? absint( $_POST['parentId'] ) : 0;
		$excludeIds = ! empty( $_POST['exclude'] ) && is_array( $_POST['exclude'] ) ? array_map( 'absint', $_POST['exclude'] ) : [];
		$includeIds = ! empty( $_POST['include'] ) && is_array( $_POST['include'] ) ? array_map( 'absint', $_POST['include'] ) : [];

		$listingType = Functions::request( 'listingType' );
		$data        = [];
		if ( ! empty( $listingType ) ) {
			$data['type'] = $listingType;
		}
		if ( ! empty( $excludeIds ) ) {
			$data['exclude'] = $excludeIds;
		}
		if ( ! empty( $includeIds ) ) {
			$data['include'] = $includeIds;
		}
		$categories = Functions::get_sub_terms( rtcl()->category, $parent_id, $data );
		$data       = [
			'success' => true,
			'message' => [],
			'cat_id'  => $parent_id,
		];
		$response   = apply_filters( 'rtcl_ajax_category_selection_before_post', $data );
		if ( empty( $response['success'] ) && ! empty( $response['message'] ) ) {
			wp_send_json_error( $response['message'][0] );

			return;
		}

		wp_send_json_success( [
			'data' => $categories,
		] );
	}

	public function get_filtered_categories(): void {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}

		$ids         = ! empty( $_POST['ids'] ) && is_array( $_POST['ids'] ) ? array_map( 'absint', $_POST['ids'] ) : [];
		$parentId    = isset( $_POST['parentId'] ) ? ( $_POST['parentId'] == 0 ? 0 : absint( $_POST['parentId'] ) ) : '';
		$excludeIds  = ! empty( $_POST['excludeIds'] ) && is_array( $_POST['excludeIds'] ) ? array_map( 'absint', $_POST['excludeIds'] ) : [];
		$q           = ! empty( $_POST['q'] ) ? sanitize_text_field( $_POST['q'] ) : '';
		$orderby     = strtolower( Functions::get_option_item( 'rtcl_archive_listing_settings', 'taxonomy_orderby', 'name' ) );
		$order       = strtoupper( Functions::get_option_item( 'rtcl_archive_listing_settings', 'taxonomy_order', 'DESC' ) );
		$number      = isset( $_POST['number'] ) ? absint( $_POST['number'] ) : false;
		$args        = [
			'hide_empty'   => false,
			'orderby'      => $orderby,
			'order'        => ( 'DESC' === $order ) ? 'DESC' : 'ASC',
			'taxonomy'     => rtcl()->category,
			'pad_counts'   => 1,
			'hierarchical' => 1,
			'parent'       => $parentId,
			'search'       => $q,
			'include'      => $ids,
			'exclude'      => $excludeIds,
			'number'       => $number,
			// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		];
		$listingType = Functions::request( 'listingType' );
		if ( $listingType ) {
			$args['meta_query'] = [
				[
					'key'   => '_rtcl_types',
					'value' => $listingType,
				],
			];
		}
		$data       = [];
		$categories = get_terms( $args );
		if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
			foreach ( $categories as $term ) {
				$image_id = get_term_meta( $term->term_id, '_rtcl_image', true );
				if ( $image_id ) {
					$image_attributes = wp_get_attachment_image_src( (int) $image_id, 'medium' );
					if ( ! empty( $image_attributes[0] ) ) {
						$term->img_url = $image_attributes[0];
					}
				}
				$icon_id = get_term_meta( $term->term_id, '_rtcl_icon', true );
				if ( $icon_id ) {
					$term->icon = $icon_id;
				}
				$data[] = $term;
			}
		}
		wp_send_json_success( [
			'data' => $data,
		] );
	}


	public function get_terms_callback() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}
		$includeIds = ! empty( $_POST['includeIds'] ) && is_array( $_POST['includeIds'] ) ? array_map( 'absint', $_POST['includeIds'] ) : [];
		if ( empty( $includeIds ) && ! empty( $_POST['ids'] ) ) {
			$includeIds = is_array( $_POST['ids'] ) ? array_map( 'absint', $_POST['ids'] ) : [];
		}
		$parentId        = isset( $_POST['parentId'] ) ? ( $_POST['parentId'] == 0 ? 0 : absint( $_POST['parentId'] ) ) : '';
		$excludeIds      = ! empty( $_POST['excludeIds'] ) && is_array( $_POST['excludeIds'] ) ? array_map( 'absint', $_POST['excludeIds'] ) : [];
		$q               = ! empty( $_POST['q'] ) ? sanitize_text_field( $_POST['q'] ) : '';
		$withHasChildren = ! empty( $_POST['withHasChildren'] );
		$orderby         = strtolower( Functions::get_option_item( 'rtcl_archive_listing_settings', 'taxonomy_orderby', 'name' ) );
		$order           = strtoupper( Functions::get_option_item( 'rtcl_archive_listing_settings', 'taxonomy_order', 'DESC' ) );
		$taxonomy        = isset( $_POST['taxonomy'] )
		                   && in_array( $_POST['taxonomy'], [
			rtcl()->tag,
			rtcl()->category,
			rtcl()->location,
		] ) ? $_POST['taxonomy'] : rtcl()->category;
		$number          = isset( $_POST['number'] ) ? absint( $_POST['number'] ) : 0;
		if ( $parentId === '' && $q ) {
			if ( ! empty( $excludeIds ) ) {
				$excludeIds = Functions::get_all_term_descendants( $excludeIds, $taxonomy );
			}
			if ( ! empty( $includeIds ) ) {
				$includeIds = Functions::get_all_term_descendants( $includeIds, $taxonomy );
			}
		}
		$args = [
			'hide_empty'   => false,
			'orderby'      => $orderby,
			'order'        => ( 'DESC' === $order ) ? 'DESC' : 'ASC',
			'taxonomy'     => $taxonomy,
			'pad_counts'   => 1,
			'hierarchical' => 1,
			'parent'       => $parentId,
			'search'       => $q,
			'include'      => $includeIds,
			'exclude'      => $excludeIds,
			'number'       => $number,
			// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		];
		if ( $args['orderby'] == '_rtcl_order' ) {
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = '_rtcl_order';
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		}
		$listingType = Functions::request( 'listingType' );
		if ( rtcl()->category === $taxonomy && $listingType ) {
			$args['meta_query'] = [
				[
					'key'   => '_rtcl_types',
					'value' => $listingType,
				],
			];
		}

		$data  = [];
		$terms = get_terms( $args );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			if ( rtcl()->category === $taxonomy || $withHasChildren ) {
				$data = array_map( function ( $term ) use ( $withHasChildren ) {
					if ( rtcl()->category === $term->taxonomy ) {
						$image_id = get_term_meta( $term->term_id, '_rtcl_image', true );
						if ( $image_id ) {
							$image_attributes = wp_get_attachment_image_src( (int) $image_id, 'medium' );
							if ( ! empty( $image_attributes[0] ) ) {
								$term->img_url = $image_attributes[0];
							}
						}
						$icon_id = get_term_meta( $term->term_id, '_rtcl_icon', true );
						if ( $icon_id ) {
							$term->icon = $icon_id;
						}
					}
					if ( $withHasChildren ) {
						$children           = get_terms( [
							'taxonomy'   => $term->taxonomy,
							'hide_empty' => false,
							'parent'     => $term->term_id,
							'number'     => 1,
						] );
						$term->has_children = ! empty( $children );
					}

					return $term;
				}, $terms );
			} else {
				$data = $terms;
			}
		}
		wp_send_json_success( [
			'data' => $data,
		] );
	}

	public function get_tags(): void {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}

		$ids        = ! empty( $_POST['ids'] ) && is_array( $_POST['ids'] ) ? array_map( 'absint', $_POST['ids'] ) : [];
		$excludeIds = ! empty( $_POST['excludeIds'] ) && is_array( $_POST['excludeIds'] ) ? array_map( 'absint', $_POST['excludeIds'] ) : [];
		$q          = ! empty( $_POST['q'] ) ? sanitize_text_field( $_POST['q'] ) : '';
		$orderby    = strtolower( Functions::get_option_item( 'rtcl_archive_listing_settings', 'taxonomy_orderby', 'name' ) );
		$order      = strtoupper( Functions::get_option_item( 'rtcl_archive_listing_settings', 'taxonomy_order', 'DESC' ) );
		$args       = [
			'hide_empty' => false,
			'orderby'    => $orderby,
			'order'      => ( 'DESC' === $order ) ? 'DESC' : 'ASC',
			'taxonomy'   => rtcl()->tag,
			'pad_counts' => 1,
			'search'     => $q,
			'include'    => $ids,
			'exclude'    => $excludeIds, // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		];
		$data       = [];
		$tags       = get_terms( $args );
		if ( ! is_wp_error( $tags ) ) {
			$data = $tags;
		}
		wp_send_json_success( [
			'data' => $data,
		] );
	}

	public function add_new_tag(): void {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}

		$listing_id = isset( $_POST['listingId'] ) ? absint( $_POST['listingId'] ) : 0;
		
		if ( ! current_user_can( 'manage_categories' ) && ! apply_filters( 'rtcl_user_can_create_tag', false, $listing_id ) ) {
			wp_send_json_error( __( "You don't have permission to create tags.", 'classified-listing' ) );

			return;
		}

		$tagName = ! empty( $_POST['tag_name'] ) ? sanitize_text_field( $_POST['tag_name'] ) : '';
		if ( empty( $tagName ) ) {
			wp_send_json_error( __( 'Tag name is required', 'classified-listing' ) );

			return;
		}

		// Enforce a sane length limit.
		$maxLength = apply_filters( 'rtcl_tag_name_max_length', 64 );
		if ( mb_strlen( $tagName ) > $maxLength ) {
			wp_send_json_error(
			/* translators: %d: maximum allowed characters */
				sprintf( __( 'Tag name must not exceed %d characters.', 'classified-listing' ), $maxLength ),
			);

			return;
		}

		// Prevent duplicate tags – return the existing term instead of creating a new one.
		$existingTerm = term_exists( $tagName, rtcl()->tag );
		if ( $existingTerm ) {
			$term = get_term( is_array( $existingTerm ) ? $existingTerm['term_id'] : $existingTerm, rtcl()->tag );
			if ( $term && ! is_wp_error( $term ) ) {
				wp_send_json_success( [
					'data' => $term,
				] );

				return;
			}
		}

		$newTag = wp_create_term( $tagName, rtcl()->tag );
		if ( is_wp_error( $newTag ) ) {
			wp_send_json_error( __( 'Error while creating new tag.', 'classified-listing' ) );

			return;
		}
		$term = get_term( $newTag['term_id'], rtcl()->tag );

		if ( ! $term || is_wp_error( $term ) ) {
			wp_send_json_error( __( 'Error while creating new tag.', 'classified-listing' ) );

			return;
		}

		wp_send_json_success( [
			'data' => $term,
		] );
	}

	public function get_location(): void {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}

		$parent_id = ! empty( $_POST['parentId'] ) ? absint( $_POST['parentId'] ) : 0;

		$locations = Functions::get_one_level_locations( $parent_id );
		wp_send_json_success( [
			'data' => $locations,
		] );
	}

	/**
	 * Handles the AI writing process by generating a response based on the provided prompt and system prompt.
	 *
	 * This method verifies the nonce to ensure the request is legitimate. It then retrieves the prompt and
	 * system prompt from the POST request, sanitizes the input, and uses the AI service to generate a response.
	 * If the response is successful, it returns the generated content in a JSON response. Otherwise,
	 * it returns an error message.
	 *
	 * @return void Sends a JSON response with either the generated content or an error message.
	 *
	 * @throws Exception If the AI service fails or an error occurs during the response generation.
	 */
	public function write_with_ai() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session error!!', 'classified-listing' ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( __( 'Unauthorized access.', 'classified-listing' ) );
		}

		$prompt        = isset( $_POST['prompt'] ) ? sanitize_text_field( wp_unslash( $_POST['prompt'] ) ) : '';
		$system_prompt = isset( $_POST['systemPrompt'] ) ? sanitize_text_field( wp_unslash( $_POST['systemPrompt'] ) ) : '';
		try {
			$aiService = rtcl()->factory->initializeAIService();
			$response  = $aiService->generateResponse( $prompt, $system_prompt );
			if ( is_wp_error( $response ) ) {
				wp_send_json_error( $response->get_error_message() );
			}
			wp_send_json_success( [ 'response' => $response ] );
		} catch ( Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}


}
