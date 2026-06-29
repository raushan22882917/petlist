<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

use RadiusTheme\Petslist\Options;
use RadiusTheme\Petslist\Helper;

$sticky_header = '';
$header_container = 'container';
if ( 'fullwidth' == Options::$header_width ) {
	$header_container = 'container-fluid';
}
if ( Options::$options['sticky_header'] == 1 ) {
    $sticky_header = 'headroom-sticky-header';
}
?>
<div class="main-header header-style-3 rt-white-color-bg <?php echo esc_attr( $sticky_header ); ?> header--fixed headroom">
    <div class="<?php echo esc_attr( $header_container ); ?>">
        <div class="main-header-inner">
            <?php get_template_part( 'template-parts/header/logo', '2' ) ?>
            <?php get_template_part( 'template-parts/header/site', 'nav' ) ?>
            <div class="header-icon-area">
                <?php get_template_part( 'template-parts/header/button', 'chat' ) ?>
                <?php get_template_part( 'template-parts/header/button', 'account' ) ?>
                <?php get_template_part( 'template-parts/header/button', 'link' ) ?>
            </div>
        </div>
    </div>
</div>
<div class="header-height-fixed header-style-3"></div>