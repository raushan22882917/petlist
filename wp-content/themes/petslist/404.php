<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

use RadiusTheme\Petslist\Options;
?>
<?php get_header(); ?>
    <div id="primary" class="content-area">
        <div class="container">
            <div class="error-page">
                <?php echo wp_get_attachment_image(  Options::$options['error_image'], 'full' ); ?>
                <h2><?php echo esc_html( Options::$options['error_text'] ); ?></h2>
                <p class="error-subtitle"><?php echo esc_html( Options::$options['error_subtitle'] ); ?></p>
                <a class="button-style-2" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( Options::$options['error_buttontext'] ); ?></a>
            </div>
        </div>
    </div>
<?php get_footer(); ?>