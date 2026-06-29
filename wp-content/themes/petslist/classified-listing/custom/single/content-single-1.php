<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */
use RtclPro\Helpers\Fns;
use Rtcl\Helpers\Functions;
use RadiusTheme\Petslist\Listing_Functions;
use Rtcl\Controllers\BusinessHoursController;

defined('ABSPATH') || exit;

global $listing;

$sidebar_position = Functions::get_option_item( 'rtcl_moderation_settings', 'detail_page_sidebar_position', 'right' );
$sidebar_class    = [
	'col-lg-4',
	'order-2 sidebar-possition-left'
];
$content_class = [
	'col-lg-8',
	'order-1',
	'listing-content'
];
if ( $sidebar_position == "left" ) {
	$sidebar_class   = array_diff( $sidebar_class, [ 'order-2' ] );
	$sidebar_class[] = 'order-1';
	$content_class   = array_diff( $content_class, [ 'order-1' ] );
	$content_class[] = 'order-2';
} else if ( $sidebar_position == "bottom" ) {
	$content_class   = array_diff( $content_class, [ 'col-lg-8' ] );
	$sidebar_class   = array_diff( $sidebar_class, [ 'col-lg-4' ] );
	$content_class[] = 'col-sm-12';
	$sidebar_class[] = 'rtcl-listing-bottom-sidebar';
}

$detailOption = Functions::get_option_item( 'rtcl_moderation_settings', 'display_options_detail', [] );

$can_report_abuse = Functions::get_option_item( 'rtcl_moderation_settings', 'has_report_abuse', '', 'checkbox' ) ? true : false;

$listing_type = Listing_Functions::get_listing_type( $listing );

$social_page = Functions::get_option_item('rtcl_misc_settings', 'social_pages', array('listing'));

$hide_listing_map   = get_post_meta( get_the_ID(), 'hide_map', true );

?>
<div class="listing-single-wrapper content-area">
   <div class="container">
      <div id="rtcl-listing-<?php the_ID(); ?>" <?php Functions::listing_class( 'listing-details-1', $listing ); ?>>
         <div class="listingDetails-header">
            <?php 
               $feature_img = '';
               $images = $listing->get_images();
               if (empty($images)) {
                     $feature_img = 'feature-img-not-set';
               }
               if (!empty($images)) {
            ?>
               <?php do_action('rt_pets_list_galley_before'); ?>
               <div class="page-header">
                  <?php do_action('rt_pets_list_galley'); ?>
               </div>
               <?php do_action('rt_pets_list_galley_after'); ?>
            <?php } ?>
         </div>
         <div class="row">
            <!-- Main content -->
            <div class="<?php echo esc_attr( implode( ' ', $content_class ) ); ?>">
               <div class="listing-details">
                  <div class="listing-details-head">
                     <div class="meta-info-box">
                        <div class="listing-details-head-top">
                           <div class="listing-badge">
                              <?php if ( in_array('ad_type', $detailOption) && ! empty( $listing_type ) ) : ?>
                                 <span class="listing-type-badge">
                                    <?php echo sprintf( "%s %s", apply_filters( 'rtcl_type_prefix', __( 'For', 'petslist' ) ), $listing_type['label'] ); ?>
                                 </span>
                              <?php endif; ?>
                              <?php $listing->the_badges(); ?>
                           </div>
                           <div class="rtcl-single-actions">
                              <ul class="meta-tags">
                                 <?php if ( in_array('listing', $social_page) ) { ?>
                                    <li class="social-share-li"> 
                                       <button class="listing-social-action" data-bs-toggle="modal" data-bs-target="#exampleModalCenter">
                                          <i class="icon-share-2"></i>
                                       </button>
                                    </li>
                                 <?php } if (Functions::is_enable_favourite()){ ?>
                                    <li class="meta-favourite">
                                       <?php echo Listing_Functions::get_favourites_link( $listing->get_id() ); ?>
                                    </li>
                                 <?php } if ( Fns::is_enable_compare() ) { ?>
                                    <li class="meta-compare">
                                       <?php
                                          $compare_ids = ! empty( $_SESSION['rtcl_compare_ids'] ) ? $_SESSION['rtcl_compare_ids'] : [];
                                          $selected_class = '';
                                          if ( is_array( $compare_ids ) && in_array( $listing->get_id(), $compare_ids ) ) {
                                             $selected_class = ' selected';
                                          }
                                       ?>
                                       <a class="rtcl-compare <?php echo esc_attr( $selected_class ); ?>" href="#" data-listing_id="<?php echo absint( $listing->get_id() ) ?>">
                                          <i class="icon-compare"></i>
                                       </a>
                                    </li>
                                 <?php } if ( $can_report_abuse ){ ?>
                                    <li class="report-abuse-li">
                                          <?php if ( is_user_logged_in() ): ?>
                                          <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#rtcl-report-abuse-modal">
                                             <i class="icon-warning"></i>
                                          </a>
                                          <?php else: ?>
                                          <a href="javascript:void(0)" class="rtcl-require-login">
                                             <i class="icon-warning"></i>
                                          </a>
                                          <?php endif; ?>
                                    </li>
                                 <?php } ?>
                              </ul>
                           </div>
                        </div>
                        <div class="title-price">
                           <h2 class="rtcl-listing-title">
                              <?php the_title(); ?>
                           </h2>
                           <!-- Price -->
                           <?php if ( $listing->can_show_price() ): ?>
                              <div class="rtcl-price-wrap price-in-mobile">
                                 <?php echo wp_kses_stripslashes( $listing->get_price_html() ); ?>
                              </div>
                           <?php endif; ?>
                        </div>
                        <div class="meta-list">
                           <?php Listing_Functions::petslist_single_listing_meta(); ?>
                        </div>
                     </div>
                  </div>

                  <div class="rtcl-single-listing-details custom-fields-box">
                     <div class="rtcl-main-content-wrapper">
                        <div class="accordion accordion-flush" id="petslist-content-accordion">
                           <div class="accordion-item">
                              <h2 class="accordion-header" id="flush-headingOne">
                                 <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                    <?php esc_html_e( 'Pet Details', 'petslist' ); ?>
                                 </button>
                              </h2>
                              <div id="flush-collapseOne" class="accordion-collapse collapse show" aria-labelledby="flush-headingOne" data-bs-parent="#petslist-content-accordion">
                                 <!-- Description -->
                                 <div class="rtcl-listing-description"><?php $listing->the_content(); ?></div>

                                 <?php if ( $sidebar_position === "bottom" ) : ?>
                                    <!-- Sidebar -->
                                    <?php do_action( 'rtcl_single_listing_sidebar' ); ?>
                                 <?php endif; ?>

                                 <!--  Inner Sidebar -->
                                 <?php do_action( 'rtcl_single_listing_inner_sidebar', $listing ); ?>
                                 <?php $listing->the_custom_fields(); ?>
                              </div>
                           </div>
                        </div>
                     </div>
               </div>
               <?php if ( method_exists( 'Rtcl\Helpers\Functions', 'has_map' ) && Functions::has_map() && ! $hide_listing_map ){ ?>
                  <div class="rtcl-single-listing-details map-box">
                        <div class="rtcl-main-content-wrapper">
                           <div class="accordion accordion-flush" id="petslist-map-accordion">
                              <div class="accordion-item">
                                 <h2 class="accordion-header" id="flush-headingTwo">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
                                       <?php esc_html_e( 'Location', 'petslist' ); ?>
                                    </button>
                                 </h2>
                                 <div id="flush-collapseTwo" class="accordion-collapse collapse show" aria-labelledby="flush-headingTwo" data-bs-parent="#petslist-map-accordion">
                                    <!-- MAP  -->
                                    <?php do_action( 'rtcl_single_listing_content_end', $listing ); ?>
                                 </div>
                              </div>
                           </div>
                        </div>
                  </div>
               <?php } if ( Functions::is_enable_business_hours() ) { ?>
                  <div class="rtcl-single-listing-details business-hour-box">
                        <div class="rtcl-main-content-wrapper">
                           <div class="accordion accordion-flush" id="petslist-business-hour-accordion">
                              <div class="accordion-item">
                                 <h2 class="accordion-header" id="flush-headingThree">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
                                       <?php esc_html_e( 'Business Hours', 'petslist' ); ?>
                                    </button>
                                 </h2>
                                 <div id="flush-collapseThree" class="accordion-collapse collapse show" aria-labelledby="flush-headingThree" data-bs-parent="#petslist-business-hour-accordion">
                                    <!-- Business Hours  -->
                                    <?php if ( Functions::is_enable_business_hours() && ! empty( BusinessHoursController::get_business_hours( $listing->get_id() ) ) ): ?>
                                       <div class="content-block-gap"></div>
                                       <div class="single-business-hour">
                                          <div class="main-content">
                                             <?php do_action( 'rtcl_single_listing_business_hours' ); ?>
                                          </div>
                                       </div>
                                    <?php endif; ?>
                                 </div>
                              </div>
                           </div>
                        </div>
                  </div>
               <?php } if ( Functions::is_enable_social_profiles() ) { ?>
                  <div class="rtcl-single-listing-details social-profile-box">
                     <div class="rtcl-main-content-wrapper">
                        <div class="accordion accordion-flush" id="petslist-social-profile-accordion">
                           <div class="accordion-item">
                              <h2 class="accordion-header" id="flush-headingFour">
                                 <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFour" aria-expanded="false" aria-controls="flush-collapseFour">
                                    <?php esc_html_e( 'Social Profile', 'petslist' ); ?>
                                 </button>
                              </h2>
                              <div id="flush-collapseFour" class="accordion-collapse collapse show" aria-labelledby="flush-headingFour" data-bs-parent="#petslist-social-profile-accordion">
                                 <!-- Social Profile  -->
                                 <?php do_action( 'rtcl_single_listing_social_profiles' ) ?>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               <?php } if (Functions::get_option_item('rtcl_moderation_settings', 'enable_review_rating', false, 'checkbox')) { ?>
                  <div class="rtcl-single-listing-details review-box">
                     <div class="rtcl-main-content-wrapper">
                        <div class="accordion accordion-flush" id="petslist-review-accordion">
                           <div class="accordion-item">
                              <h2 class="accordion-header" id="flush-headingFive">
                                 <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFive" aria-expanded="false" aria-controls="flush-collapseFive">
                                    <?php esc_html_e( 'Review', 'petslist' ); ?>
                                 </button>
                              </h2>
                              <div id="flush-collapseFive" class="accordion-collapse collapse show" aria-labelledby="flush-headingFive" data-bs-parent="#petslist-review-accordion">
                                 <!-- Review  -->
                                 <?php do_action( 'rtcl_single_listing_review' ) ?>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <?php } ?>
               </div>

               <?php if ( in_array('listing', $social_page) ) { ?>
                  <div class="listing-details-socials">
                     <?php Listing_Functions::get_share_link(); ?>
                  </div>
               <?php } ?>

               <?php if ( $can_report_abuse ) { ?>
                  <?php Listing_Functions::get_repost_abuse(); ?>
               <?php } ?>
            </div>
            <?php if ( in_array( $sidebar_position, [ 'left', 'right' ] ) ) : ?>
               <!-- Sidebar -->
               <?php do_action( 'rtcl_single_listing_sidebar' ); ?>
            <?php endif; ?>
         </div>
      </div>
   </div>
</div>