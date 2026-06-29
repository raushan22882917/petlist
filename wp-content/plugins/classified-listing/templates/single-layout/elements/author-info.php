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
use Rtcl\Helpers\Text;
use Rtcl\Models\Form\Form;
use Rtcl\Models\Listing;
use Rtcl\Services\FormBuilder\FBField;
use Rtcl\Services\FormBuilder\FBHelper;
use Rtcl\Controllers\Hooks\TemplateHooks;


defined( 'ABSPATH' ) || exit;
global $listing;
if ( ! is_a( $field, FBField::class ) || ! is_a( $listing, Listing::class ) ) {
	return;
}
$author_info = $field->getField();
$items       = $author_info['items'] ?? [];
?>
	<div class='rtcl-list-group-item rtcl-listing-author-info'>
		<div class='media'>
			<?php
			if ( in_array( 'avatar', $items ) ) {
				$pp_id = absint( get_user_meta( $listing->get_owner_id(), '_rtcl_pp_id', true ) );
				if ( $listing->can_add_user_link() ): ?>
					<a href="<?php echo esc_url( $listing->get_the_author_url() ); ?>" aria-label="Post Author"><?php echo( $pp_id ? wp_get_attachment_image( $pp_id, [
							40,
							40,
						] )
							: get_avatar( $listing->get_author_id(), 40 ) ); ?></a>
				<?php else:
					echo( $pp_id ? wp_get_attachment_image( $pp_id, [
						40,
						40,
					] ) : get_avatar( $listing->get_author_id(), 40 ) );
				endif;
			}
			?>
			<div class='media-body'>
				<?php if ( in_array( 'name', $items ) ) { ?>
					<a class="rtcl-listing-author"
					   href="<?php echo esc_url( $listing->get_the_author_url() ); ?>"><?php $listing->the_author(); ?></a>
				<?php } ?>

				<?php if ( in_array( 'author_badges', $items ) ) { ?>
					<div class="rtcl-author-badge">
						<?php do_action( 'rtcl_listing_author_badges', $listing ); ?>
					</div>
				<?php } ?>

			</div>
		</div>
	</div>
<?php
if ( in_array( 'location', $items ) ) {
	TemplateHooks::seller_location( $listing );
}
if ( in_array( 'phone', $items ) ) {
	TemplateHooks::seller_phone_whatsapp_number( $listing );
}
if ( in_array( 'website', $items ) ) {
	TemplateHooks::seller_website( $listing );
}
if ( in_array( 'contact_form', $items ) ) {
	TemplateHooks::seller_email( $listing );
}