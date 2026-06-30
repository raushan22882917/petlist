<?php
/**
 * Shared CTA: Something To Advertise?
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$img = petslist_img_url( 'cta_img' );
?>
<section class="petslist-cta-band">
	<div class="container">
		<div class="petslist-cta-band__grid">
			<div class="petslist-cta-band__img">
				<img src="<?php echo esc_url( $img ); ?>" alt="" width="139" height="184" loading="lazy">
			</div>
			<div class="petslist-cta-band__text">
				<span class="heading-subtitle"><?php esc_html_e( 'Do You Have', 'petslist' ); ?></span>
				<h2 class="heading-title"><?php esc_html_e( 'Something To Advertise?', 'petslist' ); ?></h2>
			</div>
			<div class="petslist-cta-band__btn">
				<a href="<?php echo esc_url( function_exists( 'dd_register_url' ) ? dd_register_url() : wp_registration_url() ); ?>" class="button-style-1">
					<i aria-hidden="true" class="icon-pl-plus"></i><?php esc_html_e( 'Sell Your Pet', 'petslist' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>
