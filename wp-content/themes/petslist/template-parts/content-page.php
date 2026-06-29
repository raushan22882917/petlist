<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */
?>
<div class="site-content-block">
    <div class="main-content">
        <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<?php if ( has_post_thumbnail() ): ?>
                <div class="main-thumbnail"><?php the_post_thumbnail(); ?></div>
			<?php endif; ?>

			<?php the_content(); ?>

			<?php wp_link_pages(); ?>

        </div>
    </div>
</div>