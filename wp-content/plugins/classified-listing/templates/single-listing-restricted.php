<?php
/**
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 */

use Rtcl\Helpers\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
get_header( 'listing' ); ?>

<?php do_action( 'rtcl_before_content_wrapper' ); ?>

<?php
/**
 * rtcl_before_main_content hook.
 *
 * @hooked rtcl_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked rtcl_breadcrumb - 20
 */
do_action( 'rtcl_before_main_content' );
?>
	<div class="rtcl-restricted-content">
		<?php
		Functions::add_notice( __( "Need to login to access this page", 'classified-listing' ), 'error' );
		Functions::login_form();
		?>
	</div>
<?php
/**
 * rtcl_after_main_content hook.
 *
 * @hooked rtcl_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'rtcl_after_main_content' );
?>

<?php do_action( 'rtcl_after_content_wrapper' ); ?>

<?php
get_footer( 'listing' );
