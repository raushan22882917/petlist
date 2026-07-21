<?php
/**
 * Front page template (no Elementor) - matches the Petslist demo home design.
 */
get_header();

$register_url = function_exists('dd_register_url') ? dd_register_url() : wp_registration_url();
$directory_url = function_exists('dd_dog_directory_url') ? dd_dog_directory_url() : home_url('/dog-directory/');

$hero_bg = petslist_img_url('hero_bg');
?>

<main id="primary" class="content-area petslist-custom-page petslist-home-page">

	<!-- ============ HERO ============ -->
	<section class="petslist-home-hero"
		style="background-color:#02c5bd;background-image:url('<?php echo esc_url($hero_bg); ?>');">
		<div class="container">
			<div class="petslist-home-hero__grid">
				<div class="petslist-home-hero__text">
					<div class="section-heading">
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- ============ ALL BREEDS WELCOME ============ -->
	<div class="dd-welcome-bar" style="background-color: #D3D3D3; padding: 15px 0; text-align: center;">
		<div class="container">
			<h3 style="color: #070c3e; margin: 0; font-weight: 700; letter-spacing: 1px; font-family: 'Baloo Bhaijaan 2', sans-serif; text-transform: uppercase;">
				<?php esc_html_e('ALL BREEDS WELCOME!', 'petslist'); ?>
			</h3>
		</div>
	</div>

	<!-- ============ TWO BANNER CARDS ============ -->
	<section class="petslist-home-banners">
		<div class="container">
			<div class="petslist-home-banners__grid">
				<div class="petslist-home-search-standalone">
					<h2 class="heading-title"><?php esc_html_e('Search Studs Directory', 'petslist'); ?></h2>
					<?php
					if (class_exists('Rtcl') && class_exists('\RadiusTheme\Petslist\Helper')):
						?>
						<div class="rtcl petslist-listing-search petslist-home-hero__search">
							<?php \RadiusTheme\Petslist\Helper::get_custom_listing_template('listing-header-search'); ?>
						</div>
						<?php
					endif;
					?>
				</div>
				<div class="petslist-home-banner-card petslist-home-banner-card--blue">
					<div class="petslist-home-banner-card__content">
						<h2 class="heading-title"><?php esc_html_e("Let's get you connected.", 'petslist'); ?></h2>
						<a href="<?php echo esc_url($register_url); ?>"
							class="button-style-1"><?php esc_html_e('Create An Account', 'petslist'); ?><i
								aria-hidden="true" class="icon-pl-right-arrow"></i></a>
					</div>
					<img src="<?php echo esc_url(petslist_img_url('banner_blue')); ?>" alt=""
						class="petslist-home-banner-card__img" loading="lazy">
				</div>
			</div>
		</div>
	</section>

	<!-- ============ LATEST DOG PROFILES ============ -->
	<section class="petslist-home-directory">
		<div class="container">
			<div class="petslist-home-directory__grid">
				<aside class="petslist-home-directory__side">
					<?php petslist_render_dog_breeds(16); ?>
				</aside>
				<div class="petslist-home-directory__main">
					<!-- ============ FLYER ADS ============ -->
					<div class="petslist-home-flyers-inline">
						<div class="section-heading" style="margin-bottom: 26px;">
							<h2 class="heading-title"><?php esc_html_e('Flyer Ads', 'petslist'); ?></h2>
						</div>
						<div class="petslist-flyer-grid">
							<?php
							// Fetch sponsored dogs (real ads)
							$all_dogs = get_posts( array(
								'post_type'      => 'dd_dog',
								'post_status'    => 'publish',
								'posts_per_page' => 9,
							) );
							$real_ads = array_filter( $all_dogs, function( $post ) {
								$meta = get_post_meta( $post->ID, '_dd_dog_meta', true ) ?: [];
								return isset( $meta['is_sponsored'] ) && $meta['is_sponsored'] === 'Yes';
							} );

							if ( ! empty( $real_ads ) ) {
								// Show real ads
								foreach ( $real_ads as $post ) {
									setup_postdata( $post );
									$pid = $post->ID;
									$meta = dd_get_dog_meta( $pid );
									$thumb = get_the_post_thumbnail_url( $pid, 'large' ) 
										?: ( get_post_meta( $pid, '_dd_front_photo', true ) 
											? wp_get_attachment_url( get_post_meta( $pid, '_dd_front_photo', true ) ) 
											: petslist_theme_img_url( 'dog-placeholder.svg' ) );
									$terms = get_the_terms( $pid, 'dd_breed' );
									$breed = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : ( $meta['breed'] ?? '' );
									?>
									<div class="petslist-flyer-card">
										<div class="petslist-flyer-card__image">
											<a href="<?php echo esc_url( get_permalink( $pid ) ); ?>">
												<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $pid ) ); ?>" loading="lazy">
											</a>
										</div>
										<div class="petslist-flyer-card__content">
											<h3><a href="<?php echo esc_url( get_permalink( $pid ) ); ?>" style="color: inherit; text-decoration: none;"><?php echo esc_html( get_the_title( $pid ) ); ?></a></h3>
											<p><?php echo esc_html( $breed ?: __( 'Premium dog breeding ad', 'petslist' ) ); ?></p>
										</div>
									</div>
									<?php
								}
								wp_reset_postdata();
							} else {
								$sample_flyer = file_exists( WP_CONTENT_DIR . '/uploads/2026/06/download.jpeg' )
									? content_url( 'uploads/2026/06/download.jpeg' )
									: ( file_exists( WP_CONTENT_DIR . '/uploads/2026/06/download-1.jpeg' )
										? content_url( 'uploads/2026/06/download-1.jpeg' )
										: petslist_theme_img_url( 'dog-placeholder.svg' ) );

								for ($i = 1; $i <= 9; $i++) : ?>
									<div class="petslist-flyer-card">
										<div class="petslist-flyer-card__image">
											<img src="<?php echo esc_url($sample_flyer); ?>" alt="<?php echo esc_attr(sprintf(__('Flyer Ad %d', 'petslist'), $i)); ?>" loading="lazy">
										</div>
										<div class="petslist-flyer-card__content">
											<h3><?php echo esc_html(sprintf(__('Featured Flyer %d', 'petslist'), $i)); ?></h3>
											<p><?php _e('Premium kennel showcase and breeder flyer ad space.', 'petslist'); ?></p>
										</div>
									</div>
								<?php endfor;
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

</main>

<?php get_footer(); ?>