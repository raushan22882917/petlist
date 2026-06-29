<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

use RadiusTheme\Petslist\Options;

$comments_number = get_comments_number();
$comments_text   = sprintf( '(%s)', number_format_i18n( $comments_number ) );
$has_thumbnail   = has_post_thumbnail() ? 'has-thumbnail' : 'has-no-thumbnail';
$post_class      = $has_thumbnail . ' post-each';
$has_entry_meta  = ( Options::$options['blog_cat_visibility'] && has_category() ) || Options::$options['blog_author_name'] || Options::$options['blog_comment_num'] || Options::$options['blog_date'];
$length          = Options::$options['excerpt_length'];
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $post_class ); ?>>
	<?php if ( has_post_thumbnail() ): ?>
        <div class="post-thumbnail">
            <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'petslist-size1' ); ?></a>
        </div>
	<?php endif; ?>
    <div class="post-content-area">
        <?php if ( ! empty( get_the_title() ) ): ?>
            <h3 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h3>
		<?php endif; ?>
	    <?php if ( $has_entry_meta ): ?>
            <ul class="post-meta">
			    <?php if ( Options::$options['blog_author_name'] ): ?>
                    <li>
                        <i class="icon-pl-account"></i>
                        <span class="vcard author">
                            <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"class="fn"><?php the_author(); ?></a>
                        </span>
                    </li>
			    <?php endif; ?>

			    <?php if ( Options::$options['blog_date'] ): ?>
                    <li>
                        <i class="icon-pl-calendar"></i>
                        <span class="updated published">
                            <?php the_time( get_option( 'date_format' ) ); ?>
                        </span>
                    </li>
			    <?php endif; ?>

			    <?php if ( Options::$options['blog_comment_num'] ): ?>
                    <li>
                        <i class="icon-pl-chat"></i>
                        <?php echo esc_html( $comments_text ); ?>
                    </li>
			    <?php endif; ?>

			    <?php if ( Options::$options['blog_cat_visibility'] && has_category() ): ?>
                    <li>
                        <i class="icon-pl-tag"></i>
                        <?php the_category( ', ' ); ?>
                    </li>
			    <?php endif; ?>
            </ul>
	    <?php endif; ?>
        <p class="entry-summary"><?php echo wp_trim_words( get_the_excerpt(), $length ); ?></p>
	    <?php if ( Options::$options['blog_button'] ): ?>
            <a class="button-style-2" href="<?php the_permalink(); ?>">
			    <?php esc_html_e( 'Read More', 'petslist' ); ?>
                <i class="fa-solid fa-arrow-right rtin-button-icon"></i>
            </a>
	    <?php endif; ?>
    </div>
</article>