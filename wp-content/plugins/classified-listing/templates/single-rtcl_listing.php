<?php
/**
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 */

use Rtcl\Helpers\Functions;
use Rtcl\Services\FormBuilder\FBHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( Functions::is_block_theme() ) {
	if ( function_exists( 'wp_load_block_template' ) ) {
		wp_load_block_template();
	}

	if ( function_exists( 'block_header_area' ) ) {
		block_header_area();
	}
} else {
	get_header( 'listing' );
}
?>

<?php do_action( 'rtcl_before_content_wrapper' ); ?>

<?php
/**
 * rtcl_before_main_content hook.
 *
 * @hooked rtcl_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked rtcl_breadcrumb - 20
 */
do_action( 'rtcl_before_main_content' );
global $listing;
$enableBuilder = FBHelper::isEnableSingleBuilder( $listing );
?>

<?php while ( have_posts() ) : ?>
	<?php the_post(); ?>

	<?php if ( $enableBuilder ) {
		$form = $listing->getForm();
		Functions::get_template( 'single-layout/builder', [ 'form' => $form ] );
	} else {
		Functions::get_template_part( 'content', 'single-rtcl_listing' );
	} ?>

<?php endwhile; // end of the loop. ?>

<?php
/**
 * rtcl_after_main_content hook.
 *
 * @hooked rtcl_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'rtcl_after_main_content' );
?>

<?php
if ( ! $enableBuilder ) {
	/**
	 * rtcl_sidebar hook.
	 *
	 * @hooked rtcl_get_sidebar - 10
	 */
	do_action( 'rtcl_sidebar' );
}
?>

<?php do_action( 'rtcl_after_content_wrapper' ); ?>

<?php
if ( Functions::is_block_theme() ) {
	if ( function_exists( 'block_footer_area' ) ) {
		block_footer_area();
	}
} else {
	get_footer( 'listing' );
}
