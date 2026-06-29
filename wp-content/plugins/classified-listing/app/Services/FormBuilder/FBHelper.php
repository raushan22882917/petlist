<?php

namespace Rtcl\Services\FormBuilder;

use DateTime;
use InvalidArgumentException;
use Rtcl\Database\Eloquent\Model;
use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Str;
use Rtcl\Helpers\Utility;
use Rtcl\Models\Form\Form;
use Rtcl\Models\Listing;
use Rtcl\Resources\Options;

class FBHelper {

	/**
	 * @param $id
	 *
	 * @return mixed|Form|null
	 */
	public static function getFormById( $id ) {
		$form = $id ? Form::query()->find( $id ) : null;

		$_form = apply_filters( 'rtcl_fb_form', $form );

		if ( is_a( $_form, Form::class ) ) {
			return $_form;
		}

		return null;
	}

	/**
	 * @param Listing $listing
	 * @return bool
	 */
	public static function isEnableSingleBuilder( $listing ): bool {
		if ( !is_a( $listing, Listing::class ) ) {
			return false;
		}
		if ( ( $form = $listing->getForm() ) && ( $singleLayout = $form->getSingleLayout() ) && !empty( $singleLayout['settings']['active'] ) && !empty( $singleLayout['containers'] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * @param $slug
	 *
	 * @return mixed|Form|null
	 */
	public static function getFormBySlug( $slug ) {
		$form = $slug ? Form::query()->find( $slug, 'slug' ) : null;

		$_form = apply_filters( 'rtcl_fb_form', $form );

		if ( is_a( $_form, Form::class ) ) {
			return $_form;
		}

		return null;
	}


	/**
	 * @return bool
	 */
	public static function isEnabled(): bool {
		return !empty( self::getOption( 'active' ) );
	}

	/**
	 * @param Str $slug
	 * @param int $exceptFormId
	 *
	 * @return mixed|string
	 */
	public static function getUniqueSlug( $slug, int $exceptFormId = 0 ) {
		$tempSlug = sanitize_title( $slug );
		while ( !self::isUniqueSlug( $tempSlug, $exceptFormId ) ) {
			$forms = Form::query()->where( 'slug', 'LIKE', '%' . $tempSlug . '%' )->where( 'id', '!=', $exceptFormId )->get();
			if ( !empty( $forms ) ) {
				$tempSlug = $tempSlug . '-' . $forms->count();
			} else {
				$tempSlug = $tempSlug . '-1';
			}
		}

		return $tempSlug;
	}

	/**
	 * @param string $slug
	 * @param int $exceptFormId
	 *
	 * @return bool
	 */
	public static function isUniqueSlug( $slug, int $exceptFormId = 0 ): bool {
		$forms = Form::query()->where( 'slug', '=', $slug )->where( 'id', '!=', $exceptFormId )->one();

		return empty( $forms );
	}

	public static function isUniqueTitle( $title, int $exceptFormId = 0 ): bool {
		$form = Form::query()->where( 'title', '=', $title )->where( 'id', '!=', $exceptFormId )->one();

		return empty( $form );
	}

	public static function getFileFieldData( $listing_id, $meta_key ) {

		$attachmentIds = get_post_meta( $listing_id, $meta_key, true );
		$attachmentIds = empty( $attachmentIds ) || !is_array( $attachmentIds ) ? [] : $attachmentIds;
		if ( !empty( $existingFiles ) ) {
			$files = get_posts( [
				'post_type'        => 'attachment',
				'suppress_filters' => false,
				'post__in'         => $attachmentIds
			] );

			return $files;
		}

		return [];
	}

	public static function getAttachmentFile( $attachment_id ) {
		$data = null;
		if ( !empty( $attachment_id ) ) {
			$attachment = get_post( $attachment_id );
			if ( $attachment ) {
				$data = [
					'uid'       => $attachment->ID,
					'status'    => 'done',
					'url'       => $attachment->guid,
					'listingId' => $attachment->post_parent,
					'name'      => basename( $attachment->guid ),
					'meta'      => get_post_meta( $attachment->ID, '_wp_attachment_metadata', true ),
					'mime_type' => $attachment->post_mime_type
				];
			}
		}

		return apply_filters( 'rtcl_fb_get_attachment_file', $data, $attachment_id );
	}

	public static function getFieldAttachmentFiles( $listing_id, $field, $attachmentIds = null, $repeater = null ) {
		if ( empty( $repeater ) && !is_array( $attachmentIds ) ) {
			$name = !empty( $field['name'] ) ? $field['name'] : '';
			$attachmentIds = get_post_meta( $listing_id, $name, true );
			$attachmentIds = !empty( $attachmentIds ) && is_array( $attachmentIds ) ? $attachmentIds : [];
		}

		$files = [];

		if ( is_array( $attachmentIds ) && !empty( $attachmentIds ) ) {
			$attachments = get_children( [
				'post_parent'    => $listing_id,
				'post_type'      => 'attachment',
				'post__in'       => $attachmentIds,
				'orderby'        => 'post__in',
				'posts_per_page' => -1,
				'post_status'    => 'inherit',
				'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
									  [
										  'key'     => '_rtcl_attachment_type',
										  'value'   => 'file',
										  'compare' => '='
									  ]
				]
			] );

			if ( !empty( $attachments ) ) {
				foreach ( $attachments as $attachment ) {
					$files[] = self::getAttachmentFile( $attachment );
				}
			}
		}

		return apply_filters( 'rtcl_fb_get_field_attachment_files', $files, $listing_id, $field );
	}


	/**
	 * @param int $listing_id
	 * @param Form | null $form
	 *
	 * @return null|array
	 */
	public static function getFormData( $listing_id, $form ) {
		$formData = [];
		if ( empty( $listing_id ) ) {
			return null;
		}

		$listing = rtcl()->factory->get_listing( $listing_id );
		if ( empty( $listing ) ) {
			return null;
		}

		if ( !is_a( $form, Form::class ) ) {
			$form_id = absint( get_post_meta( $listing_id, '_rtcl_form_id', true ) );
			if ( empty( $form_id ) ) {
				return null;
			}
			$form = Form::query()->find( $form_id );
		}

		if ( empty( $form ) || empty( $fields = $form->fields ) ) {
			return null;
		}

		foreach ( $form->fields as $fieldUuid => $field ) {
			if ( empty( $field ) || empty( $field['name'] ) || empty( AvailableFields::get()[$field['element']] ) ) {
				continue;
			}
			$name = $field['name'];
			$element = $field['element'];
			$value = null;
			if ( isset( $field['preset'] ) && 1 == $field['preset'] ) {
				if ( 'title' === $element ) {
					$value = $listing->get_listing()->post_title;
				} elseif ( 'description' === $element ) {
					$value = $listing->get_listing()->post_content;
				} elseif ( 'listing_type' === $element ) {
					$value = $listing->get_ad_type();
				} elseif ( 'excerpt' === $element ) {
					$value = $listing->get_listing()->post_excerpt;
				} elseif ( 'category' === $element ) {
					$listingCategories = wp_get_object_terms( $listing_id, rtcl()->category );
					if ( !empty( $field['multiple'] ) ) {
						$value = $listingCategories;
					} else {
						$categories = [];
						if ( !empty( $listingCategories ) ) {
							foreach ( $listingCategories as $term ) {
								$_ancestors = [];
								$_terms = [];
								$childTerm = $term;
								while ( !empty( $term->parent ) && !in_array( $term->parent, $_ancestors, true ) ) {
									$parentTerm = get_term( $term->parent, rtcl()->category );
									if ( !is_wp_error( $parentTerm ) ) {
										$_ancestors[] = $term->parent;
										$_terms[] = $parentTerm;
										$term = $parentTerm;
									}
								}
								$_terms = array_merge( array_reverse( $_terms ), [ $childTerm ] );
								$categories = count( $_terms ) >= count( $categories ) ? $_terms : $categories;
							}
						}
						$value = $categories;
					}
				} elseif ( 'tag' === $element ) {
					$tags = wp_get_object_terms( $listing_id, rtcl()->tag );
					if ( !is_wp_error( $tags ) ) {
						$value = $tags;
					} else {
						$value = [];
					}
				} elseif ( 'location' === $element ) {
					$listingLocations = wp_get_object_terms( $listing_id, rtcl()->location );
					$locations = [];
					if ( !empty( $listingLocations ) ) {
						foreach ( $listingLocations as $location ) {
							$_ancestors = [];
							$_terms = [];
							$childTerm = $location;
							while ( !empty( $location->parent ) && !in_array( $location->parent, $_ancestors, true ) ) {
								$parentTerm = get_term( $location->parent, rtcl()->location );
								if ( !is_wp_error( $parentTerm ) ) {
									$_ancestors[] = $location->parent;
									$_terms[] = $parentTerm;
									$location = $parentTerm;
								}
							}
							$_terms = array_merge( array_reverse( $_terms ), [ $childTerm ] );
							$locations = count( $_terms ) >= count( $locations ) ? $_terms : $locations;
						}
					}

					$value = $locations;
				} elseif ( 'zipcode' === $element ) {
					$value = get_post_meta( $listing_id, 'zipcode', true );
				} elseif ( 'view_count' === $element ) {
					$value = $listing->get_view_counts();
				} elseif ( 'address' === $element ) {
					$value = get_post_meta( $listing_id, 'address', true );
				} elseif ( 'geo_location' === $element ) {
					$value = get_post_meta( $listing_id, '_rtcl_geo_address', true );
				} elseif ( 'phone' === $element ) {
					$value = get_post_meta( $listing_id, 'phone', true );
				} elseif ( 'whatsapp' === $element ) {
					$value = get_post_meta( $listing_id, '_rtcl_whatsapp_number', true );
				} elseif ( 'email' === $element ) {
					$value = $listing->get_email();
				} elseif ( 'website' === $element ) {
					$value = get_post_meta( $listing_id, 'website', true );
				} elseif ( 'social_profiles' === $element ) {
					$value = get_post_meta( $listing_id, '_rtcl_social_profiles', true );
				} elseif ( 'video_urls' === $element ) {
					$value = get_post_meta( $listing_id, '_rtcl_video_urls', true );
				} elseif ( 'pricing' === $element ) {
					$pricing = [];
					if ( !empty( $field['options'] ) && in_array( 'pricing_type', $field['options'] ) ) {
						$pricing['pricing_type'] = $listing->get_pricing_type();
						if ( 'range' === $pricing['pricing_type'] ) {
							$pricing['max_price'] = $listing->get_max_price();
						}
					}

					if ( !empty( $field['options'] ) && in_array( 'price_type', $field['options'] ) ) {
						$pricing['price_type'] = $listing->get_price_type();
					}
					$pricing['price'] = $listing->get_price();
					$pricing['price_unit'] = $listing->get_price_unit();
					$value = $pricing;
				} elseif ( 'map' === $element ) {
					$value = [
						'latitude'  => get_post_meta( $listing_id, 'latitude', true ),
						'longitude' => get_post_meta( $listing_id, 'longitude', true ),
						'hide_map'  => !empty( get_post_meta( $listing_id, 'hide_map', true ) ) ? 1 : '',
					];
				} elseif ( 'business_hours' === $element ) {
					$rawBsh = get_post_meta( $listing_id, '_rtcl_bhs', true );
					$bhs = null;
					if ( $listing->get_form_id() ) {
						if ( !empty( $rawBsh['active'] ) ) {
							$timeFormat = !empty( $field['time_format'] ) ? $field['time_format'] : 'H:i';
							$bhs = [
								'active' => true,
								'type'   => !empty( $rawBsh['type'] ) && 'selective' === $rawBsh['type'] ? 'selective' : 247,
								// 'timezone' => ! empty( $rawBsh['timezone'] ) ? $rawBsh['timezone'] : ''
							];
							if ( 'selective' === $bhs['type'] && !empty( $rawBsh['days'] ) ) {
								foreach ( $rawBsh['days'] as $day_key => $day ) {
									if ( !empty( $day['open'] ) ) {
										$bhs['days'][$day_key]['open'] = true;
										if ( !empty( $day['times'] ) && is_array( $day['times'] ) ) {
											$newTimes = [];
											foreach ( $day['times'] as $time ) {
												if ( !empty( $time['start'] ) && !empty( $time['end'] ) ) {
													$start = Utility::formatTime( $time['start'], $timeFormat, 'H:i' );
													$end = Utility::formatTime( $time['end'], $timeFormat, 'H:i' );
													if ( $start && $end ) {
														$newTimes[] = [
															'start' => $start,
															'end'   => $end
														];
													}
												}
											}
											if ( !empty( $newTimes ) ) {
												$bhs['days'][$day_key]['times'] = $newTimes;
											}
										}
									}
								}
							}
							if ( !empty( $rawBsh['special'] ) && is_array( $rawBsh['special'] ) ) {
								$tempDateList = [];
								$newSBhs = [];
								foreach ( $rawBsh['special'] as $sbh ) {
									if ( !empty( $sbh['date'] ) && !isset( $tempDateList[$sbh['date']] )
										&& $dateObj = Utility::sanitizedDateObj( $sbh['date'] )
									) {
										$date = $dateObj->format( 'Y-m-d' );
										$tempDateList[$date] = $date;
										$newSbh = [
											'date'  => $date,
											'occur' => !empty( $sbh['occur'] ) && $sbh['occur'] === 'once' ? 'once' : 'repeat'
										];
										if ( !empty( $sbh['open'] ) ) {
											$newSbh['open'] = true;
											if ( !empty( $sbh['times'] ) && is_array( $sbh['times'] ) ) {
												$newTimes = [];
												foreach ( $sbh['times'] as $time ) {
													if ( !empty( $time['start'] ) && !empty( $time['end'] ) ) {
														$start = Utility::formatTime( $time['start'], $timeFormat, 'H:i' );
														$end = Utility::formatTime( $time['end'], $timeFormat, 'H:i' );
														if ( $start && $end ) {
															$newTimes[] = [
																'start' => $start,
																'end'   => $end
															];
														}
													}
												}
												if ( !empty( $newTimes ) ) {
													$newSbh['times'] = $newTimes;
												}
											}
										} else {
											$newSbh['open'] = false;
										}
										$newSBhs[] = $newSbh;
									}
								}
								$bhs['special'] = !empty( $newSBhs ) ? $newSBhs : '';
							}
						}
					} else {
						$business_hours = !empty( $rawBsh ) && is_array( $rawBsh ) ? $rawBsh : [];
						$special_bhs = get_post_meta( $listing_id, '_rtcl_special_bhs', true );
						if ( is_array( $special_bhs ) && !empty( $special_bhs ) ) {
							$current_week_day = absint( gmdate( 'w', current_time( 'timestamp' ) ) );
							$special_data = [];
							foreach ( $special_bhs as $special_bh ) {
								if ( !empty( $special_bh['date'] ) ) {
									$week_day = gmdate( 'w', strtotime( $special_bh['date'] ) );
									if ( $week_day !== false && absint( $week_day ) === $current_week_day ) {
										if ( isset( $special_bh['open'] ) ) {
											$special_data['open'] = !empty( $special_bh['open'] );
											if ( !empty( $special_bh['times'] ) && is_array( $special_bh['times'] ) ) {
												$special_data['times'] = $special_bh['times'];
											}
										}
									}
								}
							}
							if ( !empty( $special_data ) ) {
								$business_hours[$current_week_day] = $special_data;
							}
						}
						if ( !empty( $business_hours ) ) {
							$bhs = [
								'active' => true,
								'type'   => 'selective',
								'days'   => $business_hours
							];
						}
					}
					$value = $bhs;
				} elseif ( 'terms_and_condition' === $element ) {
					$value = get_post_meta( $listing_id, 'rtcl_agree', true );
				} elseif ( 'images' === $element ) {
					$attachments = get_children( [
						'post_parent'    => $listing_id,
						'post_type'      => 'attachment',
						'posts_per_page' => -1,
						'post_status'    => 'inherit',
						'orderby'        => 'menu_order',
						'order'          => 'ASC',
						'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
											  'relation' => 'OR',
											  [
												  'key'     => '_rtcl_attachment_type',
												  'value'   => 'image',
												  'compare' => '='
											  ],
											  [
												  'key'     => '_rtcl_attachment_type',
												  'compare' => 'NOT EXISTS'
											  ]
						]
					] );
					// Old image sorting
					// $attachments = Functions::sort_images( $attachments, $listing_id );
					$images = [];
					if ( !empty( $attachments ) ) {
						$featured_id = intval( get_post_meta( $listing_id, '_thumbnail_id', true ) );
						foreach ( $attachments as $attachment ) {
							$images[] = [
								'uid'       => $attachment->ID,
								'status'    => 'done',
								'url'       => wp_get_attachment_url( $attachment->ID ),
								'featured'  => $featured_id === $attachment->ID,
								'name'      => basename( $attachment->guid ),
								'mime_type' => $attachment->post_mime_type
							];
						}
					}
					$value = $images;
				} elseif ( 'classima_spec_info' === $name ) {
					$feature = get_post_meta( $listing_id, $name, true );
					if ( is_array( $feature ) && !empty( $feature['specs'] ) ) {
						$value = implode( PHP_EOL, array_filter(
							array_map( 'trim', preg_split( '/\r\n|\r|\n/', $feature['specs'] ) )
						) );
					} else {
						$value = $feature;
					}
				} else {
					if ( empty( $field['multiple'] ) ) {
						$value = get_post_meta( $listing_id, $name, true );
					} else {
						$value = get_post_meta( $listing_id, $name );
					}
				}
			} else {
				if ( 'checkbox' === $element ) {
					$value = get_post_meta( $listing_id, $name );
				} elseif ( 'file' === $element ) {
					$value = self::getFieldAttachmentFiles( $listing_id, $field );
				} elseif ( 'date' === $element ) {
					$dateType = !empty( $field['date_type'] ) ? $field['date_type'] : 'single';
					$dateFormat = !empty( $field['date_format'] ) ? $field['date_format'] : 'Y-d-m H:i';
					if ( 'range' === $dateType ) {
						$value = [
							'start' => get_post_meta( $listing_id, $name . '_' . 'start', true ),
							'end'   => get_post_meta( $listing_id, $name . '_' . 'end', true )
						];

						$value['start'] = !empty( $value['start'] ) ? gmdate( $dateFormat, strtotime( $value['start'] ) ) : null;
						$value['end'] = !empty( $value['end'] ) ? gmdate( $dateFormat, strtotime( $value['end'] ) ) : null;
					} else {
						$value = get_post_meta( $listing_id, $name, true );
						$value = !empty( $value ) ? gmdate( $dateFormat, strtotime( $value ) ) : '';
					}
				} elseif ( 'repeater' === $element ) {
					$tempValues = get_post_meta( $listing_id, $name, true );
					if ( !empty( $field['fields'] ) && !empty( $tempValues ) && is_array( $tempValues ) ) {
						$values = [];
						foreach ( $tempValues as $rIndex => $tempValueArray ) {
							if ( !empty( $tempValueArray ) && is_array( $tempValueArray ) ) {
								foreach ( $field['fields'] as $rField ) {
									if ( !empty( $tempValueArray[$rField['name']] ) ) {
										if ( !empty( $rField['element'] ) && 'file' === $rField['element'] ) {
											$values[$rIndex][$rField['name']] = self::getFieldAttachmentFiles( $listing_id, $rField,
												$tempValueArray[$rField['name']], $field );
										} else {
											$values[$rIndex][$rField['name']] = $tempValueArray[$rField['name']];
										}
									}
								}
							}
						}
						$value = !empty( $values ) ? $values : [];
					}
				} else {
					if ( empty( $field['multiple'] ) ) {
						$value = get_post_meta( $listing_id, $name, true );
					} else {
						$value = get_post_meta( $listing_id, $name );
					}
				}
			}

			$formData[$name] = apply_filters( 'rtcl_fb_field_value_' . $element, $value, $field, $listing );
		}

		return apply_filters( 'rtcl_fb_form_data', $formData, $form, $listing );
	}


	/**
	 * Get default form
	 *
	 * @return Model| Form | null
	 */
	public static function getDefaultForm() {
		$form = Form::query()->where( 'default', 1 )->one();

		$form = apply_filters( 'rtcl_fb_form', $form );
		if ( is_a( $form, Form::class ) ) {
			return $form;
		}

		return null;
	}

	public static function getFormList() {
		$forms = [];

		if ( ! self::isEnabled() ) {
			return $forms;
		}

		$allForms = Form::query()->select( 'id,title,`default`' )->where( 'status', 'publish' )->order_by( 'created_at', 'DESC' )->get();

		if ( ! empty( $allForms ) ) {
			foreach ( $allForms as $form ) {
				$forms[ $form->id ] = $form->title;
			}
		}

		return is_array( $forms ) ? $forms : [];
	}

	/**
	 * @param Form $form
	 *
	 * @return null|array
	 */
	public static function getFormDefaultData( $form ) {

		if ( !is_a( $form, Form::class ) || empty( $fields = $form->fields ) ) {
			return null;
		}

		$formData = [];
		foreach ( $fields as $fieldUuid => $field ) {
			if ( empty( $field ) || empty( $field['name'] ) ) {
				continue;
			}
			$name = $field['name'];
			$value = null;

			if ( !empty( $field['default_value'] ) ) {
				$value = apply_filters( 'rtcl/fb/parse_default_value', $field['default_value'], $field, $form );
				if ( $value ) {
					$formData[$name] = $value;
				}
			}
		}

		return apply_filters( 'rtcl/fb/default_data', $formData, $form );
	}

	/**
	 * @param $formData
	 * @param $logics
	 * @param $fields
	 *
	 * @return bool
	 */
	public static function isValidateCondition( $formData, $logics, $fields ) {
		if ( !empty( $logics['relation'] ) && 'and' === strtolower( $logics['relation'] ) ) {
			if ( !empty( $logics['conditions'] ) ) {
				foreach ( $logics['conditions'] as $condition ) {
					if ( !self::checkCondition( $formData, $condition, $fields ) ) {
						return false;
					}
				}

				return true;
			}
		} else {
			if ( !empty( $logics['conditions'] ) ) {
				foreach ( $logics['conditions'] as $condition ) {
					if ( self::checkCondition( $formData, $condition, $fields ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * @param array $formData
	 * @param array $condition
	 * @param array $fields
	 *
	 * @return bool
	 */
	public static function checkCondition( $formData, $condition, $fields ) {
		$field = !empty( $condition['fieldId'] ) && !empty( $fields[$condition['fieldId']] ) ? $fields[$condition['fieldId']] : '';
		if ( empty( $field ) ) {
			return false;
		}
		// Fixed: missing key at array element
		$condition = wp_parse_args( $condition, ['fieldId'=> '', 'value'=> '', 'operator' => '']);
		$fieldName = $field['name'];
		$currentValue = $formData[$fieldName] ?? null;
		if ( '=' === $condition['operator'] ) {
			return $currentValue == $condition['value'];
		} elseif ( '!=' === $condition['operator'] ) {
			return $currentValue != $condition['value'];
		} elseif ( 'contains' === $condition['operator'] ) {
			return is_array( $currentValue ) && in_array( $condition['value'], $currentValue );
		} elseif ( 'doNotContains' === $condition['operator'] ) {
			return !is_array( $currentValue ) || !in_array( $condition['value'], $currentValue );
		} elseif ( 'startsWith' === $condition['operator'] ) {
			return $currentValue && str_starts_with( $currentValue, $condition['value'] );
		} elseif ( 'endsWith' === $condition['operator'] ) {
			return $currentValue && str_ends_with( $currentValue, $condition['value'] );
		} elseif ( 'empty' === $condition['operator'] ) {
			return $currentValue === null || $currentValue === '';
		} elseif ( 'notEmpty' === $condition['operator'] ) {
			return $currentValue !== null && $currentValue !== '';
		}

		return false;
	}

	/**
	 * Get plain text length for a field value.
	 * For wp_editor fields (description, textarea with editor_type wp_editor, excerpt),
	 * strips HTML tags before counting to match frontend character counting behavior.
	 *
	 * @param string $value
	 * @param array  $field
	 *
	 * @return int
	 */
	public static function getFieldTextLength( $value, array $field ): int {
		if ( ! is_string( $value ) || '' === $value ) {
			return 0;
		}
		$isWpEditor = false;
		if ( in_array( $field['element'] ?? '', [ 'description', 'excerpt' ], true ) ) {
			$isWpEditor = true;
		} elseif ( ( $field['element'] ?? '' ) === 'textarea' && ! empty( $field['editor_type'] ) && 'wp_editor' === $field['editor_type'] ) {
			$isWpEditor = true;
		}
		if ( $isWpEditor ) {
			return mb_strlen( wp_strip_all_tags( $value ) );
		}

		return mb_strlen( $value );
	}

	/**
	 * @param string|array $value
	 * @param array $field
	 * @param Listing | null $listing
	 *
	 * @return array|null
	 */
	public static function isValidateField( $value, array $field, $listing ) {
		// Escape validation is it its from API call and recapture
		if ( wp_is_rest_endpoint() && !empty( $field['element'] ) && "recaptcha" === $field['element'] ) {
			return null;
		}
		$rules = !empty( $field['validation'] ) ? $field['validation'] : [];
		if ( !empty( $rules ) ) {
			$errors = [];

			foreach ( $rules as $ruleKey => $rule ) {
				if ( ( false === $rule['value'] || 'false' === $rule['value'] ) ) {
					continue;
				}
				$hasError = false;
				if ( 'required' === $ruleKey ) {
					if ( 'pricing' === $field['element'] ) {
						if ( !empty( $field['options'] ) && is_array( $field['options'] ) ) {
							$ruleMessage = !empty( $rule['message'] ) ? str_replace( '{value}', $rule['value'], $rule['message'] ) : '';
							$priceRequiredErrors = [];
							if ( in_array( 'pricing_type', $field['options'] ) ) {
								if ( empty( $value['pricing_type'] ) ) {
									$priceRequiredErrors['pricing_type'] = $ruleMessage;
								}
							}
							if ( !in_array( 'pricing_type', $field['options'] )
								|| ( in_array( 'pricing_type', $field['options'] )
									&& !empty( $value['pricing_type'] )
									&& $value['pricing_type'] !== "disabled" )
							) {
								if ( in_array( 'price_type', $field['options'] ) ) {
									if ( empty( $value['price_type'] ) ) {
										$priceRequiredErrors['price_type'] = $ruleMessage;
									}
								}
								if ( ( !in_array( 'price_type', $field['options'] )
									|| ( in_array( 'price_type', $field['options'] )
										&& !empty( $value['price_type'] )
										&& $value['price_type'] !== "on_call" ) )
								) {
									if ( in_array( 'price_unit', $field['options'] ) ) {
										if ( empty( $value['price_unit'] ) ) {
											$priceRequiredErrors['price_unit'] = $ruleMessage;
										}
									}
									if ( empty( $value['price'] ) ) {
										$priceRequiredErrors['price'] = $ruleMessage;
									}
									if ( in_array( 'pricing_type', $field['options'] ) && !empty( $value['pricing_type'] )
										&& $value['pricing_type'] === "range"
									) {
										if ( empty( $value['max_price'] ) ) {
											$priceRequiredErrors['max_price'] = $ruleMessage;
										}
									}
								}
							}
							if ( !empty( $priceRequiredErrors ) ) {
								$errors[$ruleKey] = $priceRequiredErrors;
							}
						} else {
							$hasError = empty( $value['price'] );
						}
					} else {
						$hasError = !$value;
					}
				} elseif ( 'min' === $ruleKey ) {
					if ( "number" === $field['element'] ) {
						if ( $rule['value'] ) {
							if ( $value && $value < $rule['value'] ) {
								$hasError = true;
							}
						}
					} else {
						if ( $rule['value'] ) {
							$checkLength = self::getFieldTextLength( $value, $field );
							if ( $value && ( $checkLength < $rule['value'] ) ) {
								$hasError = true;
							}
						}
					}
				} elseif ( 'max' === $ruleKey ) {
					$ruleValue = Functions::convertToNumber( $rule['value'] );
					if ( $ruleValue !== null ) {
						if ( "number" === $field['element'] ) {
							if ( $value && $value > $ruleValue ) {
								$hasError = true;
							}
						} else {
							$checkLength = self::getFieldTextLength( $value, $field );
							if ( $ruleValue && $value && $checkLength > $ruleValue ) {
								$hasError = true;
							}
						}
					}

				} elseif ( 'email' === $ruleKey ) {
					if ( $value && !is_email( $value ) ) {
						$hasError = true;
					}
				} elseif ( 'url' === $ruleKey ) {
					if ( $value && !filter_var( $value, FILTER_VALIDATE_URL ) ) {
						$hasError = true;
					}
				}

				if ( true === $hasError && $rule['message'] ) {
					$errors[$ruleKey] = str_replace( '{value}', $rule['value'], $rule['message'] );
				}
			}
			if ( $value ) {
				if ( 'listing_type' === $field['element'] ) {
					if ( !in_array( $value, array_keys( Functions::get_listing_types() ) ) ) {
						$errors['not_exist'] = __( 'Listing type is not exist', 'classified-listing' );
					}
				} elseif ( 'tag' === $field['element'] ) {
					if ( is_array( $value ) ) {
						foreach ( $value as $tag ) {
							$tagId = is_array( $tag ) && isset( $tag['term_id'] ) ? $tag['term_id'] : $tag;
							if ( !term_exists( absint( $tagId ), rtcl()->tag ) ) {
								$errors['not_exist'] = __( 'Tag is not exist', 'classified-listing' );
								break;
							}
						}
					} else {
						if ( !term_exists( absint( $value ), rtcl()->tag ) ) {
							$errors['not_exist'] = __( 'Tag is not exist', 'classified-listing' );
						}
					}
				} elseif ( 'category' === $field['element'] ) {
					if ( is_array( $value ) ) {
						foreach ( $value as $category ) {
							$categoryId = is_array( $category ) && isset( $category['term_id'] ) ? $category['term_id'] : $category;
							if ( !term_exists( absint( $categoryId ), rtcl()->category ) ) {
								$errors['not_exist'] = __( 'Category is not exist', 'classified-listing' );
								break;
							}
						}
					} else {
						if ( !term_exists( absint( $value ), rtcl()->category ) ) {
							$errors['not_exist'] = __( 'Category is not exist', 'classified-listing' );
						}
					}

					if ( !$listing && has_action( 'rtcl_before_add_edit_listing_into_category_condition' ) ) {
						Functions::clear_notices();
						if ( is_array( $value ) ) {
							$category = end( $value );
							$category_id = is_array( $category ) && isset( $category['term_id'] ) ? $category['term_id'] : $category;
						} else {
							$category_id = $value;
						}
						do_action( 'rtcl_before_add_edit_listing_into_category_condition', 0, $category_id );
						if ( Functions::notice_count( 'error' ) ) {
							$errors = Functions::get_notices( 'error' );
							$errors['membership_error'] = $errors[0];
						}
					}
				} elseif ( 'location' === $field['element'] ) {
					if ( is_array( $value ) ) {
						foreach ( $value as $tag ) {
							$tagId = is_array( $tag ) && isset( $tag['term_id'] ) ? $tag['term_id'] : $tag;
							if ( !term_exists( absint( $tagId ), rtcl()->location ) ) {
								$errors['not_exist'] = __( 'Location is not exist', 'classified-listing' );
								break;
							}
						}
					} else {
						if ( !term_exists( absint( $value ), rtcl()->location ) ) {
							$errors['not_exist'] = __( 'Location is not exist', 'classified-listing' );
						}
					}
				} elseif ( 'email' === $field['element'] ) {
					if ( !is_email( $value ) ) {
						$errors['invalid_email'] = __( 'Invalid email', 'classified-listing' );
					}
				} elseif ( 'website' === $field['element'] || 'url' === $field['element'] ) {
					if ( filter_var( $value, FILTER_VALIDATE_URL ) === false ) {
						$errors['invalid_url'] = __( 'Invalid url', 'classified-listing' );
					}
				} elseif ( 'video_urls' === $field['element'] ) {
					//$pattern = '/(https?:\/\/)(www.)?(youtube.com\/watch[?]v=([a-zA-Z0-9_-]{11}))|https?:\/\/(www.)?vimeo.com\/(\d+)/';
					$pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?v=|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})$|^(https?:\/\/)?(www\.)?vimeo\.com\/(\d+)$/';
					if ( is_array( $value ) ) {
						foreach ( $value as $videoUrl ) {
							if ( !preg_match( $pattern, $videoUrl ) ) {
								$errors['invalid_url'] = __( 'Invalid video url', 'classified-listing' );
								break;
							}
						}
					} else {
						if ( !preg_match( $pattern, $value ) ) {
							$errors['invalid_url'] = __( 'Invalid video url', 'classified-listing' );
						}
					}
				} elseif ( 'select' === $field['element'] || 'radio' === $field['element'] ) {
					if ( !empty( $field['options'] ) ) {
						$_selectValid = false;
						foreach ( $field['options'] as $option ) {
							if ( !empty( $option['value'] ) && $option['value'] === $value ) {
								$_selectValid = true;
								break;
							}
						}
						if ( !$_selectValid ) {
							$errors['not_exist'] = __( 'Option not exist', 'classified-listing' );
						}
					}
				} elseif ( 'checkbox' === $field['element'] ) {
					if ( is_array( $value ) ) {
						if ( !empty( $field['options'] ) ) {
							foreach ( $value as $_value ) {
								$_checkboxValid = false;
								foreach ( $field['options'] as $option ) {
									if ( !empty( $option['value'] ) && in_array( $option['value'], $value ) ) {
										$_checkboxValid = true;
										break;
									}
								}
								if ( !$_checkboxValid ) {
									$errors['not_exist'] = __( 'Option not exist', 'classified-listing' );
									break;
								}
							}
						} else {
							$errors['not_exist'] = __( 'Option not exist', 'classified-listing' );
						}
					} else {
						$errors['invalid_value'] = __( 'Value should ne an array', 'classified-listing' );
					}
				} elseif ( 'recaptcha' === $field['element'] ) {
					$recaptchaSecretKey = Functions::get_option_item( 'rtcl_misc_settings', 'recaptcha_secret_key' );
					if ( !$recaptchaSecretKey ) {
						$errors['empty_recaptcha_key'] = __( 'Invalid Google reCAPTACHA secret key', 'classified-listing' );
					}
					$request = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptchaSecretKey . '&response=' . $value
						. '&remoteip=' . $_SERVER['REMOTE_ADDR'] );
					$response_body = wp_remote_retrieve_body( $request );
					$response = json_decode( $response_body );
					if ( empty( $response->success ) || true !== $response->success ) {
						$errors['invalid_recaptcha'] = __( 'Error in Google reCAPTACHA', 'classified-listing' );
					}
				}
			}
			if ( !empty( $errors ) ) {
				return $errors;
			}
		}

		return null;
	}


	/**
	 * @param array $values
	 * @param array $field
	 * @param Listing | null $listing
	 *
	 * @return array|null
	 */
	public static function isValidateRepeaterField( $values, array $field, $listing ) {
		if ( !empty( $field['fields'] ) && is_array( $field['fields'] ) && is_array( $values ) && !empty( $values ) ) {
			$errors = [];
			foreach ( $values as $_repeaterIndex => $_repeaterValue ) {
				$itemErrors = [];
				foreach ( $field['fields'] as $_repeaterFieldIndex => $_repeaterField ) {
					if ( empty( $_repeaterField['name'] ) ) {
						continue;
					}
					$_e = self::isValidateField( $_repeaterValue[$_repeaterField['name']] ?? '', $field, $listing );
					if ( $_e ) {
						$itemErrors[$_repeaterField['name']] = $_e;
					}
				}
				if ( !empty( $itemErrors ) ) {
					$errors[$_repeaterIndex] = $itemErrors;
				}
			}

			if ( !empty( $errors ) ) {
				return $errors;
			}
		}

		return null;
	}

	/**
	 * @param array $rawFormData
	 * @param Form | Model $form
	 * @param Listing | null $listing
	 *
	 * @return array
	 */
	public static function formDataValidation( $rawFormData, $form, $listing ) {
		$errors = [];
		$sections = $form->sections;
		$fields = $form->fields;
		$availableFields = AvailableFields::get();
		if ( !empty( $sections ) ) {
			foreach ( $sections as $section ) {
				$logics = $section['logics'];
				if ( !empty( $logics['status'] ) && in_array( $logics['status'], [ true, 'true' ], true ) ) {
					if ( !self::isValidateCondition( $rawFormData, $logics, $fields ) ) {
						continue;
					}
				}

				$columns = is_array( $section['columns'] ) ? $section['columns'] : [];

				foreach ( $columns as $column ) {
					$fieldIds = is_array( $column['fields'] ) ? $column['fields'] : [];
					foreach ( $fieldIds as $fieldId ) {
						$field = !empty( $fields[$fieldId] ) ? $fields[$fieldId] : '';
						if ( empty( $field ) || empty( $availableFields[$field['element']] ) ) {
							continue;
						}
						$uuid = $field['uuid'];
						$name = !empty( $field['name'] ) ? $field['name'] : null;
						if ( ( empty( $field['logics'] ) || empty( $field['logics']['status'] )
								|| in_array( $field['logics']['status'],
									[
										false,
										'false'
									],
									true ) )
							|| ( in_array( $field['logics']['status'],
									[
										true,
										'true'
									],
									true )
								&& self::isValidateCondition( $rawFormData, $logics, $fields ) )
						) {

							if ( $field['element'] === 'repeater' ) {
								$_errors = self::isValidateRepeaterField( $rawFormData[$name] ?? '', $field, $listing );
							} else {
								$_errors = isset( $rawFormData[$name] ) ? self::isValidateField( $rawFormData[$name], $field, $listing ) : null;
							}

							if ( $_errors ) {
								$errors[$uuid] = $_errors;
							}
						}
					}
				}
			}
		}

		return $errors;
	}

	/**
	 * @param              $rawValue
	 * @param              $field
	 * @param Listing|null $listing
	 *
	 * @return mixed|null
	 */
	public static function sanitizeFieldValue( $rawValue, $field, $listing = null ) {
		if ( null == $rawValue || '' == $rawValue ) {
			return $rawValue;
		}
		$element = !empty( $field['element'] ) ? $field['element'] : null;

		switch ( $element ) {
			case 'title':
				$sanitize_value = sanitize_text_field( $rawValue );
				if ( $sanitize_value && !current_user_can( 'administrator' ) && !current_user_can( 'editor' ) ) {
					$sanitize_value = strip_shortcodes( $sanitize_value );
				}

				if ( !empty( $field['validation']['max']['value'] ) && $title_limit = absint( $field['validation']['max']['value'] ) ) {
					if ( strlen( $sanitize_value ) > $title_limit ) {
						$sanitize_value = mb_substr( $sanitize_value, 0, $title_limit, 'utf-8' );
					}
				}
				break;
			case 'description':
			case 'excerpt':
				if ( !current_user_can( 'administrator' ) && !current_user_can( 'editor' ) ) {
					$rawValue = strip_shortcodes( $rawValue );
				}
				if ( !empty( $field['validation']['max']['value'] ) && $description_limit = absint( $field['validation']['max']['value'] ) ) {
					if ( mb_strlen( wp_strip_all_tags( $rawValue ) ) > $description_limit ) {
						$sanitize_value = wp_filter_nohtml_kses( $rawValue );
						$sanitize_value = mb_substr( $sanitize_value, 0, $description_limit, 'utf-8' );
					} else {
						$sanitize_value = wp_kses_post( $rawValue );
					}
				} else {
					$sanitize_value = wp_kses_post( $rawValue );
				}

				break;
			case 'textarea':
				if ( !empty( $field['editor_type'] ) && 'wp_editor' === $field['editor_type'] ) {
					$sanitize_value = wp_kses_post( wp_unslash( $rawValue ) );
				} else {
					$sanitize_value = sanitize_textarea_field( wp_unslash( $rawValue ) );
				}
				break;
			case 'address':
				$sanitize_value = sanitize_textarea_field( wp_unslash( $rawValue ) );
				break;
			case 'html':
				$sanitize_value = wp_kses_post( $rawValue );
				break;
			case 'checkbox':
				$sanitize_value = array_map( 'esc_attr', is_array( $rawValue ) ? $rawValue : [] );
				break;
			case 'view_count':
				$sanitize_value = absint( $rawValue );
				break;
			case 'website':
			case 'url':
				$sanitize_value = esc_url_raw( $rawValue );
				break;
			case 'social_profiles':
				$sanitize_value = [];
				if ( is_array( $rawValue ) ) {
					$socialProfilesList = array_keys( Options::get_social_profiles_list() );
					foreach ( $rawValue as $spKey => $spValue ) {
						if ( in_array( $spKey, $socialProfilesList ) ) {
							$sanitize_value[$spKey] = esc_url_raw( $spValue );
						}
					}
				}
				break;
			case 'date':
				$dateType = !empty( $field['date_type'] ) ? $field['date_type'] : 'single';
				$dateFormat = !empty( $field['date_format'] ) ? $field['date_format'] : 'Y-m-d';
				$pickerType = strpos( $dateFormat, 'h:i A' ) !== false || strpos( $dateFormat, 'H:i' ) !== false ? 'time' : 'date';
				$raw_date_save_format = Functions::get_custom_field_save_date_format();
				if ( $pickerType === 'time' ) {
					$date_save_format = implode( ' ', $raw_date_save_format );
				} else {
					$date_save_format = $raw_date_save_format['date'];
				}

				try {
					if ( $dateType === 'range' ) {
						$start = $end = '';
						if ( !empty( $rawValue['start'] ) ) {
							$date = DateTime::createFromFormat( $dateFormat, $rawValue['start'] );
							$start = $date ? $date->format( $date_save_format ) : '';
						}
						if ( !empty( $rawValue['end'] ) ) {
							$date = DateTime::createFromFormat( $dateFormat, $rawValue['end'] );
							$end = $date ? $date->format( $date_save_format ) : '';
						}
						$formatted_date = [
							'start' => $start,
							'end'   => $end
						];
					} else {
						$date = DateTime::createFromFormat( $dateFormat, $rawValue );
						$formatted_date = $date ? $date->format( $date_save_format ) : '';
					}
				} catch ( \Exception $e ) {
					$formatted_date = $dateType === 'range' ? [
						'start' => '',
						'end'   => ''
					] : '';
				}

				$sanitize_value = $formatted_date;
				break;
			case 'video_urls':
				$sanitize_value = [];
				if ( !empty( $rawValue ) ) {
					// Pattern to check youtube or vimeo url
					//$pattern = '/(https?:\/\/)(www.)?(youtube.com\/watch[?]v=([a-zA-Z0-9_-]{11}))|https?:\/\/(www.)?vimeo.com\/([0-9]{9})/';
					$pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?v=|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})$|^(https?:\/\/)?(www\.)?vimeo\.com\/(\d+)$/';
					if ( is_array( $rawValue ) ) {
						$filtered = array_filter( $rawValue,
							function ( $url ) use ( $pattern ) {
								return preg_match( $pattern, $url );
							} );
						if ( !empty( $filtered ) ) {
							$sanitize_value = array_map( 'esc_url_raw', $filtered );
						}
					} elseif ( preg_match( $pattern, $rawValue ) ) {
						$sanitize_value[] = esc_url_raw( $rawValue );
					}
				}
				break;
			case 'business_hours':
				$sanitize_value = null;
				$timeFormat = !empty( $field['time_format'] ) ? $field['time_format'] : 'H:i';
				$isActive = !empty( $rawValue['active'] ) && filter_var( $rawValue['active'], FILTER_VALIDATE_BOOLEAN );
				if ( $isActive ) {
					$bhs = [
						'active' => true,
						// 'timezone' => ! empty( $rawValue['type'] ) ? sanitize_text_field( $rawValue['timezone'] ) : '',
						'type'   => !empty( $rawValue['type'] ) && !empty( $rawValue['days'] ) && $rawValue['type'] === 'selective' ? 'selective' : 247
					];
					if ( $bhs['type'] === 'selective' && !empty( $rawValue['days'] ) ) {
						$days = [];
						foreach ( $rawValue['days'] as $day_key => $day ) {
							if ( !empty( $day['open'] ) ) {
								$days[$day_key]['open'] = true;
								if ( !empty( $day['times'] ) && is_array( $day['times'] ) ) {
									$newTimes = [];
									foreach ( $day['times'] as $time ) {
										if ( !empty( $time['start'] ) && !empty( $time['end'] ) ) {
											$start = Utility::formatTime( $time['start'], 'H:i', $timeFormat );
											$end = Utility::formatTime( $time['end'], 'H:i', $timeFormat );
											if ( $start && $end ) {
												$newTimes[] = [
													'start' => $start,
													'end'   => $end
												];
											}
										}
									}
									if ( !empty( $newTimes ) ) {
										$days[$day_key]['times'] = $newTimes;
									}
								}
							}

							if ( !empty( $days ) ) {
								$bhs['days'] = $days;
							}
						}
					}
					if ( 'selective' === $bhs['type'] && empty( $bhs['days'] ) ) {
						$bhs['type'] = 247;
					}

					if ( !empty( $rawValue['special'] ) && is_array( $rawValue['special'] ) ) {
						$tempDateList = [];
						$newSBhs = [];
						foreach ( $rawValue['special'] as $sbh ) {
							if ( !empty( $sbh['date'] ) && !isset( $tempDateList[$sbh['date']] ) && $dateObj = Utility::sanitizedDateObj( $sbh['date'] ) ) {
								$date = $dateObj->format( 'Y-m-d' );
								$tempDateList[$date] = $date;
								$newSbh = [
									'date'  => $date,
									'occur' => !empty( $sbh['occur'] ) && $sbh['occur'] === 'once' ? 'once' : 'repeat'
								];
								if ( !empty( $sbh['open'] ) ) {
									$newSbh['open'] = true;
									if ( is_array( $sbh['times'] ) && !empty( $sbh['times'] ) ) {
										$newTimes = [];
										foreach ( $sbh['times'] as $time ) {
											if ( !empty( $time['start'] ) && !empty( $time['end'] ) ) {
												$start = Utility::formatTime( $time['start'], 'H:i', $timeFormat );
												$end = Utility::formatTime( $time['end'], 'H:i', $timeFormat );
												if ( $start && $end ) {
													$newTimes[] = [
														'start' => $start,
														'end'   => $end
													];
												}
											}
										}
										if ( !empty( $newTimes ) ) {
											$newSbh['times'] = $newTimes;
										}
									}
								} else {
									$newSbh['open'] = false;
								}
								$newSBhs[] = $newSbh;
							}
						}
						$bhs['special'] = !empty( $newSBhs ) ? $newSBhs : '';
					}
					$sanitize_value = $bhs;
				}
				break;
			case 'switch':
				$sanitize_value = 'yes' === $rawValue ? 'yes' : 'no';
				break;
			case 'repeater':
				$sanitize_value = [];
				if ( is_array( $field['fields'] ) && !empty( $field['fields'] ) && is_array( $rawValue ) && !empty( $rawValue ) ) {
					$oldValues = is_a( $listing, Listing::class ) ? get_post_meta( $listing->get_id(), $field['name'], true ) : [];

					foreach ( $rawValue as $_repeaterIndex => $repeaterValues ) {
						$itemValues = [];
						foreach ( $field['fields'] as $_repeaterFieldIndex => $repeaterField ) {
							if ( empty( $repeaterField['name'] ) ) {
								continue;
							}
							$_value = $repeaterValues[$repeaterField['name']] ?? '';
							if ( 'file' === $repeaterField['element'] ) {
								$value = !empty( $oldValues[$_repeaterIndex][$repeaterField['name']] )
									? $oldValues[$_repeaterIndex][$repeaterField['name']] : '';
								// if ( ! empty( $_value ) && is_array( $_value ) ) {
								// $attachment_ids = array_map( function ( $_item ) {
								// return ! empty( $_item['uid'] ) ? absint( $_item['uid'] ) : absint( $_item );
								// },
								// $_value );
								// if ( ! empty( $attachment_ids ) ) {
								// $value = $attachment_ids;
								// }
								// }
							} else {
								$value = self::sanitizeFieldValue( $_value, $repeaterField );
							}
							if ( '' !== $value ) {
								$itemValues[$repeaterField['name']] = $value;
							}
						}
						if ( !empty( $itemValues ) ) {
							$sanitize_value[] = $itemValues;
						}
					}
				}
				break;
			default:
				$sanitize_value = is_array( $rawValue ) || is_object( $rawValue ) ? map_deep( $rawValue,
					function ( $_rValue ) {
						return sanitize_text_field( wp_unslash( $_rValue ) );
					} ) : sanitize_text_field( wp_unslash( $rawValue ) );
				break;
		}

		return apply_filters( 'rtcl_fb_sanitize_field_' . $element, $sanitize_value, $rawValue, $field );
	}

	/**
	 * @param array $fields
	 *
	 * @return array
	 */
	public static function reOrderCustomField( $fields ): array {
		if ( !is_array( $fields ) || empty( $fields ) ) {
			return [];
		}

		usort( $fields,
			function ( $a, $b ) {
				$aOrder = !empty( $a['order'] ) ? absint( $a['order'] ) : 0;
				$bOrder = !empty( $b['order'] ) ? absint( $b['order'] ) : 0;

				return $aOrder <=> $bOrder;
			} );

		return $fields;
	}

	/**
	 * @param string|int|array $directory
	 *
	 * @return array
	 */
	public static function getDirectoryData( $directory = '' ) {
		$data = [
			FBField::PRESET   => [],
			FBField::CUSTOM   => [],
			FBField::SECTIONS => []
		];
		if ( $directory === 'all' || is_array( $directory ) ) {
			if ( is_array( $directory ) ) {
				$directoryIds = !empty( $directory ) ? array_filter( array_map( 'absint', $directory ) ) : [];
				if ( !empty( $directoryIds ) ) {
					$allForms = Form::query()->where( 'status', 'publish' )->where( 'id', 'in', $directoryIds )->order_by( 'created_at', 'DESC' )->get();
				} else {
					$allForms = [];
				}
			} else {
				$allForms = Form::query()->where( 'status', 'publish' )->order_by( 'created_at', 'DESC' )->get();
			}
			if ( !empty( $allForms ) ) {
				foreach ( $allForms as $_form ) {
					$_form = apply_filters( 'rtcl_fb_form', $_form );
					if ( !empty( $_form->sections ) ) {
						$data['sections'] = array_merge( $data['sections'], $_form->sections );
					}
					$_fields = $_form->fields;
					if ( !empty( $_fields ) ) {
						foreach ( $_fields as $field ) {
							if ( empty( $field['name'] ) ) {
								continue;
							}
							if ( isset( $field['preset'] ) && $field['preset'] == 1 ) {
								$data['preset'][$field['uuid']] = $field;
							} else {
								$data['custom'][$field['uuid']] = $field;
							}
						}
					}
				}
			}
		} elseif ( $directory ) {
			$form = Form::query()->where( 'status', 'publish' )->find( $directory );
			$form = apply_filters( 'rtcl_fb_form', $form );
			if ( $form ) {
				$_fields = $form->fields;
				if ( !empty( $form->sections ) ) {
					$data['sections'] = array_merge( $data['sections'], $form->sections );
				}
				if ( !empty( $_fields ) ) {
					foreach ( $_fields as $field ) {
						if ( empty( $field['name'] ) ) {
							continue;
						}
						if ( isset( $field['preset'] ) && $field['preset'] == 1 ) {
							$data['preset'][$field['uuid']] = $field;
						} else {
							$data['custom'][$field['uuid']] = $field;
						}
					}
				}
			}
		} else {
			$form = Form::query()->where( 'status', 'publish' )->where( 'default', 1 )->one();
			if ( $form ) {
				$_sections = $form->sections;
				if ( !empty( $_sections ) ) {
					$data['sections'] = array_merge( $data['sections'], $_sections );
				}
				$_fields = $form->fields;
				if ( !empty( $_fields ) ) {
					foreach ( $_fields as $field ) {
						if ( empty( $field['name'] ) ) {
							continue;
						}
						if ( isset( $field['preset'] ) && $field['preset'] == 1 ) {
							$data['preset'][$field['uuid']] = $field;
						} else {
							$data['custom'][$field['uuid']] = $field;
						}
					}
				}
			}
		}

		return $data;
	}

	/**
	 * @param string|int|null $directory
	 *
	 * @return array
	 */
	public static function getDirectoryCustomFields( $directory = '' ) {
		$fields = self::getDirectoryData( $directory );

		return $fields[FBField::CUSTOM];
	}


	/**
	 * @param string $type enum[ 'id', 'uuid']
	 * @param string $value
	 * @param string|integer $directory
	 *
	 * @return mixed|null
	 */
	public function getSectionBy( string $type, string $value, $directory = 'all' ) {
		$type = in_array( $type, [ 'uuid', 'id' ] ) ? $type : 'uuid';
		if ( empty( $value ) ) {
			return null;
		}
		$data = self::getDirectoryData( $directory );
		if ( empty( $data[FBField::SECTIONS] ) ) {
			return null;
		}
		$sections = $data[FBField::SECTIONS];
		if ( 'uuid' === $type ) {
			return !empty( $sections[$value] ) ? $sections[$value] : null;
		}

		foreach ( $sections as $section ) {
			if ( !empty( $section[$type] ) && $section[$type] === $value ) {
				return $section;
			}
		}

		return null;
	}


	/**
	 * @param string $type enum[ 'name', 'uuid', 'element','id']
	 * @param string $value
	 * @param string|integer $directory
	 *
	 * @return mixed|null
	 */
	public static function getFieldBy( string $type, string $value, $directory = 'all' ) {
		$type = in_array( $type, [ 'name', 'uuid', 'element', 'id' ] ) ? $type : 'uuid';
		if ( empty( $value ) ) {
			return null;
		}
		$data = self::getDirectoryData( $directory );
		if ( empty( $data[FBField::PRESET] ) && empty( $data[FBField::CUSTOM] ) ) {
			return null;
		}

		if ( 'uuid' === $type ) {
			if ( !empty( $data[FBField::PRESET][$value] ) ) {
				return $data[FBField::PRESET][$value];
			}
			if ( !empty( $data[FBField::CUSTOM][$value] ) ) {
				return $data[FBField::CUSTOM][$value];
			}
		}

		if ( !empty( $data[FBField::PRESET] ) ) {
			foreach ( $data[FBField::PRESET] as $field ) {
				if ( !empty( $field[$type] ) && $field[$type] === $value ) {
					return $field;
				}
			}
		}

		if ( !empty( $data[FBField::CUSTOM] ) ) {
			foreach ( $data[FBField::CUSTOM] as $field ) {
				if ( !empty( $field[$type] ) && $field[$type] === $value ) {
					return $field;
				}
			}
		}

		return null;
	}

	/**
	 * @param string $type enum[ 'name', 'uuid', 'element','id']
	 * @param string $value
	 * @param array $data
	 *
	 * @return mixed|null
	 */
	public static function getFieldByFromGivenDirectoryData( string $type, string $value, $data = [] ) {
		$type = in_array( $type, [ 'name', 'uuid', 'element', 'id' ] ) ? $type : 'uuid';
		if ( empty( $value ) ) {
			return null;
		}

		if ( empty( $data[FBField::PRESET] ) && empty( $data[FBField::CUSTOM] ) ) {
			return null;
		}

		if ( 'uuid' === $type ) {
			if ( !empty( $data[FBField::PRESET][$value] ) ) {
				return $data[FBField::PRESET][$value];
			}
			if ( !empty( $data[FBField::CUSTOM][$value] ) ) {
				return $data[FBField::CUSTOM][$value];
			}
		}

		if ( !empty( $data[FBField::PRESET] ) ) {
			foreach ( $data[FBField::PRESET] as $field ) {
				if ( !empty( $field[$type] ) && $field[$type] === $value ) {
					return $field;
				}
			}
		}

		if ( !empty( $data[FBField::CUSTOM] ) ) {
			foreach ( $data[FBField::CUSTOM] as $field ) {
				if ( !empty( $field[$type] ) && $field[$type] === $value ) {
					return $field;
				}
			}
		}

		return null;
	}

	/**
	 * @param int $listing_id
	 * @param string $type '','custom', 'preset'
	 *
	 * @return array
	 */
	public static function getFromData( int $listing_id = 0, string $type = '' ): array {
		$data = [
			'preset'   => [],
			'custom'   => [],
			'sections' => []
		];

		$form_id = $listing_id ? absint( get_post_meta( $listing_id, '_rtcl_form_id', true ) ) : 0;
		if ( !empty( $form_id ) ) {
			$form = Form::query()->select( 'fields,sections,id,title,slug' )->find( $form_id )->toArray();
		} else {
			$form = Form::query()->select( 'fields,sections,id,title,slug' )->find( 1, 'default' )->toArray();
		}

		if ( empty( $form ) || empty( $form['fields'] ) ) {
			return $data;
		}
		$data = $form['fields'];

		foreach ( $data as $fieldId => $field ) {
			$name = !empty( $field['name'] ) ? $field['name'] : '';
			if ( empty( $name ) ) {
				continue;
			}
			if ( isset( $field['preset'] ) && $field['preset'] == 1 ) {
				$data['preset'][$name] = $field;
			} else {
				$data['custom'][$name] = $field;
			}
		}

		if ( in_array( $type, [ 'custom', 'preset' ] ) ) {
			return $data[$type];
		}

		return $data;
	}

	static function getFormCustomFields() {
		$data = self::getFromData();

		return $data['custom'];
	}

	/**
	 * @param $uuid
	 * @param $listing_id
	 * @param $form_id
	 *
	 * @return mixed|null
	 */
	static function getFormFieldByUuid( $uuid, $listing_id = null, $form_id = null ) {
		$form_id = $listing_id ? absint( get_post_meta( $listing_id, '_rtcl_form_id', true ) ) : absint( $form_id );
		if ( !empty( $form_id ) ) {
			$form = Form::query()->find( $form_id )->toArray();
		} else {
			$form = Form::query()->find( 1, 'default' )->toArray();
		}

		if ( empty( $form ) || empty( $form['fields'] ) ) {
			return null;
		}

		return !empty( $form['fields'][$uuid] ) ? $form['fields'][$uuid] : null;
	}

	/**
	 * @return array
	 */
	public static function getBackEndi18nOptions() {
		$options = [];

		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$rawLanguages = apply_filters( 'wpml_active_languages', null, [ 'skip_missing' => 0 ] );
			global $sitepress;
			if ( !empty( $rawLanguages ) ) {
				$default_lang = apply_filters( 'wpml_default_language', null );
				$languages = [];
				foreach ( $rawLanguages as $language ) {
					$code = $language['code'];
					$lng = $sitepress->get_language_details( $code );
					$languages[$code] = [
						'default'          => $default_lang === $code,
						'code'             => $code,
						'display_name'     => $lng['display_name'],
						'native_name'      => $language['native_name'],
						'country_flag_url' => $language['country_flag_url'] ?? '',
						'translated_name'  => $language['translated_name'] ?? '',
						'default_locale'   => $language['default_locale'] ?? '',
					];
				}
				$options['languages'] = $languages;
				$options['default'] = $default_lang;
			}
		}
		if ( !empty( $options ) ) {
			$options['fields'] = AvailableFields::translatableFields();
			$options['formFields'] = AvailableFields::translatableFormFields();
		}

		return apply_filters( 'rtcl_fb_back_end_i18n_options', $options );
	}

	/**
	 * @return array
	 */
	public static function getFrontEndi18nOptions() {
		$options = [];

		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$options = [
				'default' => apply_filters( 'wpml_default_language', null ),
				'current' => apply_filters( 'wpml_current_language', null )
			];
		}

		return apply_filters( 'rtcl_fb_front_end_i18n_options', $options );
	}


	public static function getOption( $key = '', $default = null ) {
		$options = get_option( 'rtcl_fb_options', [] );

		if ( !$key ) {
			return $options;
		}

		return $options[$key] ?? $default;
	}

	/**
	 * @return array|array[]
	 */
	public static function getWeekDays() {
		$days = Options::get_week_days();

		$updatedDays = [];
		foreach ( $days as $dayIndex => $day ) {
			$updatedDays[] = [
				'day'      => $day,
				'dayIndex' => $dayIndex
			];
		}

		return $updatedDays;
	}

	/**
	 * @param array | string| null $value
	 * @param FBField $field
	 *
	 * @return mixed
	 */
	public static function getFormattedFieldHtml( $value, FBField $field ) {
		$html = is_array( $value ) ? '' : $value;
		if ( $field->getElement() === 'color_picker' ) {
			$html = sprintf( '<span class="cfp-color rtcl-slf-color" style="background-color: %s;"></span>', esc_attr( $value ) );
		} elseif ( in_array( $field->getElement(), [ 'select', 'radio', 'checkbox' ] ) ) {
			$options = $field->getOptions();
			$enable_icon_class = $field->getData( 'enable_icon_class', false );
			if ( $field->getElement() === 'checkbox' ) {
				if ( is_array( $value ) && !empty( $value ) ) {
					$items = [];
					foreach ( $options as $option ) {
						if ( !empty( $option['value'] ) && in_array( $option['value'], $value ) ) {
							$items[] = sprintf( '<span class="rtcl-cfp-vi %s">%s%s</span>',
								!empty( $option['icon_class'] ) && $enable_icon_class ? 'has-icon' : 'no-icon',
								!empty( $option['icon_class'] ) && $enable_icon_class ? '<i class="' . esc_attr( $option['icon_class'] ) . '"></i>' : '',
								esc_html( $option['label'] ) );

						}
					}
					$html = !empty( $items ) ? implode( '<span class="delimiter">,</span>', $items ) : '';
				}
			} else {
				foreach ( $options as $option ) {
					if ( !empty( $option['value'] ) && $option['value'] == $value ) {
						$html = sprintf( '<span class="rtcl-cfp-vi">%s%s</span>',
							!empty( $option['icon_class'] ) && $enable_icon_class ? '<i class="' . esc_attr( $option['icon_class'] ) . '"></i>' : '',
							esc_html( $option['label'] ) );
						break;
					}
				}
			}
		} elseif ( $field->getElement() === 'textarea' ) {
			if ( 'textarea' === $field->getData( 'editor_type', 'textarea' ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$html = nl2br( wp_strip_all_tags( $value ) );
			} else {
				// wp_editor content: balance unclosed tags and apply wpautop for proper rendering
				$html = wpautop( force_balance_tags( $value ) );
			}
		} elseif ( $field->getElement() === 'file' ) {
			if ( !empty( $value ) && is_array( $value ) ) {
				$html = '';
				foreach ( $value as $file ) {
					if ( empty( $file['mime_type'] ) ) {
						continue;
					}
					$mime_type = $file['mime_type'];

					if ( str_starts_with( $mime_type, 'image/' ) ) {
						$thumbnailUrl = wp_get_attachment_image_url( $file['uid'] );
						if ( $thumbnailUrl ) {
							$html .= sprintf( '<div class="rtcl-file-item rtcl-file-item-image" data-type="%s"><a href="%s" target="_blank"><img class="" src="%s" alt="%s"/></a></div>',
								esc_attr( $mime_type ), esc_url( $file['url'] ), esc_url( $thumbnailUrl ), esc_html( $file['name'] ) );
						}
					} elseif ( str_starts_with( $mime_type, 'audio/' ) ) {
						$html .= sprintf( '<div class="rtcl-file-item  rtcl-file-item-audio" data-type="%s"><i class="rtcl-icon rtcl-icon-music"></i><a href="%s" target="_blank">%s</a></div>',
							esc_attr( $mime_type ), esc_url( $file['url'] ), esc_html( $file['name'] ) );
					} elseif ( str_starts_with( $mime_type, 'video/' ) ) {
						$html .= sprintf( '<div class="rtcl-file-item rtcl-file-item-video" data-type="%s"><i class="rtcl-icon rtcl-icon-video"></i><a href="%s" target="_blank">%s</a></div>',
							esc_attr( $mime_type ), esc_url( $file['url'] ), esc_html( $file['name'] ) );
					} elseif ( str_starts_with( $mime_type, 'application/' ) ) {
						$ext = explode( '/', $mime_type )[1];
						if ( $ext === 'pdf' ) {
							$iconClass = 'rtcl-icon-file-pdf';
						} elseif ( in_array( $ext, [ 'zip', 'gz', 'gzip', 'rar', '7z' ] ) ) {
							$iconClass = 'rtcl-icon-file-archive';
						} else {
							$iconClass = 'rtcl-icon-doc';
						}
						$html .= sprintf( '<div class="rtcl-file-item" data-type="%s"><i class="rtcl-icon %s"></i><a href="%s" target="_blank">%s</a></div>',
							esc_attr( $mime_type ), esc_attr( $iconClass ), esc_url( $file['url'] ), esc_html( $file['name'] ) );
					} else {
						$html .= sprintf( '<div class="rtcl-file-item rtcl-file-item-attachment" data-type="%s"><i class="rtcl-icon rtcl-icon-attach"></i><a href="%s" target="_blank">%s</a></div>',
							esc_attr( $mime_type ), esc_url( $file['url'] ), esc_html( $file['name'] ) );
					}
				}
				if ( !empty( $html ) ) {
					$html = sprintf( '<div class="rtcl-file-items">%s</div>', $html );
				}
			}
		}

		return apply_filters( 'rtcl_fb_custom_field_value_html', $html, $value, $field );
	}

	public static function generateRandomString(): string {
		return dechex( wp_rand() );
	}
}