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

    <div class="footer-bottom">
        <div class="container">
            <div class="copyright-area">
				<?php if ( function_exists( 'petslist_logo_img' ) ) { ?>
					<div class="copyright-logo">
						<?php echo petslist_logo_img( 'footer' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php } ?>
                <div class="copyright-text">
                    <p class="footer-copyright mb-0">
                        <?php 
                        $copyright = Options::$options['copyright_text'];
                        if ( empty( $copyright ) ) {
                            $copyright = sprintf( __( '&copy; %s Petslist. All Rights Reserved.', 'petslist' ), date( 'Y' ) );
                        }
                        echo wp_kses_post( $copyright ); 
                        ?>
                    </p>
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