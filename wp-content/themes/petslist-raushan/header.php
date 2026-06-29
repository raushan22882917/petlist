<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

 use RadiusTheme\Petslist\Options;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <?php 
        if (Options::$options['preloader']) {
            do_action('site_prealoader');
        }
    ?>
    
    <div id="page" class="site">
        <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'petslist' ); ?></a>
		<?php get_template_part( 'template-parts/content', 'menu' ); ?>
        <div id="content" class="site-content">
            <?php
            if( class_exists('Rtcl') ) {
                get_template_part('template-parts/content', 'banner');
            }