<?php
/**
 * Claim Listing Form
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var int $listing_id
 * @var int $user_id
 */

use RtclClaimListing\Helpers\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$name  = get_the_author_meta( 'display_name', $user_id );
$email = get_the_author_meta( 'user_email', $user_id );
$phone = get_the_author_meta( '_rtcl_phone', $user_id );
?>
<div class="modal fade rtcl-bs-modal rtcl-claim-listing-wrapper" id="rtcl-claim-listing-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" class="rtcl-claim-listing-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="rtcl-claim-listing-modal-label">
						<?php echo esc_html( Functions::get_claim_popup_title() ); ?>
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name"><?php esc_html_e( 'Name', 'petslist' ); ?></label>
                        <input type="text" name="name" id="name" class="form-control"
                               value="<?php echo esc_attr( $name ); ?>"
                               required/>
                    </div>
                    <div class="form-group">
                        <label for="email"><?php esc_html_e( 'Email', 'petslist' ); ?></label>
                        <input type="email" name="email" id="email" class="form-control"
                               value="<?php echo esc_attr( $email ); ?>" required/>
                    </div>
                    <div class="form-group">
                        <label for="phone"><?php esc_html_e( 'Phone', 'petslist' ); ?></label>
                        <input type="tel" name="phone" id="phone" class="form-control"
                               value="<?php echo esc_attr( $phone ); ?>" required/>
                    </div>
                    <div class="form-group">
                        <label for="message"><?php esc_html_e( 'Details', 'petslist' ); ?></label>
                        <textarea placeholder="<?php esc_attr_e( 'Write your claim here', 'petslist' ); ?>"
                                  name="message"
                                  id="message" class="form-control"></textarea>
                    </div>
					<?php if ( Functions::is_enable_attachment_field() ): ?>
                        <div class="form-group">
                            <label><?php esc_html_e( 'Attachment', 'petslist' ); ?></label>
                            <div class="rtcl-claim-document-wrap">
                                <div class="rtcl-claim-document no-file">
                                    <div class="rtcl-media-action">
                                    <span class="document-upload-btn add">
                                        <i class="rtcl-icon-upload"></i> <?php esc_html_e( 'Upload File', 'petslist' ); ?>
                                    </span>
                                    </div>
                                    <div class="other-document"></div>
                                </div>
                                <div class="alert alert-danger mt-2">
									<?php esc_html_e( 'Maximum file size 5 MB, only pdf file allowed.', 'petslist' ); ?>
                                </div>
                            </div>
                        </div>
					<?php endif; ?>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="listing_id" value="<?php echo esc_attr( $listing_id ); ?>"/>
                    <input type="hidden" name="user_id" value="<?php echo esc_attr( $user_id ); ?>"/>
                    <button type="submit" class="btn btn-primary">
						<?php echo esc_html( Functions::get_claim_button_text() ); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>