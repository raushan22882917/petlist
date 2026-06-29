<?php
/**
 *Manage Listing by user
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var WP_Query $rtcl_query
 */


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Pagination;

global $post;
?>

<?php if ( $rtcl_query->have_posts() ): ?>
	<div class="rtcl-MyAccount-content-inner">
		<table class="rtcl-my-listing-table">
			<thead>
			<tr>
				<th><?php esc_html_e( 'Thumbnail', 'classified-listing' ); ?></th>
				<th class="title-cell"><?php esc_html_e( 'Title', 'classified-listing' ); ?></th>
				<th class="price-cell list-on-responsive"><?php esc_html_e( 'Price', 'classified-listing' ); ?></th>
				<th class="list-on-responsive"><?php esc_html_e( 'Expires On', 'classified-listing' ); ?></th>
				<th class="list-on-responsive"><?php esc_html_e( 'Status', 'classified-listing' ); ?></th>
				<th class="list-on-responsive"><?php esc_html_e( 'Action', 'classified-listing' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php while ( $rtcl_query->have_posts() ) : $rtcl_query->the_post();
				$post_meta  = get_post_meta( $post->ID );
				$listing    = rtcl()->factory->get_listing( $post->ID );
				$is_top     = (bool) get_post_meta( $listing->get_id(), '_top', true );
				$is_bump_up = (bool) get_post_meta( $listing->get_id(), '_bump_up', true );
				?>
				<tr class="rtcl-my-listing-row-<?php echo esc_attr( $listing->get_status() ); ?>">
					<td>
						<div class="listing-thumb">
							<a href="<?php the_permalink(); ?>"><?php $listing->the_thumbnail(); ?></a>
							<?php do_action( 'rtcl_my_listing_after_listing_thumb', $listing ); ?>
						</div>
					</td>
					<td class="title-cell">
						<div class="rtcl-ad-details">
							<a class="listing-title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							<?php $listing->the_badges(); ?>
							<ul class="rtcl-meta">
								<li>
									<svg width="16" height="16" viewBox="0 0 16 16" fill="none"
										 xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" clip-rule="evenodd"
											  d="M7.99941 1.60002C4.46479 1.60002 1.59941 4.4654 1.59941 8.00002C1.59941 11.5346 4.46479 14.4 7.99941 14.4C11.534 14.4 14.3994 11.5346 14.3994 8.00002C14.3994 4.4654 11.534 1.60002 7.99941 1.60002ZM0.399414 8.00002C0.399414 3.80266 3.80205 0.400024 7.99941 0.400024C12.1968 0.400024 15.5994 3.80266 15.5994 8.00002C15.5994 12.1974 12.1968 15.6 7.99941 15.6C3.80205 15.6 0.399414 12.1974 0.399414 8.00002ZM7.99941 3.20002C8.33078 3.20002 8.59941 3.46865 8.59941 3.80002V7.6292L11.0677 8.86337C11.3641 9.01156 11.4843 9.37196 11.3361 9.66835C11.1879 9.96474 10.8275 10.0848 10.5311 9.93668L7.73108 8.53668C7.52781 8.43505 7.39941 8.22729 7.39941 8.00002V3.80002C7.39941 3.46865 7.66804 3.20002 7.99941 3.20002Z"
											  fill="currentColor"/>
									</svg>
									<?php $listing->the_time(); ?>
								</li>
								<?php if ( $listing->has_category() ): ?>
									<li>
										<svg width="16" height="16" viewBox="0 0 16 16" fill="none"
											 xmlns="http://www.w3.org/2000/svg">
											<path fill-rule="evenodd" clip-rule="evenodd"
												  d="M0.399414 1.00002C0.399414 0.668653 0.668044 0.400024 0.999414 0.400024H8.30189C8.46104 0.400024 8.61368 0.46326 8.72621 0.575815L15.0003 6.85154C15.384 7.23769 15.5994 7.76 15.5994 8.30441C15.5994 8.84881 15.384 9.37113 15.0003 9.75727L14.999 9.75853L9.76339 14.9955C9.57204 15.1871 9.34479 15.3392 9.09464 15.4429C8.84448 15.5466 8.57634 15.6 8.30554 15.6C8.03474 15.6 7.76659 15.5466 7.51644 15.4429C7.26649 15.3392 7.03941 15.1874 6.84815 14.996C6.8483 14.9961 6.84799 14.9958 6.84815 14.996L0.575342 8.72886C0.462703 8.61633 0.399414 8.46363 0.399414 8.30441V1.00002ZM1.59941 1.60002V8.05572L7.69631 14.1471C7.77623 14.2271 7.87162 14.2911 7.97607 14.3344C8.08052 14.3777 8.19247 14.4 8.30554 14.4C8.4186 14.4 8.53056 14.3777 8.635 14.3344C8.73945 14.2911 8.83436 14.2276 8.91428 14.1476L14.1491 8.91138C14.1493 8.91119 14.1489 8.91157 14.1491 8.91138C14.309 8.75015 14.3994 8.53162 14.3994 8.30441C14.3994 8.0772 14.3096 7.85924 14.1497 7.69801C14.1499 7.6982 14.1495 7.69782 14.1497 7.69801L8.05331 1.60002H1.59941ZM4.05065 4.65222C4.05065 4.32084 4.31928 4.05222 4.65065 4.05222H4.65795C4.98932 4.05222 5.25795 4.32084 5.25795 4.65222C5.25795 4.98359 4.98932 5.25222 4.65795 5.25222H4.65065C4.31928 5.25222 4.05065 4.98359 4.05065 4.65222Z"
												  fill="currentColor"/>
										</svg>
										<?php $listing->the_categories( true, true ); ?>
									</li>
								<?php endif; ?>
								<?php if ( $listing->has_location() && apply_filters( 'rtcl_my_listing_location_display', false ) ): ?>
									<li>
										<svg width="13" height="16" viewBox="0 0 13 16" fill="none"
											 xmlns="http://www.w3.org/2000/svg">
											<path fill-rule="evenodd" clip-rule="evenodd"
												  d="M6.49941 1.60002C5.20787 1.60002 3.96406 2.13406 3.04309 3.09309C2.12132 4.05295 1.59941 5.3598 1.59941 6.7273C1.59941 8.7224 2.84381 10.6471 4.19323 12.1303C4.85707 12.86 5.52277 13.4571 6.02302 13.8719C6.20793 14.0253 6.3696 14.1532 6.49941 14.2531C6.62922 14.1532 6.7909 14.0253 6.9758 13.8719C7.47605 13.4571 8.14176 12.86 8.8056 12.1303C10.155 10.6471 11.3994 8.7224 11.3994 6.7273C11.3994 5.3598 10.8775 4.05295 9.95571 3.09309C9.03481 2.13406 7.79095 1.60002 6.49941 1.60002ZM6.49941 15C6.15725 15.4929 6.1571 15.4928 6.15692 15.4926L6.15519 15.4914L6.15114 15.4886L6.13717 15.4788C6.12529 15.4704 6.10835 15.4583 6.08667 15.4427C6.04332 15.4114 5.98101 15.3658 5.90245 15.3068C5.74538 15.1886 5.523 15.0162 5.25705 14.7957C4.72605 14.3554 4.01676 13.7195 3.3056 12.9379C1.90502 11.3984 0.399414 9.18674 0.399414 6.7273C0.399414 5.05686 1.03643 3.4502 2.17756 2.26191C3.31949 1.0728 4.87357 0.400024 6.49941 0.400024C8.12525 0.400024 9.67931 1.0728 10.8213 2.26191C11.9624 3.4502 12.5994 5.05686 12.5994 6.7273C12.5994 9.18674 11.0938 11.3984 9.69321 12.9379C8.98207 13.7195 8.27277 14.3554 7.74177 14.7957C7.47582 15.0162 7.25344 15.1886 7.09637 15.3068C7.01781 15.3658 6.9555 15.4114 6.91215 15.4427C6.89047 15.4583 6.87353 15.4704 6.86166 15.4788L6.84769 15.4886L6.84363 15.4914L6.84235 15.4923C6.84218 15.4924 6.84157 15.4929 6.49941 15ZM6.49941 15L6.84235 15.4923C6.6366 15.6352 6.36267 15.6355 6.15692 15.4926L6.49941 15ZM6.49941 5.41821C5.84093 5.41821 5.26608 5.98118 5.26608 6.7273C5.26608 7.47342 5.84093 8.03639 6.49941 8.03639C7.1579 8.03639 7.73275 7.47342 7.73275 6.7273C7.73275 5.98118 7.1579 5.41821 6.49941 5.41821ZM4.06608 6.7273C4.06608 5.36469 5.13285 4.21821 6.49941 4.21821C7.86597 4.21821 8.93275 5.36469 8.93275 6.7273C8.93275 8.0899 7.86597 9.23639 6.49941 9.23639C5.13285 9.23639 4.06608 8.0899 4.06608 6.7273Z"
												  fill="currentColor"/>
										</svg>
										<?php $listing->the_locations( true, true ); ?>
									</li>
								<?php endif; ?>
								
								<?php do_action( 'rtcl_my_listings_meta_lists', $listing ); ?>
							</ul>
							<div class="rtcl-listing-analytics-meta">
								<?php if ( apply_filters( 'rtcl_my_listing_analytics_button_display', true ) ) { ?>
									<span>
										<button type="button" class="rtcl-listing-analytics-btn"
												data-listing-id="<?php echo esc_attr( $listing->get_id() ); ?>"
												title="<?php esc_attr_e( 'View analytics', 'classified-listing' ); ?>"
												aria-label="<?php esc_attr_e( 'View analytics', 'classified-listing' ); ?>">
											<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="margin-right: 0px">
												<path fill-rule="evenodd" clip-rule="evenodd" d="M1.4 1c0-.3-.3-.6-.6-.6S.2.7.2 1v12.4c0 .8.7 1.5 1.5 1.5H15c.3 0 .6-.3.6-.6s-.3-.6-.6-.6H1.7c-.2 0-.3-.1-.3-.3V1Zm13.3 3.2c.2-.2.2-.6 0-.8a.6.6 0 0 0-.9 0l-3.4 3.5-2.2-2.2a.6.6 0 0 0-.9 0L3.5 7.8a.6.6 0 1 0 .9.9L7.7 5.4l2.2 2.2c.3.2.7.2.9 0l3.9-3.4Z" fill="currentColor"/>
												<rect x="3" y="9.4" width="1.6" height="3" rx="0.4" fill="currentColor"/>
												<rect x="7.2" y="7.6" width="1.6" height="4.8" rx="0.4" fill="currentColor"/>
												<rect x="11.4" y="6.2" width="1.6" height="6.2" rx="0.4" fill="currentColor"/>
											</svg>
										</button>
									</span>
								<?php } ?>
							</div>
							<div
								class="listing-status-mobile <?php echo esc_attr( strtolower( $post->post_status ) ); ?>">
								<span><?php esc_html_e( 'Status:', 'classified-listing' ); ?></span>
								<span><?php echo esc_html( Functions::get_status_i18n( $post->post_status ) ); ?></span>
							</div>
						</div>
						<span class="rtcl-my-listings-table-toggle-info">
								<span class="rtcl-icon rtcl-icon-angle-down"></span>
							</span>
						<?php do_action( 'rtcl_listing_loop_extra_meta', $listing ); ?>
					</td>
					<td class="price-cell list-on-responsive"
						data-column="<?php esc_html_e( 'Price:', 'classified-listing' ); ?>">
						<?php if ( $listing->can_show_price() ) {
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							Functions::print_html( $listing->get_price_html() );
						} ?>
					</td>
					<td class="list-on-responsive"
						data-column="<?php esc_html_e( 'Expires On:', 'classified-listing' ); ?>">
						<?php
						if ( $listing->get_status() !== 'pending' ) {
							if ( get_post_meta( $listing->get_id(), 'never_expires', true ) ) {
								esc_html_e( 'Never Expires', 'classified-listing' );
							} else if ( $expiry_date = get_post_meta( $listing->get_id(), 'expiry_date', true ) ) {
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $expiry_date ) );
							}
						}
						?>
					</td>
					<td class="status-cell list-on-responsive"
						data-column="<?php esc_html_e( 'Status:', 'classified-listing' ); ?>">
							<span class="<?php echo esc_attr( strtolower( $post->post_status ) ); ?>">
								<?php echo esc_html( Functions::get_status_i18n( $post->post_status ) ); ?>
							</span>
					</td>
					<td class="list-on-responsive"
						data-column="<?php esc_html_e( 'Action:', 'classified-listing' ); ?>">
						<?php if ( apply_filters( 'rtcl_my_listing_actions_button_display', true ) ) { ?>
							<div class="rtcl-actions-wrap">
									<span class="actions-dot">
										<svg width="18" height="4" viewBox="0 0 18 4" fill="none"
											 xmlns="http://www.w3.org/2000/svg">
											<circle cx="2" cy="2" r="2" fill="#646464"/>
											<circle cx="9" cy="2" r="2" fill="#646464"/>
											<circle cx="16" cy="2" r="2" fill="#646464"/>
										</svg>
									</span>
								<div class="rtcl-actions">
									<?php do_action( 'rtcl_my_listing_actions', $listing ); ?>
								</div>
							</div>
						<?php } ?>
					</td>
				</tr>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
			</tbody>
		</table>
	</div>
	<!-- pagination here -->
	<?php Pagination::pagination( $rtcl_query, true ); ?>
<?php else: ?>
	<p class="rtcl-no-data-found"><?php esc_html_e( "No listing found.", 'classified-listing' ); ?></p>
<?php endif; ?>