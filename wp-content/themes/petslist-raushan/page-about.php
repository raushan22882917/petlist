<?php
/**
 * Template Name: About Page
 * Custom About page (no Elementor).
 */
if ( have_posts() ) {
	the_post();
}

get_header();

$features = array(
	__( 'Trusted Pet Listing Website', 'petslist' ),
	__( 'Go Out & Explore', 'petslist' ),
	__( 'More Favourite Pets', 'petslist' ),
);
$team = array(
	array( 'name' => 'Marvin McKinney', 'role' => 'Accountance', 'img' => '2023/09/team-img-1.jpg' ),
	array( 'name' => 'Eleanor Pena', 'role' => 'Marketing Analytics', 'img' => '2023/09/team-img-2.jpg' ),
	array( 'name' => 'Annette Black', 'role' => 'User Research', 'img' => '2023/09/team-img-4.jpg' ),
	array( 'name' => 'Floyd Miles', 'role' => 'HR Admin', 'img' => '2023/09/team-img-3.jpg' ),
);
$plans = array(
	array( 'title' => 'Basic', 'price' => '$12', 'period' => '/Month' ),
	array( 'title' => 'Standard', 'price' => '$39', 'period' => '/Month' ),
	array( 'title' => 'Platinum', 'price' => '$99', 'period' => '/Month' ),
);
$plan_features = petslist_pricing_features();
$shape = petslist_upload_url( '2023/08/pricing-card-shape.svg' );
?>

<main id="primary" class="content-area petslist-custom-page petslist-about-page">

	<section class="petslist-about-hero">
		<div class="container">
			<div class="petslist-about-hero__grid">
				<div class="petslist-about-hero__gallery">
					<img src="<?php echo esc_url( petslist_upload_url( '2023/09/about-1.jpg' ) ); ?>" alt="" class="about-img about-img--main" loading="eager">
					<img src="<?php echo esc_url( petslist_upload_url( '2023/09/about-2.jpg' ) ); ?>" alt="" class="about-img about-img--top" loading="lazy">
					<img src="<?php echo esc_url( petslist_upload_url( '2023/09/about-3.jpg' ) ); ?>" alt="" class="about-img about-img--bottom" loading="lazy">
				</div>
				<div class="petslist-about-hero__content">
					<div class="section-heading">
						<h2 class="heading-title"><?php esc_html_e( 'We Are Top #1 Pet Listing Ads WebSite Where 5M User Trust Us', 'petslist' ); ?></h2>
						<p><?php esc_html_e( 'Maecenas quis viverra metus, et efficitur ligula. Nam congue augue congue sed luctus lectus conIn onvallis condimen tum sem Duis elementum.', 'petslist' ); ?></p>
					</div>
					<ul class="petslist-check-list">
						<?php foreach ( $features as $item ) : ?>
							<li><?php echo petslist_check_icon(); ?><span><?php echo esc_html( $item ); ?></span></li>
						<?php endforeach; ?>
					</ul>
					<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="button-style-1">
						<?php esc_html_e( 'Contact With Us', 'petslist' ); ?><i aria-hidden="true" class="icon-pl-right-arrow"></i>
					</a>
				</div>
			</div>
		</div>
	</section>

	<section class="petslist-stats-band">
		<div class="container">
			<div class="petslist-stats-grid">
				<?php
				$stats = array(
					array( __( 'Listings added', 'petslist' ), '47', 'K' ),
					array( __( 'Registered Users', 'petslist' ), '47', 'K' ),
					array( __( 'Countries Available', 'petslist' ), '243', '' ),
					array( __( 'Daily Visitors', 'petslist' ), '12', 'K' ),
				);
				foreach ( $stats as $stat ) :
					?>
					<div class="petslist-stat">
						<div class="petslist-stat__label"><?php echo esc_html( $stat[0] ); ?></div>
						<div class="petslist-stat__value"><?php echo esc_html( $stat[1] . $stat[2] ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="petslist-team-section">
		<div class="container">
			<div class="section-heading text-center">
				<h5 class="heading-title"><?php esc_html_e( 'Expert Care Takers', 'petslist' ); ?></h5>
				<p class="sub-title"><?php esc_html_e( 'Aliquam Lacinia Diam Quis Lacus Euismod', 'petslist' ); ?></p>
			</div>
			<div class="petslist-team-grid">
				<?php foreach ( $team as $member ) : ?>
					<div class="team-card">
						<div class="team-img-wrapper">
							<img src="<?php echo esc_url( petslist_upload_url( $member['img'] ) ); ?>" alt="<?php echo esc_attr( $member['name'] ); ?>" loading="lazy">
						</div>
						<div class="team-content">
							<h3 class="title"><span class="name"><?php echo esc_html( $member['name'] ); ?></span></h3>
							<p class="designation para-text"><?php echo esc_html( $member['role'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="petslist-pricing-section">
		<div class="container">
			<div class="section-heading text-center">
				<span class="heading-subtitle"><?php esc_html_e( 'Choose Your Plan', 'petslist' ); ?></span>
				<h2 class="heading-title"><?php esc_html_e( 'Our Affordable Pricing Plans', 'petslist' ); ?></h2>
			</div>
			<div class="petslist-pricing-grid">
				<?php foreach ( $plans as $plan ) : ?>
					<div class="rt-pricing-item common-style">
						<div class="pricing-header">
							<h3 class="pricing-title"><?php echo esc_html( $plan['title'] ); ?></h3>
							<h2 class="pricing-price"><?php echo esc_html( $plan['price'] ); ?><span class="pricing-plan">&nbsp;<?php echo esc_html( $plan['period'] ); ?></span></h2>
							<p class="para-text"><?php esc_html_e( 'Our Administration And Support Staff All Have Exceptional', 'petslist' ); ?></p>
						</div>
						<div class="rt-pricing-features">
							<ul class="rt-pricing-features-list feature-list feature-list--style-2">
								<?php foreach ( $plan_features as $feat ) : ?>
									<li><?php echo petslist_check_icon(); ?><span><?php echo esc_html( $feat ); ?></span></li>
								<?php endforeach; ?>
							</ul>
						</div>
						<div class="rt-pricing-item-btn">
							<a href="<?php echo esc_url( function_exists( 'dd_pricing_url' ) ? dd_pricing_url() : home_url( '/dog-directory-plans/' ) ); ?>" class="pricing-btn text-center"><?php esc_html_e( 'Purchase Now', 'petslist' ); ?></a>
						</div>
						<div class="pricing-shape-img"><img src="<?php echo esc_url( $shape ); ?>" alt="" loading="lazy"></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/custom/section', 'cta' ); ?>

</main>

<?php get_footer(); ?>
