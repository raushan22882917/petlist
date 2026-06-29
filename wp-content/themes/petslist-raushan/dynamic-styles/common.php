<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

use RadiusTheme\Petslist\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$typo_body = json_decode( Options::$options['typo_body'], true );
if ( $typo_body['font'] == 'Inherit' ) {
	$typo_body = [
		'font'          => 'Plus Jakarta Sans',
		'regularweight' => '500',
	];
}

$typo_menu = json_decode( Options::$options['typo_menu'], true );
if ( $typo_menu['font'] == 'Inherit' ) {
	$typo_menu = [
		'font'          => 'Baloo Bhaijaan 2',
		'regularweight' => '500',
	];
}

$typo_heading = json_decode( Options::$options['typo_heading'], true );
if ( $typo_heading['font'] == 'Inherit' ) {
	$typo_heading = [
		'font'          => 'Baloo Bhaijaan 2',
		'regularweight' => '700',
	];
}

$typo_h1 = json_decode( Options::$options['typo_h1'], true );
$typo_h2 = json_decode( Options::$options['typo_h2'], true );
$typo_h3 = json_decode( Options::$options['typo_h3'], true );
$typo_h4 = json_decode( Options::$options['typo_h4'], true );
$typo_h5 = json_decode( Options::$options['typo_h5'], true );
$typo_h6 = json_decode( Options::$options['typo_h6'], true );

?>
:root{
    --petslist-body-font: '<?php echo esc_html( $typo_body['font'] ); ?>', sans-serif;;
    --petslist-heading-font: '<?php echo esc_html( $typo_heading['font'] ); ?>', sans-serif;
    --petslist-menu-font: '<?php echo esc_html( $typo_menu['font'] ); ?>', sans-serif;
}

body {
    font-family: '<?php echo esc_html( $typo_body['font'] ); ?>', sans-serif;
    font-size: <?php echo esc_html( Options::$options['typo_body_size'] ) ?>;
    line-height: <?php echo esc_html( Options::$options['typo_body_height'] ); ?>;
    font-weight : <?php echo esc_html( $typo_body['regularweight'] ) ?>;
    font-style: normal;
}

.main-header .main-navigation-area .main-navigation {
    font-family: '<?php echo esc_html( $typo_menu['font'] ); ?>', sans-serif;
}

.main-header .main-navigation-area .main-navigation nav > ul > li > a {
    line-height: <?php echo esc_html( Options::$options['typo_menu_height'] ); ?>;
    font-weight : <?php echo esc_html( $typo_menu['regularweight'] ) ?>;
    font-size: <?php echo esc_html( Options::$options['typo_menu_size'] ) ?>;
}

.main-header .main-navigation-area .main-navigation nav > ul > li ul.sub-menu li a {
    font-size: <?php echo esc_html( Options::$options['typo_submenu_size'] ) ?>;
    line-height: <?php echo esc_html( Options::$options['typo_submenu_height'] ); ?>;
}

.rtcl h1, .rtcl h2, .rtcl h3, .rtcl h4, .rtcl h5, .rtcl h6,
h1,h2,h3,h4,h5,h6 {
    font-family: '<?php echo esc_html( $typo_heading['font'] ); ?>', sans-serif;
    font-weight : <?php echo esc_html( $typo_heading['regularweight'] ); ?>;
    font-style: normal;
}

<?php if ( ! empty( $typo_h1['font'] ) && $typo_h1['font'] !== 'Inherit' ) { ?>
    h1 {
        font-family: '<?php echo esc_html( $typo_h1['font'] ); ?>', sans-serif;
        font-weight : <?php echo esc_html( $typo_h1['regularweight'] ); ?>;
    }
<?php } ?>

<?php if ( ! empty( $typo_h2['font'] ) && $typo_h2['font'] !== 'Inherit' ) { ?>
    h2 {
        font-family: '<?php echo esc_html( $typo_h2['font'] ); ?>', sans-serif;
        font-weight : <?php echo esc_html( $typo_h2['regularweight'] ); ?>;
    }
<?php } ?>

<?php if ( ! empty( $typo_h3['font'] ) && $typo_h3['font'] !== 'Inherit' ) { ?>
    h3 {
        font-family: '<?php echo esc_html( $typo_h3['font'] ); ?>', sans-serif;
        font-weight : <?php echo esc_html( $typo_h3['regularweight'] ); ?>;
    }
<?php } ?>

<?php if ( ! empty( $typo_h4['font'] ) && $typo_h4['font'] !== 'Inherit' ) { ?>
    h4 {
        font-family: '<?php echo esc_html( $typo_h4['font'] ); ?>', sans-serif;
        font-weight : <?php echo esc_html( $typo_h4['regularweight'] ); ?>;
    }
<?php } ?>

<?php if ( ! empty( $typo_h5['font'] ) && $typo_h5['font'] !== 'Inherit' ) { ?>
    h5 {
        font-family: '<?php echo esc_html( $typo_h5['font'] ); ?>', sans-serif;
        font-weight : <?php echo esc_html( $typo_h5['regularweight'] ); ?>;
    }
<?php } ?>

<?php if ( ! empty( $typo_h6['font'] ) && $typo_h6['font'] !== 'Inherit' ) { ?>
    h6 {
        font-family: '<?php echo esc_html( $typo_h6['font'] ); ?>', sans-serif;
        font-weight : <?php echo esc_html( $typo_h6['regularweight'] ); ?>;
    }
<?php } ?>

h1 {
    font-size: <?php echo esc_html( Options::$options['typo_h1_size'] ); ?>;
    line-height: <?php echo esc_html( Options::$options['typo_h1_height'] ); ?>;
}

h2 {
    font-size: <?php echo esc_html( Options::$options['typo_h2_size'] ); ?>;
    line-height: <?php echo esc_html( Options::$options['typo_h2_height'] ); ?>;
}

h3 {
    font-size: <?php echo esc_html( Options::$options['typo_h3_size'] ); ?>;
    line-height: <?php echo esc_html( Options::$options['typo_h3_height'] ); ?>;
}

h4 {
    font-size: <?php echo esc_html( Options::$options['typo_h4_size'] ); ?>;
    line-height: <?php echo esc_html( Options::$options['typo_h4_height'] ); ?>;
}

h5 {
    font-size: <?php echo esc_html( Options::$options['typo_h5_size'] ); ?>;
    line-height: <?php echo esc_html( Options::$options['typo_h5_height'] ); ?>;
}

h6 {
    font-size: <?php echo esc_html( Options::$options['typo_h6_size'] ); ?>;
    line-height: <?php echo esc_html( Options::$options['typo_h6_height'] ); ?>;
}