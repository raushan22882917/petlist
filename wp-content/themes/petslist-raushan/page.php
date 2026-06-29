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
    <main id="primary" class="content-area">
        <div class="container">
            <div class="row">
				<?php
				if ( Options::$layout == 'left-sidebar' ) {
					get_sidebar();
				}
				?>
                <div class="<?php Helper::the_layout_class(); ?>">
					<?php while ( have_posts() ) : the_post(); ?>
						<?php
						get_template_part( 'template-parts/content', 'page' );
						if ( comments_open() || get_comments_number() ) {
							comments_template();
						}
						?>
					<?php endwhile; ?>
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