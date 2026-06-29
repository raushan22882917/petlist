<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */
?>
<section class="no-results not-found">
	<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>
        <p>
			<?php echo esc_html__( 'Ready to publish your first post?', 'petslist' ) ?>
            <a href="<?php echo esc_url( admin_url( 'post-new.php' ) ) ?>"><?php echo esc_html__( 'Get started here', 'petslist' ) ?></a>
        </p>
	<?php elseif ( is_search() ) : ?>
        <p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'petslist' ); ?></p>
		<?php get_search_form(); ?>
	<?php else : ?>
        <p><?php esc_html_e( "It seems we can't find what you're looking for. Perhaps searching can help.", 'petslist' ); ?></p>
		<?php get_search_form(); ?>
	<?php endif; ?>
</section>