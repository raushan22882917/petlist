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
use RtclClaimListing\Helpers\Functions as ClaimFunctions;

if ( ! $can_add_favourites && ! $can_report_abuse && ! $social ) {
	return;
}
?>
    <ul class='list-group list-group-flush rtcl-single-listing-action'>
		<?php if ( $can_add_favourites ): ?>
            <li class="list-group-item" id="rtcl-favourites" data-bs-toggle="tooltip" data-bs-placement="top"
                data-bs-title="<?php echo esc_html( Text::add_to_favourite() ); ?>">
				<?php echo Functions::get_favourites_link( $listing_id ); ?>
            </li>
		<?php endif; ?>
		<?php if ( $can_report_abuse ): ?>
            <li class="list-group-item" data-bs-toggle="tooltip" data-bs-placement="top"
                data-bs-title="<?php echo esc_html( Text::report_abuse() ); ?>">
				<?php if ( is_user_logged_in() ): ?>
                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#rtcl-report-abuse-modal">
                        <span class='rtcl-icon rtcl-icon-trash'></span>
                    </a>
				<?php else: ?>
                    <a href="javascript:void(0)" class="rtcl-require-login">
                        <span class='rtcl-icon rtcl-icon-trash'></span>
                    </a>
				<?php endif; ?>
            </li>
		<?php endif; ?>
		<?php if ( function_exists( 'rtclClaimListing' ) && ClaimFunctions::claim_listing_enable() ): ?>
            <li class='list-group-item' data-bs-toggle="tooltip" data-bs-placement="top"
                data-bs-title="<?php echo esc_html( ClaimFunctions::get_claim_action_title() ); ?>">
				<?php if ( is_user_logged_in() ): ?>
                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#rtcl-claim-listing-modal">
                        <span class="rtcl-icon rtcl-icon-exchange"></span>
                    </a>
				<?php else: ?>
                    <a href="javascript:void(0)" class="rtcl-require-login">
                        <span class="rtcl-icon rtcl-icon-exchange"></span>
                    </a>
				<?php endif; ?>
            </li>
		<?php endif; ?>
		<?php if ( $social ): ?>
            <li class="list-group-item social-share-list" data-bs-toggle="tooltip" data-bs-placement="top"
                data-bs-trigger="hover" data-bs-title="<?php esc_attr_e( 'Share', 'petslist' ); ?>">
                <a class="listing-social-action" data-bs-toggle="modal" data-bs-target="#rtcl-social-share-modal">
                    <i class="fa-solid fa-share-nodes"></i>
                </a>
            </li>
		<?php endif; ?>
    </ul>

<?php if ( $social ): ?>
    <!-- Social Share Modal -->
    <div class="modal fade rtcl-social-share-modal" id="rtcl-social-share-modal" tabindex="-1" role="dialog"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"
                        id="rtcl-social-share-modal-label"><?php esc_html_e( 'Share This Link Via', 'petslist' ); ?></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="share-icon">
						<?php echo wp_kses_post( $social ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php do_action( 'rtcl_single_listing_after_action', $listing_id ); ?>

<?php if ( $can_report_abuse ) { ?>
    <!-- Social Share Modal -->
    <div class="modal fade rtcl-bs-modal" id="rtcl-report-abuse-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="rtcl-report-abuse-form" class="form-vertical">
                    <div class="modal-header">
                        <h5 class="modal-title"
                            id="rtcl-report-abuse-modal-label"><?php esc_html_e( 'Report Abuse', 'petslist' ); ?></h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label
                                    for="rtcl-report-abuse-message"><?php esc_html_e( 'Your Complaint', 'petslist' ); ?>
                                <span class="rtcl-star">*</span></label>
                            <textarea name="message" class="form-control" id="rtcl-report-abuse-message" rows="3"
                                      placeholder="<?php esc_attr_e( 'Message... ', 'petslist' ); ?>"
                                      required></textarea>
                        </div>
                        <div id="rtcl-report-abuse-g-recaptcha"></div>
                        <div id="rtcl-report-abuse-message-display"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit"
                                class="btn btn-primary"><?php esc_html_e( 'Submit', 'petslist' ); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php } ?>