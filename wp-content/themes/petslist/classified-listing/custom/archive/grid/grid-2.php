<?php 
/**
 * Listing Archive Layout
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 */

use Rtcl\Controllers\Hooks\TemplateHooks;
use RadiusTheme\Petslist\Listing_Functions;
use RtclPro\Controllers\Hooks\TemplateHooks as ProTemplateHooks;

global $listing;

?>
<?php
/**
 * Hook: rtcl_before_listing_loop_item.
 *
 * @hooked rtcl_template_loop_product_link_open - 10
 */
do_action( 'rtcl_before_listing_loop_item' );

/**
 * Hook: rtcl_listing_loop_item.
 *
 * @hooked listing_thumbnail - 10
 */
do_action( 'rtcl_listing_loop_item_start' );

/**
 * Hook: rtcl_listing_loop_item.
 *
 * @hooked loop_item_wrap_start - 10
 * @hooked loop_item_listing_title - 20
 * @hooked loop_item_labels - 30
 * @hooked loop_item_listable_fields - 40 
 * @hooked loop_item_meta - 50
 * @hooked loop_item_excerpt - 60
 * @hooked loop_item_wrap_end - 100
 */

?>

<?php echo TemplateHooks::loop_item_wrapper_start(); ?>
    <div class="price-time-box">
        <?php echo TemplateHooks::listing_price(); ?>
        <?php if ( $listing->can_show_date() ) : ?>
            <div class="date-time">
                <?php Listing_Functions::petslist_the_time(get_the_ID()); ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="all-meta-info-box">
        <?php echo TemplateHooks::loop_item_listing_title(); ?>
        <?php echo ProTemplateHooks::loop_item_listable_fields(); ?>
        <?php echo TemplateHooks::loop_item_meta(); ?>
    </div>
<?php echo TemplateHooks::loop_item_wrapper_end(); ?>

<?php 

/**
 * Hook: rtcl_after_listing_loop_item.
 *
 * @hooked listing_loop_map_data - 50
 */
do_action( 'rtcl_after_listing_loop_item' );
?>