<?php
/**
 *
 * @package ClassifiedListing/Templates
 * @version 5.2.0
 * @var Form $form
 * @var string $fieldUuid
 * @var FBField $field
 * @var Listing $field
 */

use Rtcl\Helpers\Functions;
use Rtcl\Models\Form\Form;
use Rtcl\Models\Listing;
use Rtcl\Services\FormBuilder\FBField;
use Rtcl\Helpers\Text;

defined( 'ABSPATH' ) || exit;
global $listing;
if ( ! is_a( $field, FBField::class ) || ! is_a( $listing, Listing::class ) ) {
	return;
}
$meta_field_obj = $field->getField();
$listing_meta       = $meta_field_obj['items'];
$direction      = $meta_field_obj['direction'] ?? 'horizontal';
$listing_id         = $listing->get_id();
$can_add_favourites = (bool) Functions::get_option_item( 'rtcl_general_settings', 'has_favourites', '', 'checkbox' );
$can_report_abuse   = (bool) Functions::get_option_item( 'rtcl_single_listing_settings', 'has_report_abuse', '', 'checkbox' );
$has_compare_icon   = (bool) Functions::get_option_item( 'rtcl_single_listing_settings', 'has_compare_icon', 'yes', 'checkbox' );
$has_bookmark_icon  = (bool) Functions::get_option_item( 'rtcl_single_listing_settings', 'has_bookmark_icon', 'yes', 'checkbox' );
$has_print_icon     = (bool) Functions::get_option_item( 'rtcl_single_listing_settings', 'has_print_icon', '', 'checkbox' );
$social             = $listing->the_social_share( false );

if ( empty( $listing_meta ) ) {
	return;
}

if ( ! $can_add_favourites && ! $can_report_abuse && ! $social ) {
	return;
}

$listing_actions = [];
?>
<div class="single-listing-custom-fields-action rtcl-direction-<?php echo esc_attr( $direction ); ?>">
	<ul class="rtcl-single-listing-action">
		<?php do_action( 'rtcl_single_action_before_list_item', $listing_id ); ?>

		<?php foreach ( $listing_meta as $meta ) :
			$type       = $meta['type'];
			$icon_class = ! empty( $meta['icon']['class'] ) ? esc_attr( $meta['icon']['class'] ) : '';

			switch ( $type ) {

				case 'favourite':
					if ( $can_add_favourites ) {
						echo '<li id="rtcl-favourites">';
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo Functions::get_favourites_link( $listing_id );
						echo '</li>';
					}
					break;

				case 'compare':
					if ( $has_compare_icon ) {
						$compare_ids    = ! empty( $_SESSION['rtcl_compare_ids'] ) ? array_map( 'absint', $_SESSION['rtcl_compare_ids'] ) : [];
						$selected_class = ( is_array( $compare_ids ) && in_array( $listing_id, $compare_ids ) ) ? ' selected' : '';
						echo '<li>
							<div class="rtcl-el-button">
								<a class="rtcl-compare ' . esc_attr( $selected_class ) . '" href="#" title="' . esc_attr__( 'Compare', 'classified-listing' ) . '" data-listing_id="' . absint( $listing_id ) . '">
									<i class="rtcl-icon ' . $icon_class . '"></i>
									<span class="compare-label">' . esc_html__( 'Compare', 'classified-listing' ) . '</span>
								</a>
							</div>
						</li>';
					}
					break;

				case 'report_abuse':
					if ( $can_report_abuse ) {
						echo '<li>';
						if ( is_user_logged_in() ) {
							echo '<a href="javascript:void(0)" data-toggle="modal" id="rtcl-report-abuse-modal-link">
								<i class="rtcl-icon ' . $icon_class . '"></i>' . esc_html( Text::report_abuse() ) . '
							</a>';
						} else {
							echo '<a href="javascript:void(0)" class="rtcl-require-login">
								<i class="rtcl-icon ' . $icon_class . '"></i>' . esc_html( Text::report_abuse() ) . '
							</a>';
						}
						echo '</li>';
					}
					break;

				case 'share':
					if ( ! empty( $social ) ) {
						echo '<li class="rtcl-sidebar-social">' . wp_kses_post( $social ) . '</li>';
					}
					break;
				case 'print':
					if ( $has_print_icon ) {
						echo '<li>
							<a href="#" onclick="window.print();">
								<i class="rtcl-icon ' . $icon_class . '"></i>
								<span class="print-label">' . esc_html__( 'Print', 'listpress' ) . '</span>
							</a>
						</li>';
					}
					break;
			}

		endforeach; ?>
		<?php do_action( 'rtcl_single_action_after_list_item', $listing_id ); ?>
	</ul>
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
</div>