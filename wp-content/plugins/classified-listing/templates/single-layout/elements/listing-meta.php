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
use Rtcl\Services\FormBuilder\FBHelper;

defined( 'ABSPATH' ) || exit;
global $listing;
if ( ! is_a( $field, FBField::class ) || ! is_a( $listing, Listing::class ) ) {
	return;
}
$meta_field_obj = $field->getField();
$listing_meta   = $meta_field_obj['items'];
$direction      = $meta_field_obj['direction'] ?? 'horizontal';

if ( empty( $listing_meta ) ) {
	return;
}

if ( ! $listing->can_show_date() && ! $listing->can_show_user() && ! $listing->can_show_category() && ! $listing->can_show_location() && ! $listing->can_show_views() ) {
	return;
}
?>
<div class="rtcl-listing-meta rtcl-direction-<?php echo esc_attr( $direction ); ?>">
	<ul class="rtcl-listing-meta-data">
		<?php foreach ( $listing_meta as $meta ) :
			$type       = $meta['type'];
			$icon_class = ! empty( $meta['icon']['class'] ) ? esc_attr( $meta['icon']['class'] ) : '';

			switch ( $type ) {
				case 'type':
					if ( $listing->can_show_ad_type() ) {
						$listing_types = Functions::get_listing_types();
						$types         = isset( $listing_types[ $listing->get_ad_type() ] ) ? $listing_types[ $listing->get_ad_type() ] : '';
						if ( $types ) {
							echo '<li class="ad-type"><i class="rtcl-icon ' . $icon_class . '"></i> ' . esc_html( $types ) . '</li>';
						}
					}
					break;

				case 'date':
					if ( $listing->can_show_date() ) {
						echo '<li class="updated"><i class="rtcl-icon ' . $icon_class . '"></i> ';
						$listing->the_time();
						echo '</li>';
					}
					break;

				case 'time':
					// Optional: skip or combine with 'date'
					break;

				case 'author':
					if ( $listing->can_show_user() ) {
						echo '<li class="author"><i class="rtcl-icon ' . $icon_class . '"></i> ';
						esc_html_e( 'by ', 'classified-listing' );
						if ( $listing->can_add_user_link() && ! is_author() ) {
							echo '<a href="' . esc_url( $listing->get_the_author_url() ) . '">';
							$listing->the_author();
							echo '</a>';
						} else {
							$listing->the_author();
						}
						echo '</li>';
					}
					break;

				case 'category':
					if ( $listing->has_category() && $listing->can_show_category() ) {
						$categories = $listing->get_categories();
						if ( ! empty( $categories ) ) {
							echo '<li class="rt-categories"><i class="rtcl-icon ' . $icon_class . '"></i> ';
							$glue = '';
							foreach ( $categories as $category ) {
								echo $glue;
								echo '<a href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category->name ) . '</a>';
								$glue = '<span class="rtcl-delimiter">,</span>';
							}
							echo '</li>';
						}
					}
					break;

				case 'location':
					if ( $listing->has_location() && $listing->can_show_location() ) {
						echo '<li class="rt-location"><i class="rtcl-icon ' . $icon_class . '"></i> ';
						$listing->the_locations( true, true );
						echo '</li>';
					}
					break;

				case 'comments':
					// Example (optional)
					echo '<li class="rt-comments"><i class="rtcl-icon ' . $icon_class . '"></i> ';
					comments_number();
					echo '</li>';
					break;

				case 'view':
					if ( $listing->can_show_views() ) {
						echo '<li class="rt-views"><i class="rtcl-icon ' . $icon_class . '"></i> ';
						printf(
							esc_html( _n( '%s view', '%s views', $listing->get_view_counts(), 'classified-listing' ) ),
							esc_html( number_format_i18n( $listing->get_view_counts() ) )
						);
						echo '</li>';
					}
					break;
			}
		endforeach; ?>
	</ul>
</div>