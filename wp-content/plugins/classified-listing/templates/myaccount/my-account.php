<?php
/**
 *
 * @author 		RadiusTheme
 * @package 	classified-listing/templates
 * @version     1.0.0
 */

use Rtcl\Helpers\Functions;

if (! defined('ABSPATH')) {
	exit;
}

?>
<div class="rtcl-MyAccount-mobile-navbar">
	<h4><?php esc_html_e( 'Account Menu', 'classified-listing' ); ?></h4>
	<?php Functions::get_site_logo(); ?>
	<div class="rtcl-MyAccount-open-menu"><span></span></div>
</div>
<div class="rtcl-MyAccount-wrap">
	<?php do_action('rtcl_account_navigation'); ?>

    <div class="rtcl-MyAccount-content">
		<?php Functions::print_notices(); ?>
		<?php do_action('rtcl_account_content'); ?>
    </div>
</div>
