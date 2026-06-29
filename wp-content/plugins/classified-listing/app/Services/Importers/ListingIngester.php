<?php

namespace Rtcl\Services\Importers;

use Rtcl\Controllers\Hooks\Filters;
use Rtcl\Helpers\Functions;
use Rtcl\Models\Form\Form;
use Rtcl\Resources\Options;
use Rtcl\Services\FormBuilder\FBHelper;

/**
 * Shared row → listing ingester.
 *
 * Extracted from Ajax\Import::process_listing_data so the CSV importer, the
 * upcoming RSS importer, and the upcoming Google Places importer can all
 * funnel through a single insert/term/meta/gallery pipeline.
 */
class ListingIngester {

	/**
	 * @var array<string,string> field key => human label, used for error messages.
	 */
	private $field_labels;

	public function __construct() {
		$this->field_labels = Functions::get_listings_default_fields() + Functions::get_listings_custom_fields();
	}

	/**
	 * Ingest one CSV-style mapped row into a listing post.
	 *
	 * @param array $row        Map of column index => raw value.
	 * @param array $mapping    Map of column index => listing field key (e.g. 'rtcl_title').
	 * @param int   $row_number 1-based row number, used in error messages.
	 *
	 * @return array {
	 *     @type int|null $post_id Created post ID, or null on failure.
	 *     @type string   $title   Detected row title (used for error context).
	 *     @type string[] $errors  Per-field / per-row error messages.
	 * }
	 */
	public function ingest_csv_row( array $row, array $mapping, int $row_number = 0 ): array {
		$postarr        = [];
		$meta_data      = [];
		$cat_id         = null;
		$loc_id         = null;
		$tag_id         = null;
		$loc_ids        = [];
		$cat_ids        = [];
		$tag_ids        = [];
		$author         = [];
		$row_title      = '';
		$attachment_ids = [];
		$errors         = [];

		foreach ( $row as $field => $data ) {
			if ( ! isset( $mapping[ $field ] ) || '' === $mapping[ $field ] ) {
				continue;
			}
			$key         = $mapping[ $field ];
			$field_label = $this->field_labels[ $key ] ?? $key;

			try {
				switch ( $key ) {
					case 'rtcl_title':
						$postarr['post_title'] = $data;
						$row_title             = $data;
						break;
					case 'rtcl_content':
						$postarr['post_content'] = $data;
						break;
					case 'rtcl_excerpt':
						$postarr['post_excerpt'] = $data;
						break;
					case 'post_date':
						$postarr['post_date'] = $data;
						break;
					case 'post_author':
						$postarr['post_author'] = $data;
						$author[ $key ]         = $data;
						break;
					case 'post_author_fname':
					case 'post_author_lname':
					case 'post_author_uname':
					case 'post_author_display_name':
					case 'post_author_email':
					case 'post_author_role':
						$author[ $key ] = $data;
						break;
					case 'rtcl_listing_status':
						$postarr['post_status'] = $data;
						break;
					case 'rtcl_gallery':
						$attachment_ids = [];
						if ( ! empty( $data ) ) {
							$attachment_ids = $this->rtcl_process_image( $data );
						}
						break;
					case 'rtcl_tax_category':
						$name = trim( $data );
						if ( $name ) {
							$terms  = explode( '>', $name );
							$limit  = apply_filters( 'rtcl_import_terms_hierarchy_limit', 3 );
							$parent = 0;
							if ( ! empty( $terms ) ) {
								foreach ( $terms as $index => $slug ) {
									if ( $limit === $index ) {
										break;
									}

									$check_term = term_exists( $slug, rtcl()->category );

									if ( ! $check_term ) {
										$cat_id    = wp_insert_term(
											$slug,
											rtcl()->category,
											[
												'slug'   => sanitize_title( $slug ),
												'parent' => $parent,
											],
										);
										$cat_ids[] = absint( $cat_id['term_id'] );
									} else {
										$cat_id          = $check_term;
										$existing_parent = $cat_id['term_id'];
										while ( $existing_parent ) {
											$cat_ids[]       = absint( $existing_parent );
											$existing_term   = get_term_by( 'ID', $existing_parent, rtcl()->category );
											$existing_parent = $existing_term->parent;
										}
									}

									$parent = $cat_id['term_id'] ?? 0;
								}
							}
						}
						break;
					case 'rtcl_tax_location':
						$name = trim( $data );
						if ( $name ) {
							$terms  = explode( '>', $name );
							$limit  = apply_filters( 'rtcl_import_terms_hierarchy_limit', 3 );
							$parent = 0;
							if ( ! empty( $terms ) ) {
								foreach ( $terms as $index => $slug ) {
									if ( $limit === $index ) {
										break;
									}

									$check_term = term_exists( $slug, rtcl()->location );

									if ( ! $check_term ) {
										$loc_id    = wp_insert_term(
											$slug,
											rtcl()->location,
											[
												'slug'   => sanitize_title( $slug ),
												'parent' => $parent,
											],
										);
										$loc_ids[] = absint( $loc_id['term_id'] );
									} else {
										$loc_id          = $check_term;
										$existing_parent = $loc_id['term_id'];
										while ( $existing_parent ) {
											$loc_ids[]       = absint( $existing_parent );
											$existing_term   = get_term_by( 'ID', $existing_parent, rtcl()->location );
											$existing_parent = $existing_term->parent;
										}
									}

									$parent = $loc_id['term_id'] ?? 0;
								}
							}
						}
						break;
					case 'rtcl_tax_tags':
						if ( ! empty( $data ) ) {
							$name  = trim( $data );
							$terms = explode( ',', $name );

							if ( ! empty( $terms ) ) {
								foreach ( $terms as $index => $name ) {
									$check_term = term_exists( $name, rtcl()->tag );

									if ( ! $check_term ) {
										$tag_id = wp_insert_term(
											$name,
											rtcl()->tag,
											[
												'slug' => sanitize_title( $name ),
											],
										);
										if ( ! is_wp_error( $tag_id ) ) {
											$tag_ids[] = absint( $tag_id['term_id'] );
										}
									} else {
										$tag_id    = $check_term;
										$tag_ids[] = absint( $tag_id['term_id'] );
									}
								}
							}
						}

						break;
					case '_rtcl_video_urls':
						if ( ! empty( $data ) ) {
							$urls              = explode( ',', $data );
							$meta_data[ $key ] = $urls;
						}
						break;
					case '_rtcl_social_profiles':
						if ( ! empty( $data ) ) {
							$socials      = [];
							$all_profiles = explode( ',', $data );

							$social_profile_list = array_keys( Options::get_social_profiles_list() );

							if ( is_array( $all_profiles ) ) {
								foreach ( $all_profiles as $profile ) {
									$social_profile = explode( '|', trim( $profile ) );

									$social_key = isset( $social_profile[0] ) ? trim( $social_profile[0] ) : '';
									$social_url = isset( $social_profile[1] ) ? trim( $social_profile[1] ) : '';
									$social_url = $social_url && filter_var( $social_url, FILTER_VALIDATE_URL ) ? $social_url : '';

									if ( $social_key && $social_url && in_array( $social_key, $social_profile_list ) ) {
										$socials[ $social_key ] = $social_url;
									}
								}
							}

							if ( ! empty( $socials ) ) {
								$meta_data[ $key ] = $socials;
							}
						}
						break;
					case '_rtcl_bhs':
						if ( ! empty( $data ) ) {
							$parsed_bhs = self::parse_business_hours_import( $data );
							if ( ! empty( $parsed_bhs['active'] ) ) {
								$fb_enabled   = class_exists( FBHelper::class ) && FBHelper::isEnabled();
								$default_form = $fb_enabled ? FBHelper::getDefaultForm() : null;
								if ( $fb_enabled && $default_form ) {
									// Save in new format and assign default form for proper display.
									$meta_data[ $key ] = $parsed_bhs;
									if ( ! isset( $meta_data['_rtcl_form_id'] ) ) {
										$meta_data['_rtcl_form_id'] = $default_form->id;
									}
								} else {
									// Save in old format (days indexed 0-6 at root level).
									if ( ! empty( $parsed_bhs['type'] ) && 'selective' === $parsed_bhs['type'] ) {
										$meta_data[ $key ] = ! empty( $parsed_bhs['days'] ) ? $parsed_bhs['days'] : [];
									} else {
										// Open 24/7 - set all days as open.
										$all_open = [];
										for ( $i = 0; $i <= 6; $i++ ) {
											$all_open[ $i ] = [ 'open' => true ];
										}
										$meta_data[ $key ] = $all_open;
									}
									if ( ! empty( $parsed_bhs['special'] ) ) {
										$meta_data['_rtcl_special_bhs'] = $parsed_bhs['special'];
									}
								}
							}
						}
						break;
					case strpos( $key, 'repeater_' ) === 0:
						if ( ! empty( $data ) ) {
							$repeater_data = $this->parse_repeater_meta_data( $data );
							if ( ! empty( $repeater_data ) ) {
								$meta_data[ $key ] = $repeater_data;
							}
						}
						break;
					default:
						if ( ! empty( $data ) ) {
							$meta_data[ $key ] = $data;
						}
				}
			} catch ( \Exception $e ) {
				$row_identifier = $row_title ? $row_title : '#' . $row_number;
				/* translators: 1: Row identifier, 2: Field label, 3: Error message */
				$errors[] = sprintf(
					__( 'Row "%1$s": Field "%2$s" - %3$s', 'classified-listing' ),
					$row_identifier,
					$field_label,
					$e->getMessage()
				);
			}
		}

		if ( empty( $postarr ) ) {
			$row_identifier = $row_title ? $row_title : '#' . $row_number;
			/* translators: %s: Row identifier */
			$errors[] = sprintf( __( 'Row "%s": No valid data found to create listing.', 'classified-listing' ), $row_identifier );
			return [ 'post_id' => null, 'title' => $row_title, 'errors' => $errors ];
		}

		$postarr['post_type'] = rtcl()->post_type;

		if ( ! empty( $author ) && ! empty( $author['post_author_email'] ) ) {
			$user_id = email_exists( $author['post_author_email'] );
			if ( isset( $author['post_author_uname'] ) && ! username_exists( $author['post_author_uname'] ) ) {
				$user_name = $author['post_author_uname'];
			} else {
				$part_of_email = explode( '@', $author['post_author_email'] );
				$user_name     = username_exists( $part_of_email[0] ) ? $author['post_author_email'] : $part_of_email[0];
			}
			if ( ! $user_id ) {
				$password      = wp_generate_password();
				$new_user_data = apply_filters(
					'rtcl_import_new_user_data',
					[
						'user_login'   => $user_name,
						'user_pass'    => $password,
						'user_email'   => $author['post_author_email'],
						'first_name'   => $author['post_author_fname'] ?? '',
						'last_name'    => $author['post_author_lname'] ?? '',
						'display_name' => $author['post_author_display_name'] ?? $user_name,
						'role'         => $author['post_author_role'] ?? get_option( 'default_role', 'subscriber' ),
					],
				);
				$customer_id   = wp_insert_user( $new_user_data );
				if ( ! is_wp_error( $customer_id ) ) {
					$user_id = $customer_id;
					if ( Functions::get_option_item( 'rtcl_email_notifications_settings', 'notify_users', 'user_import', 'multi_checkbox' ) ) {
						rtcl()->mailer()->emails['User_Import_Email_To_User']->trigger( $user_id, $new_user_data );
					}
				} else {
					$row_identifier = $row_title ? $row_title : '#' . $row_number;
					/* translators: 1: Row identifier, 2: Error message */
					$errors[] = sprintf(
						__( 'Row "%1$s": Failed to create user - %2$s', 'classified-listing' ),
						$row_identifier,
						$customer_id->get_error_message()
					);
					$user_id = $author['post_author'];
				}
			}
			$postarr['post_author'] = $user_id;
		}

		$post_id = wp_insert_post( $postarr, true );
		if ( is_wp_error( $post_id ) ) {
			$row_identifier = $row_title ? $row_title : '#' . $row_number;
			/* translators: 1: Row identifier, 2: Error message */
			$errors[] = sprintf(
				__( 'Row "%1$s": Failed to create listing - %2$s', 'classified-listing' ),
				$row_identifier,
				$post_id->get_error_message()
			);
			return [ 'post_id' => null, 'title' => $row_title, 'errors' => $errors ];
		}

		if ( ! empty( $meta_data ) ) {
			wp_update_post(
				[
					'ID'         => $post_id,
					'meta_input' => $meta_data,
				],
			);
		}

		if ( ! is_wp_error( $cat_id ) && ! empty( $cat_ids ) ) {
			wp_set_object_terms( $post_id, $cat_ids, rtcl()->category );
		}

		if ( ! is_wp_error( $loc_id ) && ! empty( $loc_ids ) ) {
			wp_set_object_terms( $post_id, $loc_ids, rtcl()->location );
		}

		if ( ! is_wp_error( $tag_id ) && ! empty( $tag_ids ) ) {
			wp_set_object_terms( $post_id, $tag_ids, rtcl()->tag );
		}

		if ( ! empty( $attachment_ids ) && is_array( $attachment_ids ) ) {
			$attachment_ids = array_map( 'intval', $attachment_ids );
			$attachment_ids = array_filter( $attachment_ids );
			set_post_thumbnail( $post_id, $attachment_ids[0] );
			foreach ( $attachment_ids as $attachment_id ) {
				wp_update_post(
					[
						'ID'          => $attachment_id,
						'post_parent' => $post_id,
					],
				);
			}
			update_post_meta( $post_id, '_rtcl_attachments_order', $attachment_ids );
		}

		return [ 'post_id' => $post_id, 'title' => $row_title, 'errors' => $errors ];
	}

	/**
	 * Ingest a NormalizedRow (RSS, Google Places, …) into a listing post.
	 *
	 * Looks up an existing listing by source identity via DedupeResolver and
	 * either updates it (when update_existing=true) or skips. Insert path
	 * mirrors the CSV ingester but reads from the structured NormalizedRow
	 * shape documented in ImporterInterface instead of column-mapped values.
	 *
	 * @param array $row  NormalizedRow.
	 * @param array $opts {
	 *     @type bool           $update_existing  Refresh previously-imported listings (default false).
	 *     @type int            $target_category  Term id used when row.categories is empty.
	 *     @type int            $target_location  Term id used when row.locations is empty.
	 *     @type string         $default_status   'pending' | 'publish' | 'draft' when row.status is empty.
	 *     @type DedupeResolver $dedupe           Resolver to reuse (one is created if omitted).
	 * }
	 *
	 * @return array { post_id: int|null, action: 'inserted'|'updated'|'skipped', errors: string[] }
	 */
	public function ingest_normalized( array $row, array $opts = [] ): array {
		$opts = wp_parse_args( $opts, [
			'update_existing' => false,
			'target_category' => 0,
			'target_location' => 0,
			'default_status'  => 'pending',
			'dedupe'          => null,
		] );

		$source    = (string) ( $row['source'] ?? '' );
		$source_id = (string) ( $row['source_id'] ?? '' );

		if ( '' === $source || '' === $source_id ) {
			return [
				'post_id' => null,
				'action'  => 'skipped',
				'errors'  => [ __( 'Row is missing source / source_id.', 'classified-listing' ) ],
			];
		}

		$title = trim( (string) ( $row['title'] ?? '' ) );
		if ( '' === $title ) {
			return [
				'post_id' => null,
				'action'  => 'skipped',
				'errors'  => [ __( 'Row is missing a title.', 'classified-listing' ) ],
			];
		}

		$dedupe      = $opts['dedupe'] instanceof DedupeResolver ? $opts['dedupe'] : new DedupeResolver();
		$existing_id = $dedupe->find_existing( $source, $source_id );

		if ( $existing_id && ! $opts['update_existing'] ) {
			return [ 'post_id' => $existing_id, 'action' => 'skipped', 'errors' => [] ];
		}

		$errors       = [];
		$final_status = (string) ( $row['status'] ?? '' ) ?: (string) $opts['default_status'];
		$postarr      = [
			'post_title'   => $title,
			'post_content' => (string) ( $row['content'] ?? '' ),
			'post_excerpt' => (string) ( $row['excerpt'] ?? '' ),
			'post_status'  => $final_status,
		];

		if ( ! empty( $row['author_email'] ) ) {
			$user_id = $this->resolve_author( (string) $row['author_email'] );
			if ( is_wp_error( $user_id ) ) {
				$errors[] = $user_id->get_error_message();
			} elseif ( $user_id > 0 ) {
				$postarr['post_author'] = $user_id;
			}
		}

		if ( $existing_id ) {
			$postarr['ID'] = $existing_id;
			$result        = wp_update_post( $postarr, true );
			$action        = 'updated';
		} else {
			// Insert as 'pending' first so the listing is not publicly visible
			// while meta, terms, images, and reviews are still being populated.
			// The final status is applied at the end once all data is ready.
			$postarr['post_type']   = rtcl()->post_type;
			$postarr['post_status'] = 'pending';
			$result                 = wp_insert_post( $postarr, true );
			$action                 = 'inserted';
		}

		if ( is_wp_error( $result ) ) {
			$errors[] = $result->get_error_message();
			return [ 'post_id' => null, 'action' => 'skipped', 'errors' => $errors ];
		}

		$post_id = (int) $result;

		// Business hours need a form id to render once the form builder is
		// active (FBHelper resolves the field from the listing's form). When a
		// row carries _rtcl_bhs but no _rtcl_form_id (e.g. a Google import where
		// no target form was chosen), fall back to the run's selected form, then
		// to the default form — mirroring the CSV import path in process_meta().
		// ImportRunner normally stamps this already; this is a safety net for
		// callers that reach ingest_normalized() directly.

		$form    = ! empty( $opts['form_id'] ) ? Form::query()->find( (int) $opts['form_id'] ) : null;
		$form_id = $form ? (int) $form->id : 0;

		if ( $form_id > 0 ) {
			$row['meta']['_rtcl_form_id'] = $form_id;
		}
		
		if ( ! empty( $row['meta'] ) && is_array( $row['meta'] ) ) {
			foreach ( $row['meta'] as $meta_key => $meta_val ) {
				update_post_meta( $post_id, (string) $meta_key, $meta_val );
			}
		}

		$cat_ids = $this->collect_hierarchy_terms( $row['categories'] ?? [], rtcl()->category );
		if ( empty( $cat_ids ) && (int) $opts['target_category'] > 0 ) {
			$cat_ids = [ (int) $opts['target_category'] ];
		}
		if ( ! empty( $cat_ids ) ) {
			wp_set_object_terms( $post_id, $cat_ids, rtcl()->category );
		}

		$loc_ids = $this->collect_hierarchy_terms( $row['locations'] ?? [], rtcl()->location );
		if ( empty( $loc_ids ) && (int) $opts['target_location'] > 0 ) {
			$loc_ids = [ (int) $opts['target_location'] ];
		}
		if ( ! empty( $loc_ids ) ) {
			wp_set_object_terms( $post_id, $loc_ids, rtcl()->location );
		}

		if ( ! empty( $row['tags'] ) && is_array( $row['tags'] ) ) {
			$tag_ids = [];
			foreach ( $row['tags'] as $tag_name ) {
				$tag_name = trim( (string) $tag_name );
				if ( '' === $tag_name ) {
					continue;
				}
				$check = term_exists( $tag_name, rtcl()->tag );
				if ( ! $check ) {
					$check = wp_insert_term( $tag_name, rtcl()->tag, [ 'slug' => sanitize_title( $tag_name ) ] );
				}
				if ( ! is_wp_error( $check ) && ! empty( $check['term_id'] ) ) {
					$tag_ids[] = absint( $check['term_id'] );
				}
			}
			if ( ! empty( $tag_ids ) ) {
				wp_set_object_terms( $post_id, $tag_ids, rtcl()->tag );
			}
		}

		if ( ! empty( $row['gallery_urls'] ) ) {
			$urls = is_array( $row['gallery_urls'] ) ? $row['gallery_urls'] : explode( ',', (string) $row['gallery_urls'] );
			$urls = array_filter( array_map( 'trim', $urls ) );
			if ( ! empty( $urls ) ) {
				$image_errors  = [];
				$attachment_ids = $this->sideload_image_urls( $urls, $post_id, $image_errors );
				if ( ! empty( $image_errors ) ) {
					$errors = array_merge( $errors, $image_errors );
				}
				if ( ! empty( $attachment_ids ) ) {
					$attachment_ids = array_values( array_filter( array_map( 'intval', $attachment_ids ) ) );
					// Set the featured image on new posts AND when the existing
					// post has no thumbnail yet. Skipping only when a thumbnail
					// already exists (so manual edits aren't churned).
					if ( 'inserted' === $action || ! has_post_thumbnail( $post_id ) ) {
						set_post_thumbnail( $post_id, $attachment_ids[0] );
					}
					foreach ( $attachment_ids as $attachment_id ) {
						wp_update_post( [ 'ID' => $attachment_id, 'post_parent' => $post_id ] );
					}
					update_post_meta( $post_id, '_rtcl_attachments_order', $attachment_ids );
				}
			}
		}

		if ( ! empty( $row['reviews'] ) && is_array( $row['reviews'] ) ) {
			$this->import_reviews_as_comments( $post_id, $row['reviews'] );
		}

		$dedupe->stamp( $post_id, $source, $source_id, (string) ( $row['source_url'] ?? '' ) );

		// All data (meta, terms, images, reviews) is now in place — apply the
		// intended status. For new listings this promotes from 'pending' to the
		// final status (e.g. 'publish') so the listing only becomes visible
		// once it is fully populated.
		if ( 'inserted' === $action && 'pending' !== $final_status ) {
			wp_update_post( [ 'ID' => $post_id, 'post_status' => $final_status ] );
		}

		return [ 'post_id' => $post_id, 'action' => $action, 'errors' => $errors ];
	}

	/**
	 * Store imported reviews as native listing reviews (approved WP comments
	 * with a `rating` meta, exactly like a front-end review submission).
	 *
	 * Idempotent: each comment is stamped with the Google review's resource id
	 * in `_rtcl_google_review_id` meta, and an id already present on the listing
	 * is skipped — so re-importing (update_existing) doesn't duplicate reviews.
	 *
	 * After inserting, the listing's aggregate rating / review count are
	 * recomputed via Comments::clear_transients(), the same recompute the
	 * front-end submission path triggers.
	 *
	 * @param int   $post_id Listing post id.
	 * @param array $reviews List of { review_id, author, author_url, rating, text, time }.
	 */
	private function import_reviews_as_comments( int $post_id, array $reviews ): void {
		// Existing Google review ids on this listing, to skip on re-import.
		$existing = get_comments( [
			'post_id'     => $post_id,
			'status'      => 'all',
			'meta_key'    => '_rtcl_google_review_id',
			'meta_compare' => 'EXISTS',
			'fields'      => 'ids',
		] );
		$seen = [];
		foreach ( (array) $existing as $existing_id ) {
			$rid = get_comment_meta( (int) $existing_id, '_rtcl_google_review_id', true );
			if ( $rid ) {
				$seen[ (string) $rid ] = true;
			}
		}

		$inserted = 0;
		foreach ( $reviews as $review ) {
			if ( ! is_array( $review ) ) {
				continue;
			}
			$text   = trim( (string) ( $review['text'] ?? '' ) );
			$rating = (int) ( $review['rating'] ?? 0 );
			if ( '' === $text && $rating <= 0 ) {
				continue;
			}

			$review_id = (string) ( $review['review_id'] ?? '' );
			if ( '' !== $review_id && isset( $seen[ $review_id ] ) ) {
				continue; // Already imported.
			}

			$author = sanitize_text_field( (string) ( $review['author'] ?? '' ) );
			if ( '' === $author ) {
				$author = __( 'Google user', 'classified-listing' );
			}

			$commentarr = [
				'comment_post_ID'      => $post_id,
				'comment_author'       => $author,
				'comment_author_email' => '',
				'comment_author_url'   => esc_url_raw( (string) ( $review['author_url'] ?? '' ) ),
				'comment_content'      => $text,
				'comment_type'         => '', // Matches native listing reviews.
				'comment_parent'       => 0,
				'comment_approved'     => 1,
			];

			// Preserve the original review date when Google provides one.
			$ts = ! empty( $review['time'] ) ? strtotime( (string) $review['time'] ) : false;
			if ( $ts ) {
				$commentarr['comment_date']     = gmdate( 'Y-m-d H:i:s', $ts + ( (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) );
				$commentarr['comment_date_gmt'] = gmdate( 'Y-m-d H:i:s', $ts );
			}

			$comment_id = wp_insert_comment( $commentarr );
			if ( ! $comment_id ) {
				continue;
			}

			if ( $rating > 0 ) {
				add_comment_meta( $comment_id, 'rating', $rating, true );
			}
			if ( '' !== $review_id ) {
				add_comment_meta( $comment_id, '_rtcl_google_review_id', $review_id, true );
				$seen[ $review_id ] = true;
			}
			$inserted++;
		}

		// Recompute the listing's aggregate rating the same way a real review
		// submission does — from the actual approved comments now on the post.
		// This keeps the headline average, the total count, and the per-star
		// breakdown consistent with the reviews that were stored (e.g. ratings
		// 5,5,5,5,3 → average 4.60, 5 reviews, [5 => 4, 3 => 1]). It overrides
		// the Google-aggregate fallback the importer set for the no-reviews case.
		if ( $inserted > 0 && class_exists( '\Rtcl\Controllers\Hooks\Comments' ) ) {
			\Rtcl\Controllers\Hooks\Comments::clear_transients( $post_id );
		}
	}

	/**
	 * Resolve term ids for an array of hierarchy strings like ["Parent > Child", "Other"].
	 *
	 * Creates missing terms, walks ancestors for existing leaves so the listing
	 * is attached to every level. Mirrors the behavior of the CSV path's
	 * rtcl_tax_category / rtcl_tax_location handling.
	 *
	 * @param array  $hierarchies Array of strings or comma-separated single string.
	 * @param string $taxonomy
	 *
	 * @return int[] Deduplicated term ids.
	 */
	private function collect_hierarchy_terms( $hierarchies, string $taxonomy ): array {
		if ( empty( $hierarchies ) ) {
			return [];
		}
		if ( ! is_array( $hierarchies ) ) {
			$hierarchies = [ (string) $hierarchies ];
		}

		$limit  = apply_filters( 'rtcl_import_terms_hierarchy_limit', 3 );
		$result = [];

		foreach ( $hierarchies as $hierarchy ) {
			$hierarchy = trim( (string) $hierarchy );
			if ( '' === $hierarchy ) {
				continue;
			}

			$slugs  = explode( '>', $hierarchy );
			$parent = 0;
			foreach ( $slugs as $index => $slug ) {
				if ( $limit === $index ) {
					break;
				}
				$slug  = trim( $slug );
				if ( '' === $slug ) {
					continue;
				}
				$check = term_exists( $slug, $taxonomy );
				if ( ! $check ) {
					$check = wp_insert_term( $slug, $taxonomy, [
						'slug'   => sanitize_title( $slug ),
						'parent' => $parent,
					] );
					if ( is_wp_error( $check ) || empty( $check['term_id'] ) ) {
						continue;
					}
					$result[] = absint( $check['term_id'] );
				} else {
					// Existing leaf — walk up the ancestor chain so all levels stay attached.
					$ancestor = (int) $check['term_id'];
					while ( $ancestor ) {
						$result[] = $ancestor;
						$term     = get_term_by( 'ID', $ancestor, $taxonomy );
						$ancestor = $term && ! is_wp_error( $term ) ? (int) $term->parent : 0;
					}
				}
				$parent = (int) ( $check['term_id'] ?? 0 );
			}
		}

		return array_values( array_unique( array_filter( $result ) ) );
	}

	/**
	 * Resolve (or create) a WP user for the given email. Returns user id or WP_Error.
	 */
	private function resolve_author( string $email ) {
		$existing = email_exists( $email );
		if ( $existing ) {
			return (int) $existing;
		}

		$part = explode( '@', $email );
		$name = $part[0] ?? $email;
		if ( username_exists( $name ) ) {
			$name = $email;
		}

		$new_user_data = apply_filters( 'rtcl_import_new_user_data', [
			'user_login'   => $name,
			'user_pass'    => wp_generate_password(),
			'user_email'   => $email,
			'display_name' => $name,
			'role'         => get_option( 'default_role', 'subscriber' ),
		] );

		$created = wp_insert_user( $new_user_data );
		if ( is_wp_error( $created ) ) {
			return $created;
		}
		return (int) $created;
	}

	/**
	 * Sideload a comma-separated list of image URLs and return the attachment IDs.
	 *
	 * Kept for the legacy CSV path which expects a comma-joined string. Errors
	 * are silently dropped here (matches pre-Phase-5 behavior). For the
	 * NormalizedRow path use sideload_image_urls() which surfaces errors.
	 *
	 * @param string $data    Comma-separated image URLs.
	 * @param int    $post_id Optional parent post id; usually 0 since the parent is set after wp_insert_post.
	 *
	 * @return int[]
	 */
	private function rtcl_process_image( $data, $post_id = 0 ) {
		$urls = array_filter( array_map( 'trim', explode( ',', (string) $data ) ) );
		$tmp  = [];
		return $this->sideload_image_urls( $urls, $post_id, $tmp );
	}

	/**
	 * Sideload an array of image URLs into the media library. Per-URL failures
	 * are captured into the &$errors array so the caller (typically
	 * ingest_normalized) can surface them in the run's error list.
	 *
	 * @param string[]  $urls
	 * @param int       $post_id
	 * @param string[] &$errors  Mutated: human-readable error messages appended.
	 *
	 * @return int[]  Attachment IDs in original order (failures skipped).
	 */
	private function sideload_image_urls( array $urls, int $post_id = 0, array &$errors = [] ): array {
		$gallery = [];
		foreach ( $urls as $image_url ) {
			$image_url = trim( (string) $image_url );
			if ( '' === $image_url ) {
				continue;
			}
			$image_title   = preg_replace( '/\.[^.]+$/', '', basename( $image_url ) );
			$attachment_id = $this->upload_image( $image_url, $image_title, $post_id );
			if ( is_wp_error( $attachment_id ) ) {
				$errors[] = sprintf(
					/* translators: 1: image URL, 2: error message */
					__( 'Image sideload failed for %1$s — %2$s', 'classified-listing' ),
					$image_url,
					$attachment_id->get_error_message()
				);
				continue;
			}
			$gallery[] = (int) $attachment_id;
		}
		return $gallery;
	}

	/**
	 * Sideload a single image URL into the media library.
	 *
	 * We bypass media_sideload_image because it rejects URLs that do not match
	 * a literal `\.(jpe?g|gif|png|webp)\b` regex — and Google Places photo
	 * URLs (`/v1/places/.../photos/.../media?...`) have no file extension.
	 * Instead we download to a temp file, detect the mime type from the
	 * downloaded bytes via wp_get_image_mime(), manufacture a filename with
	 * the correct extension, and hand off to media_handle_sideload.
	 *
	 * Filters::beforeUpload/afterUpload still wrap the call so the plugin's
	 * upload-time hooks (e.g. mime filtering) apply during import.
	 *
	 * @param string $image_url
	 * @param string $image_title
	 * @param int    $post_id
	 *
	 * @return int|\WP_Error Attachment ID or WP_Error on failure.
	 */
	private function upload_image( $image_url, $image_title, $post_id = 0 ) {
		set_time_limit( 150 );
		wp_raise_memory_limit( 'image' );

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		Filters::beforeUpload();
		try {
			// download_url follows redirects (handles Google's 302 to the CDN).
			$tmp_file = download_url( $image_url, 30 );
			if ( is_wp_error( $tmp_file ) ) {
				return $tmp_file;
			}

			// Determine extension. Prefer the URL's existing extension; fall
			// back to mime-detection on the downloaded bytes for extensionless
			// URLs like the Google Places photo media endpoint.
			$ext = '';
			$url_path = (string) wp_parse_url( $image_url, PHP_URL_PATH );
			if ( $url_path && preg_match( '/\.(jpe?g|jpe|gif|png|webp)$/i', $url_path, $m ) ) {
				$ext = strtolower( $m[1] === 'jpe' ? 'jpg' : $m[1] );
			} else {
				$mime = wp_get_image_mime( $tmp_file );
				$mime_to_ext = [
					'image/jpeg' => 'jpg',
					'image/png'  => 'png',
					'image/gif'  => 'gif',
					'image/webp' => 'webp',
				];
				$ext = $mime_to_ext[ $mime ] ?? '';
			}

			if ( '' === $ext ) {
				@unlink( $tmp_file );
				return new \WP_Error( 'rtcl_image_unknown_type', __( 'Could not determine image type for the downloaded file.', 'classified-listing' ) );
			}

			// Pick a clean filename. Prefer the URL's basename, fall back to the
			// provided title, and ensure the extension matches what we detected.
			$basename = $url_path ? basename( $url_path ) : '';
			$stem     = $basename ? preg_replace( '/\.[^.]+$/', '', $basename ) : '';
			if ( '' === $stem ) {
				$stem = $image_title ?: 'image-' . substr( md5( $image_url ), 0, 8 );
			}
			$stem     = sanitize_file_name( $stem );
			$filename = $stem . '.' . $ext;

			$file_array = [
				'name'     => $filename,
				'tmp_name' => $tmp_file,
			];

			$attachment_id = media_handle_sideload( $file_array, $post_id, $image_title );

			// media_handle_sideload removes the tmp file on success, but on
			// error the file may still exist — clean up defensively.
			if ( is_wp_error( $attachment_id ) && file_exists( $tmp_file ) ) {
				@unlink( $tmp_file );
			}

			return $attachment_id;
		} finally {
			Filters::afterUpload();
		}
	}

	/**
	 * Parse a repeater field's CSV-encoded value into an array of sub-rows.
	 *
	 * Encoding: row separator is ",", key/value separator is "|", and key:value pairs use ":".
	 * Example: "label:Foo|value:1, label:Bar|value:2"
	 */
	private function parse_repeater_meta_data( string $value ) {
		$result = [];

		// Split repeater rows
		$rows = array_filter( array_map( 'trim', explode( ',', $value ) ) );
		if ( ! empty( $rows ) ) {
			foreach ( $rows as $row ) {
				$item = [];

				// Split key:value pairs
				$pairs = array_filter( array_map( 'trim', explode( '|', $row ) ) );

				foreach ( $pairs as $pair ) {
					if ( strpos( $pair, ':' ) === false ) {
						continue;
					}

					[ $key, $val ] = array_map( 'trim', explode( ':', $pair, 2 ) );

					if ( $key !== '' ) {
						$item[ $key ] = $val;
					}
				}

				if ( ! empty( $item ) ) {
					$result[] = $item;
				}
			}
		}

		return $result;
	}

	/**
	 * Parse formatted business hours text back into _rtcl_bhs meta array.
	 *
	 * Expected format:
	 * Status: Active | Type: Selective
	 * Monday: 09:00-17:00, 13:00-14:00
	 * Tuesday: Closed
	 * Special: 2024-12-25 (Once): Closed; 2024-12-31 (Repeat): 09:00-13:00
	 *
	 * Or: Status: Active | Type: Open 24/7
	 *
	 * @param string $data Formatted business hours string.
	 *
	 * @return array
	 */
	private static function parse_business_hours_import( $data ) {
		$bhs   = [];
		$lines = preg_split( '/\r\n|\r|\n/', trim( $data ) );

		if ( empty( $lines ) ) {
			return $bhs;
		}

		$day_map = [
			'sunday'    => 0,
			'monday'    => 1,
			'tuesday'   => 2,
			'wednesday' => 3,
			'thursday'  => 4,
			'friday'    => 5,
			'saturday'  => 6,
		];

		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}

			// Parse "Status: Active | Type: Selective" line
			if ( stripos( $line, 'Status:' ) === 0 ) {
				$bhs['active'] = stripos( $line, 'Active' ) !== false;
				if ( stripos( $line, 'Selective' ) !== false ) {
					$bhs['type'] = 'selective';
				} else {
					$bhs['type'] = 247;
				}
				continue;
			}

			// Parse "Special: ..." line
			if ( stripos( $line, 'Special:' ) === 0 ) {
				$special_str = trim( substr( $line, 8 ) );
				$entries     = array_map( 'trim', explode( ';', $special_str ) );
				$special     = [];
				foreach ( $entries as $entry ) {
					if ( preg_match( '/^(\d{4}-\d{2}-\d{2})\s*\((\w+)\):\s*(.+)$/', $entry, $m ) ) {
						$sbh = [
							'date'  => $m[1],
							'occur' => strtolower( $m[2] ) === 'once' ? 'once' : 'repeat',
						];
						$hours = trim( $m[3] );
						if ( strtolower( $hours ) === 'closed' ) {
							$sbh['open'] = false;
						} elseif ( strtolower( $hours ) === 'open 24 hours' ) {
							$sbh['open'] = true;
						} else {
							$sbh['open'] = true;
							$time_parts  = array_map( 'trim', explode( ',', $hours ) );
							$times       = [];
							foreach ( $time_parts as $range ) {
								$parts = array_map( 'trim', explode( '-', $range, 2 ) );
								if ( count( $parts ) === 2 && $parts[0] && $parts[1] ) {
									$times[] = [ 'start' => $parts[0], 'end' => $parts[1] ];
								}
							}
							if ( ! empty( $times ) ) {
								$sbh['times'] = $times;
							}
						}
						$special[] = $sbh;
					}
				}
				if ( ! empty( $special ) ) {
					$bhs['special'] = $special;
				}
				continue;
			}

			// Parse day lines like "Monday: 09:00-17:00, 13:00-14:00"
			if ( preg_match( '/^(\w+):\s*(.+)$/', $line, $m ) ) {
				$day_name = strtolower( $m[1] );
				if ( isset( $day_map[ $day_name ] ) ) {
					$day_index = $day_map[ $day_name ];
					$hours     = trim( $m[2] );

					if ( ! isset( $bhs['days'] ) ) {
						$bhs['days'] = [];
					}

					if ( strtolower( $hours ) === 'closed' ) {
						$bhs['days'][ $day_index ] = [ 'open' => false ];
					} elseif ( strtolower( $hours ) === 'open 24 hours' ) {
						$bhs['days'][ $day_index ] = [ 'open' => true ];
					} else {
						$time_parts = array_map( 'trim', explode( ',', $hours ) );
						$times      = [];
						foreach ( $time_parts as $range ) {
							$parts = array_map( 'trim', explode( '-', $range, 2 ) );
							if ( count( $parts ) === 2 && $parts[0] && $parts[1] ) {
								$times[] = [ 'start' => $parts[0], 'end' => $parts[1] ];
							}
						}
						$bhs['days'][ $day_index ] = [
							'open'  => true,
							'times' => ! empty( $times ) ? $times : [],
						];
					}
				}
			}
		}

		return $bhs;
	}
}
