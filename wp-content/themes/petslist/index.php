<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

use RadiusTheme\Petslist\Helper;
use RadiusTheme\Petslist\Options;

get_header();

?>
<main id="primary" class="site-main content-area">
	<div class="container">
		<div class="row">
			<?php
			if ( Options::$layout == 'left-sidebar' ) {
				get_sidebar();
			}
			?>
			<div class="<?php Helper::the_layout_class(); ?>">
				<div class="main-content">
					<?php if ( have_posts() ): ?>
						<?php
							while ( have_posts() ): the_post();
								get_template_part( 'template-parts/content' );
							endwhile;
						?>
					<?php else: ?>
						<?php get_template_part( 'template-parts/content', 'none' ); ?>
					<?php endif; ?>
				</div>
				<?php get_template_part( 'template-parts/pagination' ); ?>
			</div>
			<?php
			if ( Options::$layout == 'right-sidebar' ) {
				get_sidebar();
			}
			?>
		</div>
	</div>
    </main>

<?php get_footer(); ?>