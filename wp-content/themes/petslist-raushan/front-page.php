<?php
/**
 * Front page template (no Elementor) - matches the Petslist demo home design.
 */
get_header();

$register_url = function_exists( 'dd_register_url' ) ? dd_register_url() : wp_registration_url();
$directory_url = function_exists( 'dd_dog_directory_url' ) ? dd_dog_directory_url() : home_url( '/dog-directory/' );

$hero_bg = petslist_img_url( 'hero_bg' );
?>

<main id="primary" class="content-area petslist-custom-page petslist-home-page">

	<!-- ============ HERO ============ -->
	<section class="petslist-home-hero" style="background-color:#02c5bd;background-image:linear-gradient(rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.2)), url('<?php echo esc_url( $hero_bg ); ?>');">
		<div class="container">
			<div class="petslist-home-hero__grid">
				<div class="petslist-home-hero__text">
					<div class="section-heading">
						<h1 class="heading-title"><?php esc_html_e( 'Find The Perfect Pet For You!', 'petslist' ); ?></h1>
						<p><?php esc_html_e( 'Browse pets from our network of over 11,500 shelters and rescues.', 'petslist' ); ?></p>
					</div>

				</div>
				<div class="petslist-home-hero__visual">
				</div>
			</div>
		</div>
	</section>

	<!-- ============ TWO BANNER CARDS ============ -->
	<section class="petslist-home-banners">
		<div class="container">
			<div class="petslist-home-banners__grid">
				<div class="petslist-home-search-standalone">
					<h2 class="heading-title"><?php esc_html_e( 'Search Dog Directory', 'petslist' ); ?></h2>
					<?php
					if ( class_exists( 'Rtcl' ) && class_exists( '\RadiusTheme\Petslist\Helper' ) ) :
						?>
						<div class="rtcl petslist-listing-search petslist-home-hero__search">
							<?php \RadiusTheme\Petslist\Helper::get_custom_listing_template( 'listing-header-search' ); ?>
						</div>
						<?php
					endif;
					?>
				</div>
				<div class="petslist-home-banner-card petslist-home-banner-card--blue">
					<div class="petslist-home-banner-card__content">
						<h2 class="heading-title"><?php esc_html_e( 'Let’s Find Your New Best Friend', 'petslist' ); ?></h2>
						<a href="<?php echo esc_url( $register_url ); ?>" class="button-style-1"><?php esc_html_e( 'Create An Account', 'petslist' ); ?><i aria-hidden="true" class="icon-pl-right-arrow"></i></a>
					</div>
					<img src="<?php echo esc_url( petslist_img_url( 'banner_blue' ) ); ?>" alt="" class="petslist-home-banner-card__img" loading="lazy">
				</div>
			</div>
		</div>
	</section>

	<!-- ============ LATEST DOG PROFILES ============ -->
	<section class="petslist-home-directory">
		<div class="container">
			<div class="petslist-home-directory__grid">
				<aside class="petslist-home-directory__side">
					<?php petslist_render_dog_breeds( 16 ); ?>
				</aside>
				<div class="petslist-home-directory__main">
					<div class="section-heading">
						<h2 class="heading-title"><?php esc_html_e( 'Latest Dog Profiles', 'petslist' ); ?></h2>
					</div>
					<div class="petslist-home-listings">
						<?php petslist_render_dog_cards( 9 ); ?>
					</div>
					<div class="petslist-home-directory__actions">
						<a href="<?php echo esc_url( $directory_url ); ?>" class="button-style-1"><?php esc_html_e( 'See All Dogs', 'petslist' ); ?><i aria-hidden="true" class="icon-pl-right-arrow"></i></a>
					</div>
				</div>
			</div>
		</div>
	</section>



</main>

<?php get_footer(); ?>
