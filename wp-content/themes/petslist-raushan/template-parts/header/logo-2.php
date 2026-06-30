<?php 
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

use RadiusTheme\Petslist\Helper;

$rt_logo_two = empty( Helper::rt_logo_two() ) ? get_bloginfo( 'name' ) : Helper::rt_logo_two();

?>
<div class="site-branding">
	<?php if ( !empty( Helper::rt_logo_two() ) ): ?>
        <a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php echo Helper::rt_logo_two(); ?>
        </a>
	<?php else: ?>
        <h1 class="site-title">
			<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php echo wp_kses_post( $rt_logo_two ); ?>
			</a>
        </h1>
	<?php endif; ?>
</div>