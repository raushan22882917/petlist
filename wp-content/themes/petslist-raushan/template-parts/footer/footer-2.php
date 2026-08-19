<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

use RadiusTheme\Petslist\Helper;
use RadiusTheme\Petslist\Options;

$socials = Helper::socials();
$widget_areas = Options::$options['f2_widgets_area'];

$privacy_url = get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/privacy-policy/' );
$terms_url   = home_url( '/terms-and-conditions/' );
?>

<!--=====================================-->
<!--=        Footer 2 Area Start        =-->
<!--=====================================-->
<footer class="footer footer-style-2">
    <?php if ( is_active_sidebar( 'footer-widget-2-1' ) || is_active_sidebar( 'footer-widget-2-2' ) || is_active_sidebar( 'footer-widget-2-3' ) || is_active_sidebar( 'footer-widget-2-4' ) ) { ?>
    <div class="footer-top">
        <div class="container">
            <div class="row justify-content-between footer-widget-area">
                <?php for ( $i = 1; $i <= $widget_areas; $i++ ) { ?>
                <div class="col-lg-<?php echo esc_attr(Options::$options['f2_area'.$i.'_column']); ?> col-md-6">
                    <?php dynamic_sidebar( 'footer-widget-2-'.esc_attr($i) ); ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php } ?>
    <div class="footer-bottom">
        <div class="container">
            <div class="copyright-area justify-content-center text-center">
                <div class="copyright-text text-center w-100">
                    <p class="footer-copyright mb-0 text-center">&copy; Copyright <?php echo esc_html( date( 'Y' ) ); ?> Studs 4 You &ndash; All Right Reserved<span class="footer-sep">|</span><a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Privacy Policy', 'petslist' ); ?></a><span class="footer-sep">|</span><a href="<?php echo esc_url( $terms_url ); ?>"><?php esc_html_e( 'Terms and Conditions', 'petslist' ); ?></a></p>
                </div>
            </div>
        </div>
    </div>
</footer>
<!--=====================================-->
<!--=          Footer Area End          =-->
<!--=====================================-->    