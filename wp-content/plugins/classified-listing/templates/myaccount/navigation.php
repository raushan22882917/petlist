<?php
/**
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 */

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'rtcl_before_account_navigation' );
?>

<nav class="rtcl-MyAccount-navigation">
	<?php Functions::get_site_logo(); ?>
	<ul>
		<?php foreach ( Functions::get_account_menu_items() as $endpoint => $label ) : ?>
			<?php if ( 'add-listing' === $endpoint ): ?>
				<li class="<?php echo esc_attr( Functions::get_account_menu_item_classes( $endpoint ) ); ?>">
					<a href="<?php echo esc_url( Link::get_listing_form_page_link() ); ?>"><?php echo esc_html( $label ); ?></a>
				</li>
			<?php else: ?>
				<li class="<?php echo esc_attr( Functions::get_account_menu_item_classes( $endpoint ) ); ?>">
					<a data-href="<?php echo esc_url( Link::get_account_endpoint_url( $endpoint ) ); ?>"
					   href="<?php echo esc_url( Link::get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
				</li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
	<?php do_action( 'rtcl_after_account_navigation_list' ); ?>
</nav>

<?php do_action( 'rtcl_after_account_navigation' ); ?>
