<?php
/**
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 */

use Rtcl\Models\Listing;
use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Pagination;
use RadiusTheme\Petslist\Listing_Functions;
use RtclPro\Controllers\Hooks\TemplateHooks;
?>

<div class="rtcl rtcl-listings-sc-wrapper rtcl-elementor-widget">
	<div class="rtcl-listings-wrapper">
		<?php
		$class  = '';
		$class .= ! empty( $view ) ? 'rtcl-' . $view . '-view ' : 'rtcl-list-view ';
		$class .= ! empty( $style ) ? 'rtcl-' . $style . '-view ' : 'rtcl-style-1-view ';

		$class .= ! empty( $instance['rtcl_listings_column'] ) ? 'columns-' . $instance['rtcl_listings_column'] . ' ' : ' columns-1';
		$class .= ! empty( $instance['rtcl_listings_column_tablet'] ) ? 'tab-columns-' . $instance['rtcl_listings_column_tablet'] . ' ' : ' tab-columns-2';
		$class .= ! empty( $instance['rtcl_listings_column_mobile'] ) ? 'mobile-columns-' . $instance['rtcl_listings_column_mobile'] . ' ' : ' mobile-columns-2';

		?>
		<div class="rtcl-listings <?php echo esc_attr( $class ); ?> ">
			<?php

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
				$time                = null;
				$location            = null;
				$category            = null;
				$price               = null;
				$img_position_class  = '';
				$types               = null;
				$phone               = get_post_meta( $_id, 'phone', true );
				$custom_field 		= null;
				?>

				<div <?php Functions::listing_class( [ 'rtcl-widget-listing-item', 'listing-item', $img_position_class ] ); ?>>
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
					</div>
					
					<!-- All Meta Info Box -->
					<div class="item-content">
						<?php if ( $instance['rtcl_show_category'] ): ?>
							<div class="rt-category">
								<?php echo wp_kses_post( $listing->the_categories( false, true )); ?>
							</div>
						<?php endif; ?>
						<div class="title-excerpt-box">
							<div class="title-price-box">
								<?php if ( $instance['rtcl_show_title'] ) { ?>
									<h3 class="listing-title rtcl-listing-title"><a href="<?php the_permalink(); ?>" title="<?php the_title() ?>"><?php the_title() ?></a></h3>
								<?php } ?>
								<?php 
									if ( $instance['rtcl_show_price'] ) {
										echo wp_kses_post($listing->get_price_html()); 
									}
								?>
							</div>
							<?php if ( $instance['rtcl_show_description'] ) { ?>
								<div class="rtcl-short-description">
									<?php 
										if (!empty($instance['rtcl_content_limit'])) {
											Listing_Functions::petslist_listing_excerpt($instance['rtcl_content_limit']); 
										} else {
											Listing_Functions::petslist_listing_excerpt($instance['rtcl_content_limit']); 
										}
									?>
								</div>
							<?php } ?>
						</div>

						<?php if (!empty($instance['rtcl_show_custom_fields'])) { ?>
							<div class="custom-flelds-box">
								<?php TemplateHooks::loop_item_listable_fields(); ?>
							</div>
						<?php } ?>

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

								<?php if ( $instance['rtcl_show_date'] ) { ?>
									<li class="date-time">
										<i class="icon-pl-clock"></i>
										<?php 
											Listing_Functions::petslist_the_time(get_the_ID());
										?>
									</li>
								<?php } ?>
							</ul>

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
					</div>
				</div>

			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</div>
		<?php if ( ! empty( $instance['rtcl_listing_pagination'] ) ) { ?>
			<?php Pagination::pagination( $the_loops, true ); ?>
		<?php } ?>
	</div>
</div>
