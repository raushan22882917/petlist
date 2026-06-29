<?php

namespace Rtcl\Controllers\Admin;

class NoticeController {

	/**
	 * Minimum published listings to show the review notice for the first time.
	 */
	const REVIEW_INITIAL_THRESHOLD = 50;

	/**
	 * Additional listings required before showing the notice again after "Maybe Later".
	 */
	const REVIEW_SNOOZE_INCREMENT = 50;

	public function __construct() {
		$current      = time();
		$currentYear  = gmdate( 'Y' );
		$black_friday = mktime( 0, 0, 0, 11, 10, $currentYear ) <= $current && $current <= mktime( 0, 0, 0, 1, 10, $currentYear + 1 );

		if ( $black_friday ) {
			add_action( 'admin_init', [ $this, 'black_friday_notice' ] );
		} else {
			add_action( 'admin_init', [ $this, 'check_review_notice' ] );
			add_action( 'wp_ajax_rtcl_review_notice_action', [ $this, 'handle_review_notice_ajax' ] );
		}
		add_action( 'admin_notices', [ __CLASS__, 'eid_special_deal_admin_notice' ] );
		add_action( 'wp_ajax_rtcl_dismiss_eid_notice', [ __CLASS__, 'dismiss_eid_notice' ] );
	}

	public static function eid_special_deal_admin_notice() {
		// Set expiration date (April 7)
		$expiration_date = strtotime( 'April 7, 2025' );

		// Check if the current date is past the expiration date
		if ( time() > $expiration_date ) {
			return;
		}

		// Check if notice is dismissed
		if ( get_user_meta( get_current_user_id(), 'rtcl_dismissed_ramadan_notice', true ) ) {
			return;
		}


		$plugin_name   = 'Classified Listing';
		$download_link = 'https://www.radiustheme.com/downloads/classified-listing-pro-plugins-bundle/';

		?>

		<div class="notice notice-info is-dismissible rtcl-ramadan-notice" data-rtcl-dismissable="rtcl_dismiss_ramadan_notice"
		     style="display:grid !important;grid-template-columns: 100px auto;padding-top: 25px; padding-bottom: 22px;">
			<img alt="<?php
			echo esc_attr( $plugin_name ); ?>"
			     src="<?php
				 echo esc_url( rtcl()->get_assets_uri( 'images/classified-listing-promo.gif' ) ); ?>"
			     width="74px" height="74px" style="grid-row: 1 / 4; align-self: center;justify-self: center"/>
			<h3 style="margin:0;display: inline-flex;align-items: center;gap: 4px;">
				<?php
				echo sprintf( ' %s – 🌙 Eid Special Offer', esc_html( $plugin_name ) ); ?>
				<img alt="Deal" style="width: 60px;position: static" src="<?php
				echo esc_url( rtcl()->get_assets_uri( 'images/deal.gif' ) ); ?>">
			</h3>
			<p style="margin-top: 0; font-size: 14px;">
				<strong>Eid Special:</strong>
				Celebrate Eid with exclusive discounts on
				<b><a href="<?php
					echo esc_url( $download_link ); ?>" style="text-decoration: none;color: inherit">Classified Listing Bundle</a></b>. Save
				<b style="display:inline-block;color: white;background:red;padding: 0 8px;border-radius:3px; transform: skewX(-10deg);">UP TO 40%</b>
				for a limited time! 🎁🌙✨
			</p>
			<p style="margin:0;">
				<a class="button button-primary" href="<?php
				echo esc_url( $download_link ); ?>"
				   style="background: #3232FF;"
				   target="_blank">Buy Now</a>
			</p>
		</div>

		<script>
			jQuery(document).on('click', '.rtcl-ramadan-notice .notice-dismiss', function () {
				jQuery.post(ajaxurl, {
					action: 'rtcl_dismiss_eid_notice',
					security: '<?php echo esc_attr( wp_create_nonce( "dismiss_eid_notice" ) ); ?>',
				})
			})
		</script>
		<?php
	}

	public static function dismiss_eid_notice() {
		check_ajax_referer( 'dismiss_eid_notice', 'security' );
		update_user_meta( get_current_user_id(), 'rtcl_dismissed_ramadan_notice', true );
		wp_die();
	}

	/**
	 * Check if the listing-count-based review notice should display.
	 */
	public function check_review_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( get_option( 'rtcl_review_notice_dismissed' ) === 'yes' ) {
			return;
		}

		// One-time migration from old time-based system
		$old_status = get_option( 'rtcl_rating_status' );
		if ( false !== $old_status ) {
			if ( in_array( $old_status, [ 'rated', 'skip' ], true ) ) {
				// User already rated or permanently skipped
				update_option( 'rtcl_review_notice_dismissed', 'yes' );
			} elseif ( is_numeric( $old_status ) ) {
				// User clicked "Remind Me Later" (stored as timestamp)
				// Migrate to threshold-based: set next threshold to current count + snooze increment
				$counts          = wp_count_posts( 'rtcl_listing' );
				$published_count = isset( $counts->publish ) ? (int) $counts->publish : 0;
				$new_threshold   = max( $published_count, self::REVIEW_INITIAL_THRESHOLD ) + self::REVIEW_SNOOZE_INCREMENT;
				update_option( 'rtcl_review_notice_next_threshold', $new_threshold );
			}
			delete_option( 'rtcl_rating_status' );

			return;
		}

		$counts          = wp_count_posts( 'rtcl_listing' );
		$published_count = isset( $counts->publish ) ? (int) $counts->publish : 0;
		$threshold       = (int) get_option( 'rtcl_review_notice_next_threshold', self::REVIEW_INITIAL_THRESHOLD );

		if ( $published_count >= $threshold ) {
			add_action( 'admin_notices', [ $this, 'render_review_notice' ] );
		}
	}

	/**
	 * Render the review request admin notice with AJAX-powered buttons.
	 */
	public function render_review_notice() {
		global $pagenow;

		$exclude = [
			'themes.php',
			'users.php',
			'tools.php',
			'options-general.php',
			'options-writing.php',
			'options-reading.php',
			'options-discussion.php',
			'options-media.php',
			'options-permalink.php',
			'options-privacy.php',
			'edit-comments.php',
			'upload.php',
			'media-new.php',
			'admin.php',
			'import.php',
			'export.php',
			'site-health.php',
			'export-personal-data.php',
			'erase-personal-data.php',
		];

		if ( in_array( $pagenow, $exclude, true ) ) {
			return;
		}

		$review_url = 'https://wordpress.org/support/plugin/classified-listing/reviews/?filter=5#new-post';
		$nonce      = wp_create_nonce( 'rtcl_review_notice_nonce' );
		$icon_url   = rtcl()->get_assets_uri( 'images/icon-64x64.png' );
		$threshold  = (int) get_option( 'rtcl_review_notice_next_threshold', self::REVIEW_INITIAL_THRESHOLD );
		?>
		<div class="notice rtcl-review-notice" id="rtcl-review-notice">
			<button type="button" class="rtcl-review-notice__close" data-action="dismissed" aria-label="<?php esc_attr_e( 'Dismiss', 'classified-listing' ); ?>">&times;</button>
			<div class="rtcl-review-notice__inner">
				<div class="rtcl-review-notice__icon">
					<img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php esc_attr_e( 'Classified Listing', 'classified-listing' ); ?>" width="60" height="60"/>
				</div>
				<div class="rtcl-review-notice__content">
					<h3 class="rtcl-review-notice__title">
						<?php esc_html_e( 'Enjoying Classified Listing?', 'classified-listing' ); ?>
						<span class="rtcl-review-notice__stars" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
					</h3>
					<p class="rtcl-review-notice__desc">
						<?php
						echo wp_kses(
							sprintf(
							/* translators: %1$s: opening strong tag, %2$d: listing count, %3$s: closing strong tag */
								__( 'You\'ve published %1$s%2$d listings%3$s with Classified Listing — that\'s a real milestone! If the plugin has helped your site, a quick %1$s5-star review%3$s on WordPress.org would mean a lot and helps other users find us.', 'classified-listing' ),
								'<strong>',
								$threshold,
								'</strong>',
							),
							[ 'strong' => [] ],
						);
						?>
					</p>
					<div class="rtcl-review-notice__actions">
						<a href="<?php echo esc_url( $review_url ); ?>"
						   class="rtcl-review-btn rtcl-review-btn--primary"
						   target="_blank"
						   data-action="rated">
							&#9733; <?php esc_html_e( 'Leave a 5-star review', 'classified-listing' ); ?>
						</a>
						<a href="#"
						   class="rtcl-review-btn rtcl-review-btn--outline rtcl-review-btn--rated"
						   data-action="rated">
							&#10003; <?php esc_html_e( 'I\'ve already rated', 'classified-listing' ); ?>
						</a>
						<a href="#"
						   class="rtcl-review-btn rtcl-review-btn--outline"
						   data-action="later">
							<?php esc_html_e( 'Maybe later', 'classified-listing' ); ?>
						</a>
						<a href="#"
						   class="rtcl-review-btn rtcl-review-btn--outline rtcl-review-btn--dismissed"
						   data-action="dismissed">
							<?php esc_html_e( 'Don\'t show this again', 'classified-listing' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>

		<style>
			.notice.rtcl-review-notice {
				position: relative;
				margin: 15px 20px 15px 2px;
				padding: 0 !important;
				border: 1px solid #e2e4e7;
				border-left: 4px solid #3232FF;
				border-radius: 4px;
				background: #fff;
				box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif;
			}

			.rtcl-review-notice__close {
				position: absolute;
				top: 12px;
				right: 14px;
				background: none;
				border: none;
				font-size: 20px;
				line-height: 1;
				color: #999;
				cursor: pointer;
				padding: 0;
				width: 24px;
				height: 24px;
				display: flex;
				align-items: center;
				justify-content: center;
				border-radius: 50%;
				transition: color 0.15s, background 0.15s;
			}

			.rtcl-review-notice__close:hover {
				color: #d63638;
				background: #fcf0f1;
			}

			.rtcl-review-notice__inner {
				display: flex;
				align-items: flex-start;
				gap: 18px;
				padding: 22px 44px 22px 24px;
			}

			.rtcl-review-notice__icon {
				flex-shrink: 0;
				width: 60px;
				height: 60px;
				border-radius: 14px;
				overflow: hidden;
				background: linear-gradient(135deg, #4C6FFF, #6939c6);
				display: flex;
				align-items: center;
				justify-content: center;
				box-shadow: 0 2px 8px rgba(76, 111, 255, 0.25);
			}

			.rtcl-review-notice__icon img {
				display: block;
				width: 100%;
				height: 100%;
				object-fit: cover;
			}

			.rtcl-review-notice__title {
				margin: 0 0 8px;
				font-size: 15px;
				font-weight: 600;
				color: #1e1e1e;
				line-height: 1.4;
				display: flex;
				align-items: center;
				gap: 8px;
			}

			.rtcl-review-notice__inner .rtcl-review-notice__stars {
				color: #f0b849;
				font-size: 14px;
				letter-spacing: 1px;
				line-height: 1;
			}

			.rtcl-review-notice__inner .rtcl-review-notice__desc {
				margin: 0 0 16px;
				font-size: 13px;
				line-height: 1.6;
				color: #50575e;
				max-width: 720px;
			}

			.rtcl-review-notice__inner .rtcl-review-notice__actions {
				display: flex;
				flex-wrap: wrap;
				align-items: center;
				gap: 10px;
			}

			.rtcl-review-notice__inner .rtcl-review-btn {
				display: inline-flex;
				align-items: center;
				gap: 5px;
				padding: 8px 16px;
				border-radius: 4px;
				font-size: 13px;
				font-weight: 500;
				line-height: 1.3;
				text-decoration: none;
				cursor: pointer;
				white-space: nowrap;
				box-shadow: none;
				outline: none;
				transition: background 0.15s, color 0.15s, border-color 0.15s;
			}

			/* Primary CTA */
			.rtcl-review-notice__inner .rtcl-review-btn--primary {
				background: #3232FF;
				color: #fff;
				border: 1px solid #3232FF;
			}

			.rtcl-review-notice__inner .rtcl-review-btn--primary:hover,
			.rtcl-review-notice__inner .rtcl-review-btn--primary:focus {
				background: #062ed5;
				border-color: #062ed5;
				color: #fff;
				box-shadow: none;
				outline: none;
			}

			/* Outlined buttons (default neutral) */
			.rtcl-review-notice__inner .rtcl-review-btn--outline {
				background: #fff;
				color: rgb(60, 67, 74);
				border: 1px solid rgb(220, 220, 222);
			}

			.rtcl-review-notice__inner .rtcl-review-btn--outline:hover,
			.rtcl-review-notice__inner .rtcl-review-btn--outline:focus {
				background: #fafaff;
				color: #3232FF;
				border-color: #9797ff;
				outline: none;
				box-shadow: none;
			}

			/* Already rated - green accent */
			.rtcl-review-notice__inner .rtcl-review-btn--rated {
				color: #008a20;
				border-color: #008a20;
			}

			.rtcl-review-notice__inner .rtcl-review-btn--rated:hover,
			.rtcl-review-notice__inner .rtcl-review-btn--rated:focus {
				background: #edfcf2;
				color: #006818;
				border-color: #006818;
				box-shadow: none;
				outline: none;
			}

			.rtcl-review-notice__inner .rtcl-review-btn--dismissed {
				border-color: rgb(220, 220, 222);
				color: rgb(120, 124, 130);
			}

			.rtcl-review-notice__inner .rtcl-review-btn--dismissed:hover,
			.rtcl-review-notice__inner .rtcl-review-btn--dismissed:focus {
				color: #eb2628;
				background: #fcf0f1;
				border-color: #ffa6a7;
			}

			@media screen and (max-width: 782px) {
				.rtcl-review-notice__inner {
					flex-direction: column;
					gap: 14px;
					padding: 18px 40px 18px 18px;
				}

				.rtcl-review-notice__inner .rtcl-review-notice__actions {
					gap: 8px;
				}

				.rtcl-review-notice__inner .rtcl-review-btn {
					padding: 7px 12px;
					font-size: 12px;
				}
			}
		</style>

		<script>
			jQuery(function ($) {
				var $notice = $('#rtcl-review-notice');
				$notice.on('click', '.rtcl-review-btn[data-action], .rtcl-review-notice__close[data-action]', function (e) {
					var action = $(this).data('action');
					if (action !== 'rated' || !$(this).attr('target')) {
						e.preventDefault();
					}
					$notice.fadeOut(250);
					$.post(ajaxurl, {
						action: 'rtcl_review_notice_action',
						review_action: action,
						_wpnonce: '<?php echo esc_js( $nonce ); ?>'
					});
				});
			});
		</script>
		<?php
	}

	/**
	 * AJAX handler for review notice button actions.
	 */
	public function handle_review_notice_ajax() {
		check_ajax_referer( 'rtcl_review_notice_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		$review_action = isset( $_POST['review_action'] ) ? sanitize_text_field( wp_unslash( $_POST['review_action'] ) ) : '';

		switch ( $review_action ) {
			case 'rated':
			case 'dismissed':
				update_option( 'rtcl_review_notice_dismissed', 'yes' );
				break;

			case 'later':
				$counts            = wp_count_posts( 'rtcl_listing' );
				$published_count   = isset( $counts->publish ) ? (int) $counts->publish : 0;
				$current_threshold = (int) get_option( 'rtcl_review_notice_next_threshold', self::REVIEW_INITIAL_THRESHOLD );
				$new_threshold     = max( $published_count, $current_threshold ) + self::REVIEW_SNOOZE_INCREMENT;
				update_option( 'rtcl_review_notice_next_threshold', $new_threshold );
				break;

			default:
				wp_send_json_error( [ 'message' => 'Invalid action' ] );

				return;
		}

		wp_send_json_success();
	}

	public function black_friday_notice() {
		delete_option( 'rtcl_dismiss_admin_notice' );
		$currentYear = gmdate( 'Y' );
		if ( get_option( 'rtcl_dismiss_admin_notice_' . $currentYear ) != '1' && ! isset( $GLOBALS['rtcl_dismiss_admin_notice_notice'] ) ) {
			$GLOBALS['rtcl_dismiss_admin_notice_notice'] = 'rtcl_dismiss_admin_notice';
			$this->bfNoticeActions();
		}
	}


	/**
	 * Undocumented function.
	 *
	 * @return void
	 */
	public function bfNoticeActions() {
		add_action( 'admin_enqueue_scripts', function () {
			wp_enqueue_script( 'jquery' );
		} );
		add_action(
			'admin_notices',
			function () {
				$currentYear   = gmdate( 'Y' );
				$plugin_name   = 'Classified Listing';
				$download_link = 'https://www.radiustheme.com/downloads/classified-listing-pro-plugins-bundle/'; ?>
				<div class="notice notice-info is-dismissible" data-rtcl-bf-dismiss-able="rtcl_dismiss_admin_notice"
				     style="display:grid;grid-template-columns: 100px auto;column-gap:10px;padding-top: 15px; padding-bottom: 12px; background: #f1f2fe; border-color: #cfd2ff; border-left-color: #3232ff;">
					<img alt="<?php
					echo esc_attr( $plugin_name ); ?>"
					     src="<?php
						 echo esc_url( rtcl()->get_assets_uri( 'images/classified-listing-promo.gif' ) ) ?>"
					     width="90px"
					     height="90px" style="grid-row: 1 / 4; align-self: center;justify-self: center"/>
					<h3 style="margin:0;display: flex;align-items: center"><?php
						echo sprintf( '%s - Holiday Special <img style="width: 45px;position: relative;margin-left: 6px" src="%s" />',
							esc_html( $plugin_name ),
							esc_url( rtcl()->get_assets_uri( 'images/deal.gif' ) ) ); ?></h3>

					<p style="margin:3px 0 5px; font-size: 14px">
						Holiday special sale is live now! Get the <strong>plugin bundle</strong> or
						<strong>individual addon</strong> and enjoy discounts <span style="color: #fe0100; font-weight: 600">up to 50%</span>. Limited time
						offer!!
					</p>

					<p style="margin:0;">
						<a class="button button-primary" href="<?php
						echo esc_url( $download_link ); ?>"
						   target="_blank" style="background: #3232FF;">Buy Now</a>
						<a class="button button-dismiss" href="#" style="color: #3232FF; border-color: #3232FF; background: none">Dismiss</a>
					</p>
				</div>
				<?php
			},
		);

		add_action(
			'admin_footer',
			function () {
				?>
				<script type="text/javascript">
					(function ($) {
						$(function () {
							setTimeout(function () {
								$('div[data-rtcl-bf-dismiss-able] .notice-dismiss, div[data-rtcl-bf-dismiss-able] .button-dismiss')
									.on('click', function (e) {
										e.preventDefault();
										$.post(ajaxurl, {
											'action': 'rtcl_bf_dismiss_admin_notice',
											'nonce': <?php echo wp_json_encode( wp_create_nonce( 'rtcl-bf-dismissible-notice' ) ); ?>
										});
										$(e.target).closest('.is-dismissible').remove();
									});
							}, 1000);
						});
					})(jQuery);
				</script>
				<?php
			},
		);

		add_action(
			'wp_ajax_rtcl_bf_dismiss_admin_notice',
			function () {
				$currentYear = gmdate( 'Y' );
				check_ajax_referer( 'rtcl-bf-dismissible-notice', 'nonce' );

				update_option( 'rtcl_dismiss_admin_notice_' . $currentYear, '1' );
				wp_die();
			},
		);
	}

}