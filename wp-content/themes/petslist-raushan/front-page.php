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
		style="background-image:url('<?php echo esc_url($hero_bg); ?>');">
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
					<h2 class="heading-title"><?php esc_html_e('Search Stud Directory', 'petslist'); ?></h2>
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
					<?php petslist_render_dog_breeds(); ?>
				</aside>
				<div class="petslist-home-directory__main">
					<!-- ============ FLYER ADS ============ -->
					<div class="petslist-home-flyers-inline">
						<div class="section-heading" style="margin-bottom: 16px;">
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
									$thumb = get_the_post_thumbnail_url( $pid, 'large' );
									if ( ! $thumb ) {
										$front_id = get_post_meta( $pid, '_dd_front_photo', true );
										$thumb = $front_id ? wp_get_attachment_url( $front_id ) : '';
									}
									if ( ! $thumb || strpos( $thumb, 'download-2' ) !== false || strpos( $thumb, 'dog-placeholder' ) !== false ) {
										$thumb = petslist_theme_img_url( 'theme/flyer-ad-placeholder.png' );
									}
									$terms = get_the_terms( $pid, 'dd_breed' );
									$breed = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : ( $meta['breed'] ?? '' );
									?>
									<div class="petslist-flyer-card">
										<div class="petslist-flyer-card__image">
											<a href="<?php echo esc_url( $thumb ); ?>" class="dd-flyer-lightbox-trigger" data-full="<?php echo esc_url( $thumb ); ?>" data-title="<?php echo esc_attr( get_the_title( $pid ) ); ?>">
												<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $pid ) ); ?>" loading="lazy">
											</a>
										</div>
										<div class="petslist-flyer-card__content">
											<h3><a href="<?php echo esc_url( $thumb ); ?>" class="dd-flyer-lightbox-trigger" data-full="<?php echo esc_url( $thumb ); ?>" data-title="<?php echo esc_attr( get_the_title( $pid ) ); ?>" style="color: inherit; text-decoration: none;"><?php echo esc_html( get_the_title( $pid ) ); ?></a></h3>
											<p><?php echo esc_html( $breed ?: __( 'Premium dog breeding ad', 'petslist' ) ); ?></p>
										</div>
									</div>
									<?php
								}
								wp_reset_postdata();
							} else {
								$sample_flyer = petslist_theme_img_url( 'theme/flyer-ad-placeholder.png' );

								for ($i = 1; $i <= 9; $i++) : ?>
									<div class="petslist-flyer-card">
										<div class="petslist-flyer-card__image">
											<a href="<?php echo esc_url($sample_flyer); ?>" class="dd-flyer-lightbox-trigger" data-full="<?php echo esc_url($sample_flyer); ?>" data-title="<?php echo esc_attr(sprintf(__('Featured Flyer %d', 'petslist'), $i)); ?>">
												<img src="<?php echo esc_url($sample_flyer); ?>" alt="<?php echo esc_attr(sprintf(__('Flyer Ad %d', 'petslist'), $i)); ?>" loading="lazy">
											</a>
										</div>
										<div class="petslist-flyer-card__content">
											<h3><a href="<?php echo esc_url($sample_flyer); ?>" class="dd-flyer-lightbox-trigger" data-full="<?php echo esc_url($sample_flyer); ?>" data-title="<?php echo esc_attr(sprintf(__('Featured Flyer %d', 'petslist'), $i)); ?>" style="color: inherit; text-decoration: none;"><?php echo esc_html(sprintf(__('Featured Flyer %d', 'petslist'), $i)); ?></a></h3>
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

	<!-- ============ FLYER LIGHTBOX MODAL ============ -->
	<div id="dd-flyer-lightbox-modal" class="dd-flyer-modal" style="display:none;">
		<div class="dd-flyer-modal__backdrop"></div>
		<div class="dd-flyer-modal__content">
			<button type="button" class="dd-flyer-modal__close" aria-label="Close">&times;</button>
			<img id="dd-flyer-modal-img" src="" alt="Flyer Ad">
			<div id="dd-flyer-modal-caption" class="dd-flyer-modal__caption"></div>
		</div>
	</div>

	<script>
	document.addEventListener('DOMContentLoaded', function() {
		var modal = document.getElementById('dd-flyer-lightbox-modal');
		var modalImg = document.getElementById('dd-flyer-modal-img');
		var caption = document.getElementById('dd-flyer-modal-caption');
		var closeBtn = modal ? modal.querySelector('.dd-flyer-modal__close') : null;
		var backdrop = modal ? modal.querySelector('.dd-flyer-modal__backdrop') : null;

		document.querySelectorAll('.dd-flyer-lightbox-trigger').forEach(function(trigger) {
			trigger.addEventListener('click', function(e) {
				e.preventDefault();
				var fullSrc = this.getAttribute('data-full') || this.getAttribute('href');
				var title = this.getAttribute('data-title') || '';
				if (fullSrc && modal && modalImg) {
					modalImg.src = fullSrc;
					if (caption) caption.textContent = title;
					modal.style.display = 'flex';
					document.body.style.overflow = 'hidden';
				}
			});
		});

		function closeModal() {
			if (modal) {
				modal.style.display = 'none';
				if (modalImg) modalImg.src = '';
				document.body.style.overflow = '';
			}
		}

		if (closeBtn) closeBtn.addEventListener('click', closeModal);
		if (backdrop) backdrop.addEventListener('click', closeModal);
		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
				closeModal();
			}
		});
	});
	</script>

</main>

<?php get_footer(); ?>