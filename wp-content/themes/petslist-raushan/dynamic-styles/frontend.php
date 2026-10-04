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
	--petslist-primary-color: <?php echo esc_html( $primary_color ? $primary_color : '#bd8c42' ); ?>;
	--petslist-secondary-color: #bd8c42;
	--petslist-body-color: <?php echo esc_html( $body_color ? $body_color : '#515167' ); ?>;
	--petslist-heading-color: <?php echo esc_html( $heading_color ? $heading_color : '#070C3E' ); ?>;
	--petslist-button-color1: #bd8c42;
	--petslist-button-color2: #bd8c42;
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

/* Force footer background to black */
footer.footer-style-3:after {
	background-color: #000000 !important;
}
footer.footer-style-3 .footer-bottom {
	background-color: #000000 !important;
	padding: 20px 0 !important;
}
footer.footer-style-3 .footer-bottom .copyright-area {
	display: flex !important;
	align-items: center !important;
	justify-content: space-between !important;
	width: 100% !important;
	gap: 16px !important;
}
footer.footer-style-3 .footer-bottom .copyright-left {
	text-align: left !important;
	flex: 1 1 auto !important;
}
footer.footer-style-3 .footer-bottom .copyright-right {
	text-align: right !important;
	flex: 0 1 auto !important;
}
footer.footer-style-3 .footer-bottom .footer-copyright,
footer.footer-style-3 .footer-bottom .footer-links {
	color: #ffffff !important;
	font-size: 15px !important;
	margin: 0 !important;
}
footer.footer-style-3 .footer-bottom .copyright-left .footer-copyright {
	text-align: left !important;
}
footer.footer-style-3 .footer-bottom .copyright-right .footer-links,
footer.footer-style-3 .footer-bottom .copyright-right .footer-copyright {
	text-align: right !important;
}
footer.footer-style-3 .footer-bottom .footer-links a,
footer.footer-style-3 .footer-bottom .footer-copyright a {
	color: #ffffff !important;
	text-decoration: none !important;
}
footer.footer-style-3 .footer-bottom .footer-links a:hover,
footer.footer-style-3 .footer-bottom .footer-copyright a:hover {
	color: var(--petslist-primary-color, #bd8c42) !important;
	text-decoration: none !important;
}
footer.footer-style-3 .footer-bottom .footer-sep {
	margin: 0 10px !important;
	opacity: 0.45 !important;
	display: inline-block !important;
}
@media (max-width: 767px) {
	footer.footer-style-3 .footer-bottom .copyright-area {
		flex-direction: column !important;
		justify-content: center !important;
		text-align: center !important;
		gap: 8px !important;
	}
	footer.footer-style-3 .footer-bottom .copyright-left,
	footer.footer-style-3 .footer-bottom .copyright-right,
	footer.footer-style-3 .footer-bottom .copyright-left .footer-copyright,
	footer.footer-style-3 .footer-bottom .copyright-right .footer-links {
		text-align: center !important;
		width: 100% !important;
	}
}

/* Category Sidebar Text Overrides */
.category-list .category-item .content .category-name {
	color: #000000 !important;
	font-size: 1.08rem !important;
	font-weight: 600 !important;
}
.category-list .category-item .content .category-name:hover {
	color: var(--petslist-primary-color) !important;
}
.category-list .category-item .content .item-number {
	color: #000000 !important;
	opacity: 0.8 !important;
	font-size: 0.9rem !important;
}

/* Solid Black Text Overrides */
.main-header .main-navigation-area .main-navigation ul li a,
.section-heading .heading-title,
.dd-dir-card__name,
.dd-dir-card__name a {
	color: #000000 !important;
}
.petslist-home-hero .heading-title,
.petslist-cta-band .heading-title {
	color: #ffffff !important;
}

/* Search Dropdown Alignment: Text Left, Dropdown Icon Far Right */
.petslist-listing-search-form .rtin-country-space .form-group,
.petslist-listing-search-form .rtin-state-space .form-group,
.petslist-home-search-standalone .rtin-country-space .form-group,
.petslist-home-search-standalone .rtin-state-space .form-group,
.petslist-home-hero__search .rtin-country-space .form-group,
.petslist-home-hero__search .rtin-state-space .form-group,
.header-search-area .rtin-country-space .form-group,
.header-search-area .rtin-state-space .form-group {
	justify-content: flex-start !important;
	padding: 0 14px !important;
	gap: 8px !important;
}

.petslist-listing-search-form .rtin-country-space .rtcl-search-input-button,
.petslist-listing-search-form .rtin-state-space .rtcl-search-input-button,
.petslist-home-search-standalone .rtin-country-space .rtcl-search-input-button,
.petslist-home-search-standalone .rtin-state-space .rtcl-search-input-button,
.petslist-home-hero__search .rtin-country-space .rtcl-search-input-button,
.petslist-home-hero__search .rtin-state-space .rtcl-search-input-button,
.header-search-area .rtin-country-space .rtcl-search-input-button,
.header-search-area .rtin-state-space .rtcl-search-input-button {
	flex: 1 1 0% !important;
	width: 100% !important;
	min-width: 0 !important;
	max-width: 100% !important;
	display: flex !important;
	align-items: center !important;
	justify-content: flex-start !important;
	position: relative !important;
}

.petslist-listing-search-form .select2-container,
.petslist-home-search-standalone .select2-container,
.petslist-home-hero__search .select2-container,
.header-search-area .select2-container {
	flex: 1 1 auto !important;
	width: 100% !important;
	min-width: 0 !important;
	max-width: 100% !important;
	display: block !important;
	position: relative !important;
}

.petslist-listing-search-form .select2-container .select2-selection--single,
.petslist-home-search-standalone .select2-container .select2-selection--single,
.petslist-home-hero__search .select2-container .select2-selection--single,
.header-search-area .select2-container .select2-selection--single {
	width: 100% !important;
	height: 48px !important;
	background: transparent !important;
	border: none !important;
	outline: none !important;
	box-shadow: none !important;
	position: relative !important;
	display: flex !important;
	align-items: center !important;
	cursor: pointer !important;
	padding: 0 !important;
}

.petslist-listing-search-form .select2-container .select2-selection--single .select2-selection__rendered,
.petslist-home-search-standalone .select2-container .select2-selection--single .select2-selection__rendered,
.petslist-home-hero__search .select2-container .select2-selection--single .select2-selection__rendered,
.header-search-area .select2-container .select2-selection--single .select2-selection__rendered {
	text-align: left !important;
	padding-left: 0 !important;
	padding-right: 28px !important;
	width: 100% !important;
	color: #4b5563 !important;
	font-size: 15px !important;
	font-weight: 500 !important;
	line-height: 48px !important;
	white-space: nowrap !important;
	overflow: hidden !important;
	text-overflow: ellipsis !important;
	display: block !important;
}

.petslist-home-search-standalone .rtin-keyword .select2-container .select2-selection--single .select2-selection__rendered,
.petslist-home-hero__search .rtin-keyword .select2-container .select2-selection--single .select2-selection__rendered {
	padding-left: 4px !important;
}

.petslist-listing-search-form .select2-container .select2-selection--single .select2-selection__arrow,
.petslist-home-search-standalone .select2-container .select2-selection--single .select2-selection__arrow,
.petslist-home-hero__search .select2-container .select2-selection--single .select2-selection__arrow,
.header-search-area .select2-container .select2-selection--single .select2-selection__arrow {
	position: absolute !important;
	right: 6px !important;
	left: auto !important;
	top: 50% !important;
	transform: translateY(-50%) !important;
	width: 18px !important;
	height: 18px !important;
	border: none !important;
	background: transparent !important;
	background-image: none !important;
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	pointer-events: none !important;
}

.petslist-listing-search-form .select2-container .select2-selection--single .select2-selection__arrow b,
.petslist-home-search-standalone .select2-container .select2-selection--single .select2-selection__arrow b,
.petslist-home-hero__search .select2-container .select2-selection--single .select2-selection__arrow b,
.header-search-area .select2-container .select2-selection--single .select2-selection__arrow b {
	border: none !important;
	border-width: 0 !important;
	width: auto !important;
	height: auto !important;
	position: relative !important;
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	top: auto !important;
	left: auto !important;
	margin: 0 !important;
}

.petslist-listing-search-form .select2-container .select2-selection--single .select2-selection__arrow b::before,
.petslist-home-search-standalone .select2-container .select2-selection--single .select2-selection__arrow b::before,
.petslist-home-hero__search .select2-container .select2-selection--single .select2-selection__arrow b::before,
.header-search-area .select2-container .select2-selection--single .select2-selection__arrow b::before {
	content: none !important;
	display: none !important;
}

.petslist-listing-search-form .select2-container .select2-selection--single .select2-selection__arrow b::after,
.petslist-home-search-standalone .select2-container .select2-selection--single .select2-selection__arrow b::after,
.petslist-home-hero__search .select2-container .select2-selection--single .select2-selection__arrow b::after,
.header-search-area .select2-container .select2-selection--single .select2-selection__arrow b::after {
	content: "\f078" !important;
	font-family: "Font Awesome 5 Free", "Font Awesome 6 Free", "FontAwesome" !important;
	font-weight: 900 !important;
	font-size: 11px !important;
	color: #8a8fa3 !important;
	display: inline-block !important;
	position: static !important;
	line-height: 1 !important;
	transition: transform 0.2s ease !important;
}

.petslist-listing-search-form .select2-container.select2-container--open .select2-selection--single .select2-selection__arrow b::after,
.petslist-home-search-standalone .select2-container.select2-container--open .select2-selection--single .select2-selection__arrow b::after,
.petslist-home-hero__search .select2-container.select2-container--open .select2-selection--single .select2-selection__arrow b::after,
.header-search-area .select2-container.select2-container--open .select2-selection--single .select2-selection__arrow b::after {
	transform: rotate(180deg) !important;
	color: var(--petslist-primary-color, #bd8c42) !important;
}

.petslist-listing-search-form select.form-control,
.petslist-home-search-standalone select.form-control,
.petslist-home-hero__search select.form-control,
.header-search-area select.form-control {
	width: 100% !important;
	text-align: left !important;
	text-align-last: left !important;
	-webkit-text-align-last: left !important;
	-moz-text-align-last: left !important;
	padding-left: 0 !important;
	padding-right: 28px !important;
	appearance: none !important;
	-webkit-appearance: none !important;
	-moz-appearance: none !important;
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%238a8fa3' d='M0 0l5 5 5-5z'/%3E%3C/svg%3E") !important;
	background-repeat: no-repeat !important;
	background-position: right 8px center !important;
}