<?php
/**
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var boolean $can_add_favourites
 * @var boolean $can_report_abuse
 * @var boolean $social
 * @var integer $listing_id
 */

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Text;


if ( ! $can_add_favourites && ! $can_report_abuse && ! $social ) {
	return;
}
?>
	<div class="rtcl-single-listing-action-wrapper">
		<?php
		if ( Functions::is_tag_enabled() ) {
			Functions::print_html( Functions::get_listing_tag( $listing_id ) );
		}
		?>
		<ul class='rtcl-single-listing-action'>
			<?php do_action( 'rtcl_single_action_before_list_item', $listing_id ); ?>
			<?php if ( $can_add_favourites ): ?>
				<li id="rtcl-favourites"><?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo Functions::get_favourites_link( $listing_id ); ?></li>
			<?php endif; ?>
			<?php if ( $can_report_abuse ): ?>
				<li>
					<?php if ( is_user_logged_in() ): ?>
						<a href="javascript:void(0)" data-toggle="modal" id="rtcl-report-abuse-modal-link"><span
								class='rtcl-icon rtcl-icon-trash'></span><?php echo esc_html( Text::report_abuse() ); ?>
						</a>
					<?php else: ?>
						<a href="javascript:void(0)" class="rtcl-require-login"><span
								class='rtcl-icon rtcl-icon-trash'></span><?php echo esc_html( Text::report_abuse() ); ?>
						</a>
					<?php endif; ?>
				</li>
			<?php endif; ?>
			<?php do_action( 'rtcl_single_action_after_list_item', $listing_id ); ?>
			<?php if ( $social ): ?>
				<li class="rtcl-sidebar-social">
					<?php echo wp_kses_post( $social ); ?>
				</li>
			<?php endif; ?>
		</ul>
	</div>

<?php do_action( 'rtcl_single_listing_after_action', $listing_id ); ?>

<?php if ( $can_report_abuse ) { ?>
	<div class="rtcl-popup-wrapper" id="rtcl-report-abuse-modal">
		<div class="rtcl-popup">
			<div class="rtcl-popup-content">
				<div class="rtcl-popup-header">
					<h5 class="rtcl-popup-title" id="rtcl-report-abuse-modal-label"><?php esc_html_e( 'Report Abuse', 'classified-listing' ); ?></h5>
					<a href="#" class="rtcl-popup-close">×</a>
				</div>
				<div class="rtcl-popup-body">
					<form id="rtcl-report-abuse-form">
						<div class="rtcl-form-group">
							<label class="rtcl-field-label" for="rtcl-report-abuse-message">
								<?php esc_html_e( 'Your Complaint', 'classified-listing' ); ?>
								<span class="rtcl-star">*</span>
							</label>
							<textarea name="message" class="rtcl-form-control" id="rtcl-report-abuse-message" rows="3"
									  placeholder="<?php esc_attr_e( 'Message... ', 'classified-listing' ); ?>"
									  required></textarea>
						</div>
						<div id="rtcl-report-abuse-g-recaptcha"></div>
						<div id="rtcl-report-abuse-message-display"></div>
						<button type="submit"
								class="rtcl-btn rtcl-btn-primary"><?php esc_html_e( 'Submit', 'classified-listing' ); ?></button>
					</form>
				</div>
			</div>
		</div>
	</div>
<?php } ?>