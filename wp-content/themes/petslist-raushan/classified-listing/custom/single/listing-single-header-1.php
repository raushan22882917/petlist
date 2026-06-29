<?php 

use Rtrs\Models\Review;
use RtclPro\Helpers\Fns;
use Rtcl\Helpers\Functions;
use RadiusTheme\CLHotel\Helper;
use RadiusTheme\CLHotel\Listing_Functions;
use Rtcl\Controllers\BusinessHoursController;
use Rtcl\Controllers\BusinessHoursController as BHS;
global $listing;
global $wp_locale;

$images = $listing->get_images();

$banner = Listing_Functions::listing_single_banner_option();

$business_hours = BHS::get_business_hours($listing->get_id());
if (BHS::openStatus($business_hours)) {
    $onoff = '<span class="onoff-status open"><i class="fas fa-check-circle"></i>'. esc_html__( 'Open', 'petslist' ).'</span>';
} else {
    $onoff = '<span class="onoff-status close"><i class="fas fa-times-circle"></i>'. esc_html__( 'Closed', 'petslist' ).'</span>';
}

$generalSettings = Functions::get_option( 'rtcl_general_settings' );

$detailOption = Functions::get_option_item( 'rtcl_moderation_settings', 'display_options_detail', [] );

if( class_exists( Review::class ) ){
    $rating_count   = Review::getTotalRatings( get_the_ID() );
} else {
    $rating_count   = $listing->get_rating_count();
} 

$show_phone = ! empty( $mod_settings['display_options'] ) && in_array( 'phone', $mod_settings['display_options'] );

$address = get_post_meta( $listing->get_id(), 'address', true );
$phone = get_post_meta( $listing->get_id(), 'phone', true );
$phone_url = str_replace( ' ', '', $phone );

$can_report_abuse = Functions::get_option_item( 'rtcl_moderation_settings', 'has_report_abuse', '', 'checkbox' ) ? true : false;

?>
<div class="listingDetails-header header-v1">
    <div class="row align-items-center">
        <div class="col-lg-9">
            <div class="listingDetails-header">
                <div class="listingDetails-header__fetures">
                    <ul class="ul-ol">
                        <?php if ( $listing->has_category() && $listing->can_show_category() ){ ?>
                            <li class="product-category">
                                <?php echo Helper::CLH_listing_categories( $listing->get_id()); ?>
                            </li> 
                        <?php }
                        if ( $listing && Fns::is_enable_mark_as_sold() && Fns::is_mark_as_sold( $listing->get_id() ) ) {
                            echo '<li class="rtcl-sold-out">' . apply_filters( 'rtcl_sold_out_header_text', esc_html__( "Sold Out", 'petslist' ) ) . '</li>';
                        }
                        ?>
                        <?php if ( $listing->can_show_price() ): ?>
                            <li class="product-price"><?php printf( "%s", $listing->get_price_html() ); ?></li>
                        <?php endif; ?>
                        <li class="product-badges"><?php $listing->the_badges(); ?></li>
                    </ul>
                </div>
                <h2 class="listingDetails-header__heading">
                    <?php the_title(); ?>
                </h2>
                <div class="listingDetails-header__meta">
                    <ul class="info-list">
                        <?php
                        if ( ! empty( $rating_count ) && in_array( 'rating', $detailOption ) ){ ?>
                            <li class="meta-address">
                                <?php Listing_Functions::get_listing_reviews( $listing ); ?>
                            </li>
                        <?php } if ( ! empty( $address ) && in_array( 'address', $detailOption ) ){ ?>
                            <li class="meta-address"><i class="demo-icon rt-custom-icon-rt18-marker"></i> <?php echo esc_html( $address ); ?></li>
                        <?php } if ( $listing->can_show_date() ) { ?>
                            <li class="meta-date"><i class="rt-custom-icon-calendar1-1"></i> <?php $listing->the_time(); ?></li>
                        <?php } if ( $listing->can_show_views() ){ ?>
                            <li class="meta-view">
                                <span>
                                    <i class="demo-icon rt-custom-icon-d-eye"></i>  
                                    <?php echo sprintf( _n( "Visitor: %s", "Visitors: %s", $listing->get_view_counts(), 'petslist' ), 
                                        number_format_i18n( $listing->get_view_counts() ) ); ?>
                                </span>
                            </li>
                        <?php } if ( Functions::is_enable_business_hours() && ! empty( BusinessHoursController::get_business_hours( $listing->get_id() ) ) ){ ?>
                            <li class="meta-status">
                                <?php echo wp_kses_post( $onoff ); ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-3 text-lg-end">
            <div class="listing-actions">
                <ul>
                    <?php if ( Fns::is_enable_compare() ) { ?>
                        <li class="meta-compare">
                        <?php
                            $compare_ids = ! empty( $_SESSION['rtcl_compare_ids'] ) ? $_SESSION['rtcl_compare_ids'] : [];
                            $selected_class = '';
                            if ( is_array( $compare_ids ) && in_array( $listing->get_id(), $compare_ids ) ) {
                                $selected_class = ' selected';
                            }
                        ?>
                        <a class="rtcl-compare <?php echo esc_attr( $selected_class ); ?>" href="#" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" title="<?php esc_attr_e( "Compare", "petslist" ) ?>" data-listing_id="<?php echo absint( $listing->get_id() ) ?>">
                            <i class="rt-custom-icon-compare"></i>
                        </a>
                        </li>
                    <?php } if (Functions::is_enable_favourite()){ ?>
                    <li class="meta-favourite" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" title="<?php esc_attr_e( "Favourite", "petslist" ) ?>">
                        <?php echo Listing_Functions::get_favourites_link( $listing->get_id() ); ?>
                    </li>
                    <?php } if ( in_array('social_share', $detailOption)){ ?>
                    <li class="social-share-li" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" title="<?php esc_attr_e( "Share", "petslist" ) ?>"> 
                        <button class="listing-social-action" data-bs-toggle="modal" data-bs-target="#exampleModalCenter">
                            <i class="rt-custom-icon-share-icon"></i>
                        </button>   
                    </li>
                    <?php } if ( $can_report_abuse ){ ?>
                        <li class="report-abuse-li">
                            <?php if ( is_user_logged_in() ): ?>
                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#rtcl-report-abuse-modal">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </a>
                            <?php else: ?>
                            <a href="javascript:void(0)" class="rtcl-require-login">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </a>
                            <?php endif; ?>
                        </li>
                    <?php } ?>
                    <li><a href="#" onclick="window.print();"  data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" title="<?php esc_attr_e( "Print", "petslist" ) ?>"><i class="fa-solid fa-print"></i></a></li>
                </ul>
            </div>
        </div>
    </div>

    <?php 
        $feature_img = '';
        $images = $listing->get_images();
        if (empty($images)) {
            $feature_img = 'feature-img-not-set';
        }
        if (!empty($images)) {
    ?>
        <div class="page-header">
            <?php 
                if ( $banner == 'slider' ) {
                    Listing_Functions::listing_details_slider();
                } else {
                    Listing_Functions::listing_details_gallery();
                }
            ?>
        </div>
    <?php } ?>
</div>

<?php 
    if ( in_array('social_share', $detailOption)){
        Listing_Functions::get_share_link(); 
    }
    if ( $can_report_abuse ){ 
        Listing_Functions::get_repost_abuse(); 
   } 
?>