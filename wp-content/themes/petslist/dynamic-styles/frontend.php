<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

use RadiusTheme\Petslist\Helper;
use RadiusTheme\Petslist\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

Helper::requires( 'common.php', 'dynamic-styles' );

$header_transparent_color = Options::$options['header_transparent_color'];
$logo_max_width           = Options::$options['logo_width'];

$primary_color   = Helper::get_primary_color();
$secondary_color = Helper::get_secondary_color();
$body_color      = Helper::get_body_color();
$heading_color   = Helper::get_heading_color();

$primary_rgb   = Helper::hex2rgb( $primary_color );
$secondary_rgb = Helper::hex2rgb( $secondary_color );

$button_color_1   = Helper::get_button_color1();
$button_color_2   = Helper::get_button_color2();

?>

<?php
/*-------------------------------------
#. Defaults
---------------------------------------*/
?>
:root {
	--petslist-white-color: #ffffff;
	--petslist-primary-color: <?php echo esc_html( $primary_color ? $primary_color : '#02c5bd' ); ?>;
	--petslist-secondary-color: <?php echo esc_html( $secondary_color ? $secondary_color : '#FF3D41' ); ?>;
	--petslist-body-color: <?php echo esc_html( $body_color ? $body_color : '#515167' ); ?>;
	--petslist-heading-color: <?php echo esc_html( $heading_color ? $heading_color : '#070C3E' ); ?>;
	--petslist-button-color1: <?php echo esc_html( $button_color_1 ? $button_color_1 : '#FF282C' ); ?>;
	--petslist-button-color2: <?php echo esc_html( $button_color_2 ? $button_color_2 : '#FF4E51' ); ?>;
}
<?php
/*-------------------------------------
#. Header
---------------------------------------*/
?>
.trheader .main-header {
	background-color: <?php echo esc_html( $header_transparent_color ); ?>;
}
.main-header .site-branding {
	max-width: <?php echo esc_html( $logo_max_width ); ?>;
}

<?php 
/* = Footer 1 bg images
=======================================================*/
if ( !empty( Options::$options['f1_bg_img']) ) {
	$f1_bg_img = wp_get_attachment_image_src( Options::$options['f1_bg_img'], 'full', true );
?>

footer.footer-style-1 { 
	background-image: url(<?php echo esc_url($f1_bg_img[0]); ?>) !important
}

<?php } if ( !empty( Options::$options['f1_bg_color']) ) { ?>
footer.footer-style-1:after {
	background-color: <?php echo esc_html( Options::$options['f1_bg_color'] ); ?>;
}
<?php } if ( !empty( Options::$options['f1_bg_opacity']) ) { 
	$opacity = Options::$options['f1_bg_opacity']/100;
?>
footer.footer-style-1:after {
	opacity: <?php echo esc_html( $opacity ); ?>;
}
<?php } if ( !empty( Options::$options['f1_cr_bg_color']) ) { ?>
footer.footer-style-1 .footer-bottom {
	background-color: <?php echo esc_html( Options::$options['f1_cr_bg_color'] ); ?>;
}
<?php } ?>

<?php 
/* = Footer 2 bg images
=======================================================*/
if ( !empty( Options::$options['f2_bg_img']) ) {
	$f2_bg_img = wp_get_attachment_image_src( Options::$options['f2_bg_img'], 'full', true );
?>

footer.footer-style-2 { 
	background-image: url(<?php echo esc_url($f2_bg_img[0]); ?>) !important
}

<?php } if ( !empty( Options::$options['f2_bg_color']) ) { ?>
footer.footer-style-2:after {
	background-color: <?php echo esc_html( Options::$options['f2_bg_color'] ); ?>;
}
<?php } if ( !empty( Options::$options['f2_bg_opacity']) ) { 
	$opacity = Options::$options['f2_bg_opacity']/100;
?>
footer.footer-style-2:after {
	opacity: <?php echo esc_html( $opacity ); ?>;
}
<?php } if ( !empty( Options::$options['f2_cr_bg_color']) ) { ?>
footer.footer-style-2 .footer-bottom {
	background-color: <?php echo esc_html( Options::$options['f2_cr_bg_color'] ); ?>;
}
<?php } ?>

<?php 
/* = Footer 3 bg images
=======================================================*/
if ( !empty( Options::$options['f3_bg_img']) ) {
	$f3_bg_img = wp_get_attachment_image_src( Options::$options['f3_bg_img'], 'full', true );
?>

footer.footer-style-3 { 
	background-image: url(<?php echo esc_url($f3_bg_img[0]); ?>) !important
}

<?php } if ( !empty( Options::$options['f3_bg_color']) ) { ?>
footer.footer-style-3:after {
	background-color: <?php echo esc_html( Options::$options['f3_bg_color'] ); ?>;
}
<?php } if ( !empty( Options::$options['f3_bg_opacity']) ) { 
	$opacity = Options::$options['f3_bg_opacity']/100;
?>
footer.footer-style-3:after {
	opacity: <?php echo esc_html( $opacity ); ?>;
}
<?php } if ( !empty( Options::$options['f3_cr_bg_color']) ) { ?>
footer.footer-style-3 .footer-bottom {
	background-color: <?php echo esc_html( Options::$options['f3_cr_bg_color'] ); ?>;
}
<?php } ?>


<?php 
$pt = Options::$padding_top;
$pb = Options::$padding_bottom;
/* = Banner content padding
=======================================================*/
if ( !empty( $pt )) { ?>

.breadcrumbs-area {
	padding-top: <?php echo esc_html( $pt ); ?>px;
}

<?php } if ( !empty( $pb )) { ?>

.breadcrumbs-area {
	padding-bottom: <?php echo esc_html( $pb ); ?>px;
}

<?php } ?>