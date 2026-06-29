<?php 
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

use RadiusTheme\Petslist\Helper;

$rt_main_logo = empty( Helper::petslist_main_logo() ) ? get_bloginfo( 'name' ) : Helper::petslist_main_logo();

?>

<div class="site-branding">
	<?php if ( !empty( Helper::petslist_main_logo() ) ): ?>
        <a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php echo Helper::petslist_main_logo(); ?>
        </a>
	<?php else: ?>
        <h1 class="site-title">
			<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php echo wp_kses_post( $rt_main_logo ); ?>
			</a>
        </h1>
	<?php endif; ?>
	<?php 
		if ( display_header_text() == true ) {
			$description = get_bloginfo( 'description', 'display' );
		if ( $description || is_customize_preview() ) : ?>
			<div class="site-description"><?php echo esc_html( $description ); ?></div>
		<?php endif; 
		}
	?>
</div>