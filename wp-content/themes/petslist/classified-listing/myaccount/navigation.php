<?php
/**
 *
 * @author 		RadiusTheme
 * @package 	classified-listing/templates
 * @version     1.0.0
 */

use RadiusTheme\Petslist\Options;
 use RadiusTheme\Petslist\Helper;
use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
do_action( 'rtcl_before_account_navigation' );
$logo_url = '';
$logo_url = wp_get_attachment_image_url( Options::$options['logo'], 'full' );

?>
<nav class="rtcl-MyAccount-navigation">
	<?php $light_logo = empty( $logo_url ) ? Helper::get_img( 'logo-light.svg' ) : $logo_url; ?>
    <div class="rtcl-myaccount-logo">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php if ( ! empty( $logo_url ) ) { ?>
                <a class="light-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php echo wp_get_attachment_image( Options::$options['logo'], 'full' ); ?>
                </a>
			<?php } else { ?>
                <a class="light-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <img src="<?php echo esc_url( $light_logo ); ?>" width="150" height="45" alt="<?php bloginfo( 'name' ); ?>">
                </a>
			<?php } ?>
        </a>
    </div>
	<ul>
		<?php foreach ( Functions::get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="<?php echo Functions::get_account_menu_item_classes( $endpoint ); ?>">
				<a href="<?php echo esc_url( Link::get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php do_action( 'rtcl_after_account_navigation_list' ); ?>
</nav>

<?php do_action( 'rtcl_after_account_navigation' ); ?>

