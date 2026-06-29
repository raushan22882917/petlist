<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

use RadiusTheme\Petslist\Options;

$comments_number = get_comments_number();
$comments_text   = sprintf( '(%s)', number_format_i18n( $comments_number ) );
$has_entry_meta  = Options::$options['post_author_name'] || Options::$options['post_comment_num'] || Options::$options['post_date'];
$footer_class    = Options::$options['post_tag'] && has_tag() && Options::$options['post_social_icon'] && class_exists( 'Petslist_Core' ) ? 'col-md-6 col-sm-12 col-12'
	: 'col-md-12 col-sm-12 col-12';
$has_post_footer = ( Options::$options['post_tag'] && has_tag() ) || ( Options::$options['post_social_icon'] && class_exists( 'Petslist_Core' ) );
$has_post_social = ( class_exists( 'Petslist_Core' ) && Options::$options['post_social_icon'] );

?>
    <div class="site-content-block">
        <div class="main-content">
            <div id="post-<?php the_ID(); ?>" <?php post_class( 'post-each post-each-single' ); ?>>

				<?php if ( has_post_thumbnail() ): ?>
                    <div class="post-thumbnail">
						<?php the_post_thumbnail( 'petslist-size1' ); ?>
                    </div>
				<?php endif; ?>

                <div class="post-content-area">

                    <div class='post-title-wrap'><h2 class="post-title"><?php the_title(); ?></h2></div>

					<?php if ( $has_entry_meta ): ?>
                        <ul class="post-meta">
							<?php if ( Options::$options['post_date'] ): ?>
                                <li>
                                    <i class="icon-pl-calendar"></i>
                                    <span class="updated published">
                                        <?php the_time( get_option( 'date_format' ) ); ?>
                                    </span>
                                </li>
							<?php endif; ?>

							<?php if ( Options::$options['post_author_name'] ): ?>
                                <li>
                                    <i class="icon-pl-account"></i>
                                    <span class="vcard author">
                                        <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>" class="fn"><?php the_author(); ?></a>
                                    </span>
                                </li>
							<?php endif; ?>

							<?php if ( Options::$options['post_comment_num'] ): ?>
                                <li>
                                    <i class="icon-pl-chat"></i>
                                    <?php echo esc_html( $comments_text ); ?>
                                </li>
							<?php endif; ?>

							<?php if ( Options::$options['post_cats'] && has_category() ): ?>
                                <li>
                                    <i class="icon-pl-tag"></i>
                                    <?php the_category( ', ' ); ?>
                                </li>
							<?php endif; ?>
                        </ul>
					<?php endif; ?>

                    <div class="post-content entry-content clearfix">
                        <?php the_content(); ?>
                    </div>

					<?php wp_link_pages(); ?>

					<?php if ( $has_post_footer ): ?>
                        <div class="blog-tag-share d-flex flex-column flex-md-row gap-3 gap-lg-0 justify-content-between align-items-center wow animate__fadeInUp" data-wow-duration="1200ms" data-wow-delay="800ms">
                            <?php if ( has_tag() && Options::$options['post_tag'] ): ?>
                                <div class="single-tag-area d-flex align-items-center">
                                    <div class="title"><?php esc_html_e( 'Tags:', 'petslist' ); ?></div>
                                    <div class="tag-list d-flex flex-wrap">
                                        <?php echo get_the_term_list( $post->ID, 'post_tag' ); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ( class_exists( 'Petslist_Core' ) && Options::$options['post_social_icon'] ): ?>
                                <div class="single-share-area d-flex align-items-center">
                                    <div class="title"><?php esc_html_e( 'Share:', 'petslist' ); ?></div>
                                    <?php Petslist_Core::social_share(); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (get_the_author_meta('description')) : ?>      
                        <div class="blog-author">
                            <div class="media">
                                <div class="info-item avatar">
                                    <?php echo get_avatar(get_the_author_meta('user_email'), '180'); ?>
                                </div>
                                <div class="info-item avatar-text">
                                    <span><?php esc_html_e( 'Autohr', 'petslist' ); ?></span>
                                    <h4 class="author-title"><?php esc_html(the_author_meta('display_name')); ?></h4>
                                    <p><?php esc_html(the_author_meta('description')); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>