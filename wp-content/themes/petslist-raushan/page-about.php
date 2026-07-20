<?php
/**
 * Template Name: About Page
 * Custom About page (no Elementor).
 */
if ( have_posts() ) {
	the_post();
}

get_header();
?>

<main id="primary" class="content-area petslist-custom-page petslist-about-page">

	<section class="petslist-about-hero">
		<div class="container">
			<div class="petslist-about-hero__grid">
				<div class="petslist-about-hero__gallery">
					<img src="<?php echo esc_url( petslist_img_url( 'about_1' ) ); ?>" alt="" class="about-img about-img--main" loading="eager">
					<img src="<?php echo esc_url( petslist_img_url( 'about_2' ) ); ?>" alt="" class="about-img about-img--top" loading="lazy">
					<img src="<?php echo esc_url( petslist_img_url( 'about_3' ) ); ?>" alt="" class="about-img about-img--bottom" loading="lazy">
				</div>
				<div class="petslist-about-hero__content">
					<div class="section-heading">
						<h2 class="heading-title"><?php esc_html_e( 'About Studs 4 You', 'petslist' ); ?></h2>
						<p><?php esc_html_e( 'At Studs 4 You, our goal is to connect people who are serious about dog breeding. This website was created after we experienced our own challenges finding stud dogs with the specific qualities we were looking for. We recognized a need and wanted to create a platform that would not only help us but also support the breeding community as a whole. Studs 4 You was built to fill that gap by making it easier for breeders to connect, find quality stud dogs, and help strengthen responsible breeding within the community.', 'petslist' ); ?></p>
					</div>
					<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="button-style-1">
						<?php esc_html_e( 'Contact With Us', 'petslist' ); ?><i aria-hidden="true" class="icon-pl-right-arrow"></i>
					</a>
				</div>
			</div>
		</div>
	</section>

</main>

<?php get_footer(); ?>
