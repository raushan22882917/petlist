<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

use RadiusTheme\Petslist\Helper;
use RadiusTheme\Petslist\Options;

$socials = Helper::socials();
$widget_areas = Options::$options['f3_widgets_area'];

?>

<!--=====================================-->
<!--=        Footer 1 Area Start        =-->
<!--=====================================-->
<footer class="footer footer-style-3">
    <?php if ( is_active_sidebar( 'footer-widget-3-1' ) || is_active_sidebar( 'footer-widget-3-2' ) || is_active_sidebar( 'footer-widget-3-3' ) || is_active_sidebar( 'footer-widget-3-4' ) ) { ?>
    <div class="footer-top">
        <div class="container">
            <div class="row justify-content-between footer-widget-area">
                <?php for ( $i = 1; $i <= $widget_areas; $i++ ) { ?>
                <div class="col-lg-<?php echo esc_attr(Options::$options['f3_area'.$i.'_column']); ?> col-md-6">
                    <?php dynamic_sidebar( 'footer-widget-3-'.esc_attr($i) ); ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php } ?>
    <div class="footer-bottom">
        <div class="container">
            <div class="copyright-area">
				<?php if ( !empty( Options::$options['f3_cr_logo']) ) { ?>
					<div class="copyright-logo">
						<?php echo wp_get_attachment_image( Options::$options['f3_cr_logo'], 'full' ); ?>
					</div>
				<?php } ?>
                <div class="copyright-text">
                    <p class="footer-copyright mb-0"><?php echo wp_kses_stripslashes( Options::$options['copyright_text'] ); ?></p>
                </div>
				<?php if ( $socials ): ?>
					<div class="social-btn">
						<ul class="social-list d-flex align-items-center">
							<?php foreach ( $socials as $social ): ?>
							<li class="social-item">
								<a href="<?php echo esc_url( $social['url'] ); ?>" class="<?php echo esc_attr( $social['class'] ); ?> circle-radius d-flex justify-content-center align-items-center" target="_blank">
									<i class="<?php echo esc_attr( $social['icon'] ); ?>"></i>
								</a>
							</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
            </div>
        </div>
    </div>
</footer>
<!--=====================================-->
<!--=          Footer Area End          =-->
<!--=====================================-->    