<?php

namespace Rtcl\Controllers\Admin;

use Rtcl\Controllers\SessionHandler;
use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Utility;
use Rtcl\Models\Form\Form;
use WP_Query;

class Cron {

	function __construct() {
		add_action( 'rtcl_hourly_scheduled_events', [ $this, 'hourly_scheduled_events' ] );
		add_action( 'rtcl_daily_scheduled_events', [ $this, 'daily_scheduled_events' ] );
		add_action( 'rtcl_cleanup_sessions', [ $this, 'cleanup_session_data' ] );
		add_action( 'rtcl_cleanup_temp_listings', [ $this, 'cleanup_temp_listings' ] );
		add_action( 'rtcl_form_cf_data_migration', [ $this, 'form_cf_data_migration' ] );
	}


	public function form_cf_data_migration() {
		$migration = get_option( 'rtcl_fb_migration_data', [] );
		if ( empty( $migration['active'] ) || empty( $migration['formId'] ) || empty( $migration['fields'] ) ) {
			return;
		}

		$formId = absint( $migration['formId'] );
		$form   = Form::query()->find( $formId );

		if ( empty( $formId ) || empty( $form ) ) {
			return;
		}

		$args = [
			'post_type'      => rtcl()->post_type,
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'post_status'    => 'any',
			'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			                      [
				                      'key'     => '_rtcl_form_id',
				                      'compare' => 'NOT EXISTS'
			                      ]
			]
		];

		$query = new WP_Query( $args );

		if ( ! empty( $query->posts ) ) {
			global $wpdb;
			foreach ( $query->posts as $postId ) {
				// if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
				// $type = apply_filters( 'wpml_element_type', get_post_type( $postId ) );
				// $trid = apply_filters( 'wpml_element_trid', false, $postId, $type );
				// $translations = apply_filters( 'wpml_get_element_translations', [], $trid, $type );
				// $translatedIds = [];
				// foreach ( $translations as $lang => $translation ) {
				// if ( $translation->element_id !== $postId ) {
				// $translatedIds[] = $translation->element_id;
				// }
				// }
				// }

				update_post_meta( $postId, '_rtcl_form_id', $formId );
				$rawBsh         = get_post_meta( $postId, '_rtcl_bhs', true );
				$business_hours = ! empty( $rawBsh ) && is_array( $rawBsh ) ? $rawBsh : [];
				if ( ! empty( $business_hours ) ) {
					$bhs           = [
						'active' => true,
						'type'   => 'selective',
						'days'   => $business_hours
					];
					$rawSpecialBhs = get_post_meta( $postId, '_rtcl_special_bhs', true );
					if ( ! empty( $rawSpecialBhs ) && is_array( $rawSpecialBhs ) ) {
						$timeFormat   = 'H:i';
						$tempDateList = [];
						$newSBhs      = [];

						foreach ( $rawSpecialBhs as $sbh ) {

							if ( ! empty( $sbh['date'] ) && ! isset( $tempDateList[ $sbh['date'] ] ) && $dateObj = Utility::sanitizedDateObj( $sbh['date'] ) ) {
								$date                  = $dateObj->format( 'Y-m-d' );
								$tempDateList[ $date ] = $date;
								$newSbh                = [
									'date'  => $date,
									'occur' => 'repeat'
								];
								if ( ! empty( $sbh['open'] ) ) {
									$newSbh['open'] = true;
									if ( is_array( $sbh['times'] ) && ! empty( $sbh['times'] ) ) {
										$newTimes = [];
										foreach ( $sbh['times'] as $time ) {
											if ( ! empty( $time['start'] ) && ! empty( $time['end'] ) ) {
												$start = Utility::formatTime( $time['start'], 'H:i', $timeFormat );
												$end   = Utility::formatTime( $time['end'], 'H:i', $timeFormat );
												if ( $start && $end ) {
													$newTimes[] = [
														'start' => $start,
														'end'   => $end
													];
												}
											}
										}
										if ( ! empty( $newTimes ) ) {
											$newSbh['times'] = $newTimes;
										}
									}
								} else {
									$newSbh['open'] = false;
								}
								$newSBhs[] = $newSbh;
							}
						}
						$bhs['special'] = ! empty( $newSBhs ) ? $newSBhs : '';
					}
					update_post_meta( $postId, '_rtcl_bhs', $bhs );
				}
				foreach ( $migration['fields'] as $fieldUuid => $_field ) {
					if ( empty( $form->fields[ $fieldUuid ] ) ) {
						continue;
					}
					$field     = $form->fields[ $fieldUuid ];
					$fieldName = ! empty( $field['name'] ) ? $field['name'] : '';
					if ( empty( $fieldName ) || empty( $_field['id'] ) ) {
						return;
					}
					$fieldId = (int) $_field['id'];
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$metaData = $wpdb->get_results( "SELECT * FROM {$wpdb->postmeta} WHERE post_id = $postId AND meta_key LIKE '_field_$fieldId%'" );
					delete_post_meta( $postId, $fieldName );
					if ( empty( $metaData ) ) {
						continue;
					}

					foreach ( $metaData as $meta ) {
						add_post_meta( $meta->post_id, $fieldName, $meta->meta_value );
					}
				}

				do_action( 'rtcl_fb_cf_data_migration', $postId, $formId, $form );
			}
		}

		if ( $query->max_num_pages > 1 ) {
			// add next scheduler event
			wp_schedule_single_event( time(), 'rtcl_form_cf_data_migration' );
		}
	}

	/**
	 * Cleans up session data - cron callback.
	 *
	 * @since 2.2.7
	 */
	function cleanup_session_data() {
		$session_class = apply_filters( 'rtcl_session_handler', SessionHandler::class );
		$session       = new $session_class();

		if ( is_callable( [ $session, 'cleanup_sessions' ] ) ) {
			$session->cleanup_sessions();
		}
	}

	/**
	 * Cleans up temp listings - cron callback.
	 *
	 * @since 3.1.15
	 */
	function cleanup_temp_listings() {
		$time_ago   = current_time( 'timestamp' ) - ( 2 * HOUR_IN_SECONDS );
		$args       = [
			'post_type'      => rtcl()->post_type,
			'posts_per_page' => - 1,
			'post_status'    => 'rtcl-temp',
			'fields'         => 'ids',
			'date_query'     => [
				[
					'before'    => gmdate( 'Y-m-d H:i:s', $time_ago ),
					'inclusive' => true,
				]
			]
		];
		$rtcl_query = new WP_Query( apply_filters( 'rtcl_cron_cleanup_temp_listings_args', $args ) );
		if ( ! empty( $rtcl_query->posts ) ) {
			foreach ( $rtcl_query->posts as $post_id ) {
				wp_delete_post( $post_id, true );
			}
		}
	}

	function daily_scheduled_events() {
		do_action( 'rtcl_cron_daily_scheduled_events' );
	}

	function hourly_scheduled_events() {
		// TODO : Active all this function to active
		$this->sent_renewal_email_to_published_listings();
		$this->move_listings_publish_to_expired();
		$this->send_renewal_reminders();
		$this->delete_expired_listings();
		$this->remove_expired_featured();
		do_action( 'rtcl_cron_hourly_scheduled_events' );
	}

	function sent_renewal_email_to_published_listings() {
		$email_template_settings = Functions::get_option( 'rtcl_email_templates_settings' );
		$email_threshold         = (int) $email_template_settings['renewal_email_threshold'];

		if ( $email_threshold > 0 ) {

			$email_threshold_date = gmdate( 'Y-m-d H:i:s', strtotime( '+' . $email_threshold . ' days' ) );

			// Define the query
			$args = [
				'post_type'           => rtcl()->post_type,
				'posts_per_page'      => - 1,
				'post_status'         => 'publish',
				'fields'              => 'ids',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'meta_query'          => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				                           'relation' => 'AND',
				                           [
					                           'key'     => 'expiry_date',
					                           'value'   => $email_threshold_date,
					                           'compare' => '<',
					                           'type'    => 'DATETIME'
				                           ],
				                           [
					                           'key'     => 'renewal_reminder_sent',
					                           'compare' => 'NOT EXISTS'
				                           ],
				                           [
					                           'key'     => 'never_expires',
					                           'compare' => 'NOT EXISTS',
				                           ]
				]
			];

			$rtcl_query = new WP_Query( apply_filters( 'rtcl_cron_sent_renewal_email_to_published_listings_query_args', $args ) );

			if ( ! empty( $rtcl_query->posts ) ) {

				foreach ( $rtcl_query->posts as $post_id ) {
					// Send emails to user
					if ( Functions::get_option_item( 'rtcl_email_notifications_settings', 'notify_users', 'listing_renewal', 'multi_checkbox' ) ) {
						if ( rtcl()->mailer()->emails['Listing_Renewal_Email_To_Owner']->trigger( $post_id ) ) {
							update_post_meta( $post_id, 'renewal_reminder_sent', 1 );
						}
					}
					do_action( 'rtcl_cron_sent_renewal_email_to_published_listing', $post_id );
				}
			}
		}
	}

	function move_listings_publish_to_expired() {

		$general_settings           = Functions::get_option( 'rtcl_general_settings' );
		$email_template_settings    = Functions::get_option( 'rtcl_email_templates_settings' );
		$renewal_reminder_threshold = isset( $email_template_settings['renewal_reminder_threshold'] )
			? absint( $email_template_settings['renewal_reminder_threshold'] ) : 0;
		$delete_expired_listings    = isset( $general_settings['delete_expired_listings'] ) ? absint( $general_settings['delete_expired_listings'] ) : 0;
		$delete_threshold           = $renewal_reminder_threshold + $delete_expired_listings;

		// Define the query
		$args = [
			'post_type'           => rtcl()->post_type,
			'posts_per_page'      => - 1,
			'post_status'         => 'publish',
			'fields'              => 'ids',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'meta_query'          => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			                           'relation' => 'AND',
			                           [
				                           'key'     => 'expiry_date',
				                           'value'   => current_time( 'mysql' ),
				                           'compare' => '<',
				                           'type'    => 'DATETIME'
			                           ],
			                           [
				                           'key'     => 'never_expires',
				                           'compare' => 'NOT EXISTS',
			                           ]
			]
		];

		$rtcl_query = new WP_Query( apply_filters( 'rtcl_cron_move_listings_publish_to_expired_query_args', $args ) );

		if ( ! empty( $rtcl_query->posts ) ) {

			foreach ( $rtcl_query->posts as $post_id ) {
				// Update the post into the database
				$newData = [
					'ID'          => $post_id,
					'post_status' => 'rtcl-expired'
				];
				wp_update_post( $newData );      // Update post status to
				delete_post_meta( $post_id, 'expiry_date' );
				delete_post_meta( $post_id, 'never_expired' );
				delete_post_meta( $post_id, 'featured' );
				delete_post_meta( $post_id, 'feature_expiry_date' );
				delete_post_meta( $post_id, 'renewal_reminder_sent' );

				$syncData = [
					'post_status_update' => 'rtcl-expired',
					'delete'             => [
						'expiry_date',
						'never_expired',
						'featured',
						'feature_expiry_date',
						'renewal_reminder_sent'
					]
				];

				if ( $delete_threshold > 0 ) {
					$deletion_date_time = gmdate( 'Y-m-d H:i:s', strtotime( '+' . $delete_threshold . ' days' ) );
					update_post_meta( $post_id, 'deletion_date', $deletion_date_time ); // TODO : Need to check from where it to make action
					$syncData['update']['deletion_date'] = $deletion_date_time;
				}
				Functions::syncMLListingMeta( $post_id, $syncData );
				if ( Functions::get_option_item( 'rtcl_email_notifications_settings', 'notify_users', 'listing_expired', 'multi_checkbox' ) ) {
					rtcl()->mailer()->emails['Listing_Expired_Email_To_Owner']->trigger( $post_id );
				}

				if ( Functions::get_option_item( 'rtcl_email_notifications_settings', 'notify_admin', 'listing_expired', 'multi_checkbox' ) ) {
					rtcl()->mailer()->emails['Listing_Expired_Email_To_Admin']->trigger( $post_id );
				}

				// Hook for developers
				do_action( 'rtcl_cron_move_listing_publish_to_expired', $post_id );
			}
		}
	}

	function delete_expired_listings() {

		$general_settings        = Functions::get_option( 'rtcl_general_settings' );
		$email_template_settings = Functions::get_option( 'rtcl_email_templates_settings' );

		$renewal_reminder_threshold = isset( $email_template_settings['renewal_reminder_threshold'] )
			? (int) $email_template_settings['renewal_reminder_threshold'] : 0;
		$delete_expired_listings    = isset( $general_settings['delete_expired_listings'] ) ? (int) $general_settings['delete_expired_listings'] : 0;
		$can_renew                  = Functions::get_option_item( 'rtcl_general_settings', 'renew', false, 'checkbox' );

		if ( $can_renew ) {
			$delete_threshold = $renewal_reminder_threshold + $delete_expired_listings;
		} else {
			$delete_threshold = $delete_expired_listings;
		}

		if ( $delete_threshold > 0 ) {

			// Define the query
			$args = [
				'post_type'           => rtcl()->post_type,
				'posts_per_page'      => - 1,
				'post_status'         => 'rtcl-expired',
				'fields'              => 'ids',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'meta_query'          => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				                           'relation' => 'AND',
				                           [
					                           'key'     => 'deletion_date',
					                           'value'   => current_time( 'mysql' ),
					                           'compare' => '<',
					                           'type'    => 'DATETIME'
				                           ],
				                           [
					                           'key'     => 'never_expires',
					                           'compare' => 'NOT EXISTS',
				                           ]
				]
			];

			$rtcl_query = new WP_Query( apply_filters( 'rtcl_cron_delete_expired_listings_query_args', $args ) );

			if ( ! empty( $rtcl_query->posts ) ) {

				foreach ( $rtcl_query->posts as $post_id ) {
					do_action( 'rtcl_cron_delete_expired_listing', $post_id );
					Functions::delete_post( $post_id );
					Functions::syncMLListingMeta( $post_id, [ 'post_delete' => 1 ] );
				}
			}
		}
	}

	/**
	 * Renewal Reminders
	 *
	 * @return void
	 */
	function send_renewal_reminders() {
		$email_template_settings = Functions::get_option( 'rtcl_email_templates_settings' );
		$reminder_threshold      = isset( $email_template_settings['renewal_reminder_threshold'] )
			? (int) $email_template_settings['renewal_reminder_threshold'] : 0;

		if ( $reminder_threshold > 0 ) {
			// Define the query
			$args = [
				'post_type'           => rtcl()->post_type,
				'posts_per_page'      => - 1,
				'post_status'         => 'rtcl-expired',
				'fields'              => 'ids',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'meta_query'          => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				                           'relation' => 'AND',
				                           [
					                           'key'     => 'renewal_reminder_sent',
					                           'value'   => 0,
					                           'compare' => '='
				                           ],
				                           [
					                           'key'     => 'never_expires',
					                           'compare' => 'NOT EXISTS',
				                           ]
				]
			];

			$rtcl_query = new WP_Query( apply_filters( 'rtcl_cron_send_renewal_reminders_query_args', $args ) );

			if ( ! empty( $rtcl_query->posts ) ) {

				foreach ( $rtcl_query->posts as $post_id ) {

					$expiration_date      = get_post_meta( $post_id, 'expiry_date', true );
					$expiration_date_time = strtotime( $expiration_date );
					$reminder_date_time   = strtotime( '+' . $reminder_threshold . ' days', strtotime( $expiration_date_time ) );

					if ( current_time( 'timestamp' ) > $reminder_date_time ) {

						// Send renewal reminder emails to listing owner
						update_post_meta( $post_id, 'renewal_reminder_sent', 1 );
						if ( Functions::get_option_item( 'rtcl_email_notifications_settings', 'notify_users', 'remind_renewal', 'multi_checkbox' ) ) {
							rtcl()->mailer()->emails['Listing_Renewal_Reminder_Email_To_Owner']->trigger( $post_id );
						}

						do_action( 'rtcl_cron_send_renewal_reminders_listing', $post_id );
					}
				}
			}
		}
	}

	private function remove_expired_featured() {
		// Define the query
		$args = [
			'post_type'           => rtcl()->post_type,
			'posts_per_page'      => - 1,
			'post_status'         => 'publish',
			'fields'              => 'ids',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'meta_query'          => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			                           'relation' => 'AND',
			                           [
				                           'key'     => 'feature_expiry_date',
				                           'value'   => current_time( 'mysql' ),
				                           'compare' => '<',
				                           'type'    => 'DATETIME'
			                           ],
			                           [
				                           'key'     => 'featured',
				                           'compare' => '=',
				                           'value'   => 1,
			                           ]
			]
		];

		$rtcl_query = new WP_Query( apply_filters( 'rtcl_cron_remove_expired_featured_query_args', $args ) );

		if ( ! empty( $rtcl_query->posts ) ) {

			foreach ( $rtcl_query->posts as $post_id ) {
				delete_post_meta( $post_id, 'featured' );
				delete_post_meta( $post_id, 'feature_expiry_date' );
				do_action( 'rtcl_cron_remove_expired_featured_listing', $post_id );
				$syncData = [
					'delete' => [
						'featured',
						'feature_expiry_date'
					]
				];
				Functions::syncMLListingMeta( $post_id, $syncData );
			}
		}
	}
}