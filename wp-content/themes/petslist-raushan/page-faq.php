<?php
/**
 * Template Name: FAQ Page
 * Custom FAQ page (no Elementor).
 */
if ( have_posts() ) {
	the_post();
}

get_header();

$faqs = array(
	array(
		__( 'What do I need to bring?', 'petslist' ),
		__( 'When an unknown printer took a galley and scrambled five make a type specimen book has. When an unknown printer took a galley and scrambled five make a type specimen book has.', 'petslist' ),
	),
	array(
		__( 'Will my pet have food and water?', 'petslist' ),
		__( 'When an unknown printer took a galley and scrambled five make a type specimen book has. When an unknown printer took a galley and scrambled five make a type specimen book has.', 'petslist' ),
	),
	array(
		__( 'Are your associates knowledgeable about pets?', 'petslist' ),
		__( 'When an unknown printer took a galley and scrambled five make a type specimen book has. When an unknown printer took a galley and scrambled five make a type specimen book has.', 'petslist' ),
	),
	array(
		__( 'How do you handle older pets?', 'petslist' ),
		__( 'When an unknown printer took a galley and scrambled five make a type specimen book has. When an unknown printer took a galley and scrambled five make a type specimen book has.', 'petslist' ),
	),
);
?>

<main id="primary" class="content-area petslist-custom-page petslist-faq-page">
	<section class="petslist-faq-section">
		<div class="container">
			<div class="petslist-faq-layout">
				<div class="petslist-faq-intro">
					<div class="section-heading text-center">
						<h2 class="heading-title"><?php esc_html_e( 'Find Answer Of Your Questions', 'petslist' ); ?></h2>
						<p><?php esc_html_e( 'Maecenas Quis Viverra Metus, Et Efficitur Ligula. Nam Congueaugue Congue Sed Luctus Lectus ConIn Onvallis Condimentum.', 'petslist' ); ?></p>
					</div>
				</div>
				<div class="faq-box">
					<div class="panel-group" id="petslist-faq-accordion">
						<?php foreach ( $faqs as $i => $faq ) :
							$uid       = 'faq' . $i;
							$expanded  = 0 === $i ? 'show' : '';
							$collapsed = 0 === $i ? '' : 'collapsed';
							?>
							<div class="panel panel-default">
								<div class="panel-heading" id="heading-<?php echo esc_attr( $uid ); ?>">
									<button class="accordion-button right <?php echo esc_attr( $collapsed ); ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo esc_attr( $uid ); ?>" aria-expanded="<?php echo 0 === $i ? 'true' : 'false'; ?>">
										<?php echo esc_html( $faq[0] ); ?>
										<span class="rtin-accordion-icon">
											<span class="rtin-icon rt-icon-closed"><?php echo petslist_faq_accordion_icon( false ); ?></span>
											<span class="rtin-icon rt-icon-opened"><?php echo petslist_faq_accordion_icon( true ); ?></span>
										</span>
									</button>
								</div>
								<div id="collapse-<?php echo esc_attr( $uid ); ?>" class="accordion-collapse collapse <?php echo esc_attr( $expanded ); ?>" data-bs-parent="#petslist-faq-accordion">
									<div class="panel-body"><p><?php echo esc_html( $faq[1] ); ?></p></div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/custom/section', 'cta' ); ?>
</main>

<?php get_footer(); ?>
