<?php
/**
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 */

use Rtcl\Helpers\Functions;
use Rtcl\Models\Listing;
use RadiusTheme\Petslist\Listing_Functions;
use RtclPro\Controllers\Hooks\TemplateHooks;

$data = [
	'template'              => 'elementor/listing-ads-slider/slider-header',
	'view'                  => $view,
	'style'                 => $style,
	'instance'              => $instance,
	'the_loops'             => $the_loops,
	'default_template_path' => $default_template_path,
];
$data = apply_filters( 'rtcl_el_listing_slider_filter_data', $data );
Functions::get_template( $data['template'], $data, '', $data['default_template_path'] );

while ( $the_loops->have_posts() ) :
	$the_loops->the_post();
	$_id                 = get_the_ID();
	$post_meta           = get_post_meta( $_id );
	$listing             = new Listing( $_id );
	$listing_title       = null;
	$listing_meta        = null;
	$listing_description = null;
	$img                 = null;
	$labels              = null;
	$u_info              = null;
	$time                = null;
	$location            = null;
	$category            = null;
	$price               = null;
	$img_position_class  = '';
	$types               = null;
	$custom_field 		= null;
	$phone               = get_post_meta( $_id, 'phone', true );
	?>

	<div <?php Functions::listing_class( [ 'rtcl-widget-listing-item', 'listing-item swiper-slide-customize', $img_position_class ] ); ?>>	
		<!-- Thumbnail Image Box -->
		<div class="item-img bg--gradient-50">
			<div class="petslist-listing-actions-buttons">
				<?php 
					if ( $instance['rtcl_show_types'] && $listing->get_ad_type() ) {
					$listing_type = Listing_Functions::get_listing_type( $listing );
					?>
						<span class="listing-type-badge">
							<?php echo sprintf( "%s %s", apply_filters( 'rtcl_type_prefix', __( 'For', 'petslist' ) ), $listing_type['label'] ); ?>
						</span>
				<?php } ?>
				
				<?php 
					if ( $instance['rtcl_show_labels'] ) {
						$labels = $listing->badges() ? $listing->badges() : '';
						echo wp_kses_post($labels); 
					}
				?>
			</div>
			<?php 
			if ( $instance['rtcl_show_image'] ) {
				$image_size    = $instance['rtcl_thumb_image_size'];
				$the_thumbnail = $listing->get_the_thumbnail( $image_size );
				if ( $the_thumbnail ) { ?>
			<div class='listing-thumb'>
				<?php 
					if ( rtcl()->has_pro() ) {
						TemplateHooks::sold_out_banner();
					}
				?>
				<a href="<?php esc_url( get_the_permalink() ); ?>" title="<?php the_title() ?>"><?php echo wp_kses_post($the_thumbnail); ?></a> 
			</div>
			<?php }
				} ?>
			<ul class="meta-tags">
				<?php if ( $instance['rtcl_show_favourites'] ) { ?>
					<?php if ( is_user_logged_in() ) { ?>
						<li class="meta-item meta-favourite">
							<?php echo Listing_Functions::get_favourites_link( $listing->get_id() ); ?>
						</li>
					<?php } else { ?>
						<li class="meta-item meta-favourite">
							<?php echo Listing_Functions::get_favourites_link( $listing->get_id() ); ?>
						</li>
					<?php } ?>
				<?php } if ( rtcl()->has_pro() ) { 
					if ( ! empty( $instance['rtcl_show_compare'] ) ){
					?>
					<li class="meta-compare">
					<?php
						$compare_ids = ! empty( $_SESSION['rtcl_compare_ids'] ) ? $_SESSION['rtcl_compare_ids'] : [];
						$selected_class = '';
						if ( is_array( $compare_ids ) && in_array( $listing->get_id(), $compare_ids ) ) {
							$selected_class = ' selected';
						}
					?>
					<a class="rtcl-compare <?php echo esc_attr( $selected_class ); ?>" href="#" data-listing_id="<?php echo absint( $listing->get_id() ) ?>">
						<i class="icon-compare"></i>
					</a>
					</li>
					<?php }
					}
					if ( rtcl()->has_pro() ) {
						if ( ! empty( $instance['rtcl_show_quick_view'] ) ) :
							?>
							<li class="rtin-el-button">
								<a class="rtcl-quick-view" href="#" title="<?php esc_attr_e( 'Quick View', 'petslist' ); ?>"  data-listing_id="<?php echo absint( $_id ); ?>">
									<i class="icon-pl-eye"></i>
								</a>
							</li>
							<?php
						endif;
					} 
				?>
			</ul>
		</div>
					
		<!-- All Meta Info Box -->
		<div class="item-content">
			<div class="title-excerpt-box">
				<?php if ( $instance['rtcl_show_title'] ) { ?>
					<h3 class="listing-title rtcl-listing-title"><a href="<?php the_permalink(); ?>" title="<?php the_title() ?>"><?php the_title() ?></a></h3>
				<?php } ?>
				<?php 
					if ( $instance['rtcl_show_description'] ) {
						$excerpt = get_the_excerpt( $_id );
					?>
						<div class="rtcl-short-description"><?php echo wpautop( $excerpt ); ?></div>
					<?php } 
				?>
			</div>

			<?php if (!empty($instance['rtcl_show_custom_fields'])) { ?>
				<div class="custom-flelds-box">
					<?php TemplateHooks::loop_item_listable_fields(); ?>
				</div>
			<?php } ?>
			<?php if ( !empty ( $instance['rtcl_show_user'] || $instance['rtcl_show_location'] || $instance['rtcl_show_views'] || $instance['rtcl_show_category'] )): ?>
			<div class="all-meta-info-box">
				<ul class="rtcl-listing-meta-data">
					<?php if ( $instance['rtcl_show_user'] ) : ?>
						<li class="author">
							<i class="icon-pl-account"></i>
							<?php esc_html_e( 'by ', 'petslist' ); ?>
							<?php if ( $listing->can_add_user_link() && ! is_author() ) : ?>
								<a href="<?php echo esc_url( $listing->get_the_author_url() ); ?>"><?php $listing->the_author(); ?></a>
							<?php else : ?>
								<?php $listing->the_author(); ?>
							<?php endif; ?>
							<?php do_action( 'rtcl_after_author_meta', $listing->get_owner_id() ); ?>
						</li>
					<?php endif; ?>
					<?php
					if ( $instance['rtcl_show_location'] ) :
						?>
						<li class="rt-location">
							<i class="icon-pl-location"></i>
							<?php $listing->the_locations( true, true ); ?>
						</li>
					<?php endif; ?>
					<?php if ( $instance['rtcl_show_views'] ) : ?>
						<li class="rt-views">
							<i class="icon-pl-eye"></i>
							<?php echo sprintf( _n( '%s view', '%s views', $listing->get_view_counts(), 'petslist' ), number_format_i18n( $listing->get_view_counts() ) ); ?>
						</li>
					<?php endif; ?>
					<?php if ( $instance['rtcl_show_category'] ): ?>
						<li class="rt-category">
							<i class="icon-pl-tag"></i>
							<?php echo wp_kses_post( $listing->the_categories( false, true )); ?>
						</li>
					<?php endif; ?>
				</ul>
			</div>
			<?php endif; ?>
			<div class="price-time-box">
				<?php 
					if ( $instance['rtcl_show_price'] ) {
						echo wp_kses_post($listing->get_price_html()); 
					}
				?>
					<?php if ( $instance['rtcl_show_date'] ) { ?>
					<div class="date-time">
						<?php 
							Listing_Functions::petslist_the_time(get_the_ID());
						?>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>

<?php endwhile; ?>
<?php wp_reset_postdata(); ?>
<?php

$data = [
	'template'              => 'elementor/listing-ads-slider/slider-footer',
	'view'                  => $view,
	'style'                 => $style,
	'instance'              => $instance,
	'the_loops'             => $the_loops,
	'default_template_path' => $default_template_path,
];
$data = apply_filters( 'rtcl_el_listing_slider_filter_data', $data );
Functions::get_template( $data['template'], $data, '', $data['default_template_path'] );
