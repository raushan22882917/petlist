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

defined( 'ABSPATH' ) || exit;
global $listing;
if ( ! is_a( $field, FBField::class ) || ! is_a( $listing, Listing::class ) ) {
	return;
}
$video_urls = get_post_meta( $listing->get_id(), '_rtcl_video_urls', true );
$video_urls = is_array( $video_urls ) && ! empty( $video_urls ) ? $video_urls : [];

if ( empty( $video_urls ) ) {
	return;
} ?>
<div class="rtcl-sl-element rtcl-slf-videos">
	<?php
	foreach ( $video_urls as $index => $video_url ) { ?>
		<div class="swiper-slide rtcl-slider-item rtcl-slider-video-item">
			<iframe class="rtcl-lightbox-iframe"
					data-src="<?php echo esc_url(Functions::get_sanitized_embed_url( $video_url )) ?>"
					src="<?php echo esc_url(Functions::get_sanitized_embed_url( $video_url )) ?>"
					style="width: 100%; height: 400px; margin: 0;padding: 0; background-color: #000"
					frameborder="0" webkitAllowFullScreen
					mozallowfullscreen allowFullScreen></iframe>
		</div>
		<?php
	}
	?>
</div>