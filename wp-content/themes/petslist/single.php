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
    <section id="primary" class="content-area site-single">
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
						get_template_part( 'template-parts/content-single' );
						if ( comments_open() || get_comments_number() ) {
							comments_template();
						}
						?>
					<?php endwhile; ?>
					<?php
					if ( Options::$options['post_details_related_section'] ) {
						get_template_part( 'template-parts/related', 'posts' );
					}
					?>
                </div>
				<?php
				if ( Options::$layout == 'right-sidebar' ) {
					get_sidebar();
				}
				?>
            </div>
        </div>
    </section>

<?php get_footer(); ?>