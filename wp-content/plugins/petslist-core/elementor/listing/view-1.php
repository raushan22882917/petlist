<?php
/**
 * This file can be overridden by copying it to yourtheme/elementor-custom/listing/view-1.php
 * 
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

use RtclPro\Helpers\Fns;
use Rtcl\Helpers\Functions;
use RadiusTheme\Petslist\Helper;
use RadiusTheme\Petslist\Options;
global $listing;

extract( $data );
$dclass = '';

$post_type = 'rtcl_listing';
$grid_query = null;
$args = array(
  'post_type'      => $post_type,
  'post_status'    => 'publish',
  'posts_per_page' => $number,
  'orderby'        => $orderby
);

if ( $query_type == 'loccat' ) {

  $tax_query = [];

  if(!empty($locations)){
    $tax_query[] = array(
      'taxonomy' => 'rtcl_location',
      'field'    => 'id',
      'terms' => $locations
    );
  }
  if(!empty($terms)){
    $tax_query[] = array(
      'taxonomy' => 'rtcl_category',
      'field'    => 'id',
      'terms' => $terms
    );
  }

  if(!empty($tax_query)){
    if(count($tax_query) > 1){
      $tax_query['relation'] = 'AND';
    }
    $args['tax_query'] = $tax_query;
  }
} elseif ( $query_type == 'titles' && !empty( $postbytitle ) ) {
  $args['post__in'] = $postbytitle;
}

$grid_query = new \WP_Query( $args );

if ( $grid_query->have_posts() ) :

?>
<div class="rtcl">
  <div class="rtcl-listings rtcl-grid-view columns-<?php echo esc_attr( $cols ); ?>">
    <?php
      while ( $grid_query->have_posts() ) : $grid_query->the_post();

        if ( $listing && Fns::is_enable_mark_as_sold() && Fns::is_mark_as_sold( $listing->get_id() ) ) {
          $action_class = 'is-sold';
        } else {
          $action_class = '';
        }
    ?>
    <div <?php Functions::listing_class('listing-layout-'.$style, $listing) ?><?php Functions::listing_data_attr_options() ?>>
      <?php Helper::get_custom_listing_template( 'archive/grid/grid-'.$style ); ?>
    </div>
    <?php endwhile; wp_reset_postdata(); ?>
  </div>
  <?php endif; ?>
</div>