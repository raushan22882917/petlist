<?php
/**
 * The template for displaying product content in the single-rtcl_listing.php template
 *
 * This template can be overridden by copying it to yourtheme/classified-listing/content-single-rtcl_listing.php.
 *
 * @package ClassifiedListing/Templates
 * @version 1.5.56
 */

use Rtcl\Helpers\Functions;
use Rtcl\Models\Form\Form;

defined( 'ABSPATH' ) || exit;

global $listing;

if ( post_password_required() ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo get_the_password_form();

	return;
}

$sidebar_position = Functions::get_option_item( 'rtcl_single_listing_settings', 'detail_page_sidebar_position', 'right' );
/**
 * Hook: rtcl_before_single_product.
 *
 * @hooked rtcl_print_notices - 10
 */
do_action( 'rtcl_before_single_listing' );

?>
<div id="rtcl-listing-<?php the_ID(); ?>" <?php Functions::listing_class( '', $listing ); ?>>
	<div class="listing-content">
		<div class="mb-4 rtcl-single-listing-details">
			<?php do_action( 'rtcl_single_listing_content' ); ?>
			<div class="rtcl-main-content-wrapper">
				<!-- Price -->
				<?php if ( $listing->can_show_price() ): ?>
					<div class="rtcl-price-wrap">
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $listing->get_price_html(); ?>
					</div>
				<?php endif; ?>

				<!-- Description -->
				<div class="rtcl-listing-description"><?php $listing->the_content(); ?></div>

				<?php if ( $sidebar_position === "bottom" ) : ?>
					<!-- Sidebar -->
					<?php do_action( 'rtcl_single_listing_sidebar' ); ?>
				<?php endif; ?>
				<div class="single-listing-custom-fields-action">
					<?php do_action( 'rtcl_single_listing_inner_sidebar' ); ?>
				</div>
			</div>
		</div>

		<!-- MAP  -->
		<?php do_action( 'rtcl_single_listing_content_end', $listing ); ?>

		<!-- Business Hours  -->
		<?php do_action( 'rtcl_single_listing_business_hours' ) ?>

		<!-- Social Profile  -->
		<?php do_action( 'rtcl_single_listing_social_profiles' ) ?>

		<!-- Related Listing -->
		<?php $listing->the_related_listings(); ?>

		<!-- Review  -->
		<?php do_action( 'rtcl_single_listing_review' ) ?>

		<?php if ( !Functions::is_enable_template_support() && in_array( $sidebar_position, [ 'left', 'right' ] ) ) : ?>
			<!-- Sidebar -->
			<?php do_action( 'rtcl_single_listing_sidebar' ); ?>
		<?php endif; ?>
	</div>
</div>

<?php do_action( 'rtcl_after_single_listing' ); ?>
