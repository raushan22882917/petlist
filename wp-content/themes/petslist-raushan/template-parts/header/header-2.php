<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

use RadiusTheme\Petslist\Options;
$sticky_header = '';
$header_container = 'container';
if ( 'fullwidth' == Options::$header_width ) {
	$header_container = 'container-fluid';
}
if ( Options::$options['sticky_header'] == 1 ) {
    $sticky_header = 'headroom-sticky-header';
}

?>
<div class="main-header header-style-2 rt-primary-color-bg <?php echo esc_attr( $sticky_header ); ?> header--fixed headroom">
    <div class="header-top rt-dark-color-bg">
        <div class="<?php echo esc_attr( $header_container ); ?>">
            <div class="main-header-inner">
                <?php get_template_part( 'template-parts/header/site', 'nav' ) ?>
                <div class="header-icon-area">
                    <?php get_template_part( 'template-parts/header/button', 'chat' ) ?>
                    <?php get_template_part( 'template-parts/header/button', 'account' ) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="header-bottom">
        <div class="<?php echo esc_attr( $header_container ); ?>">
            <div class="main-header-inner">
                <?php get_template_part( 'template-parts/header/logo', '1' ) ?>
                <?php get_template_part( 'template-parts/header/header', 'search' ) ?>
                <?php get_template_part( 'template-parts/header/button', 'link' ) ?>
            </div>
        </div>
    </div>
</div>
<div class="header-height-fixed header-style-2"></div>