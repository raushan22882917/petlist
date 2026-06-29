<?php
/**
 * @var string  $address
 * @var string  $phone
 * @var string  $whatsapp_number
 * @var string  $email
 * @var string  $website
 * @var array   $phone_options
 * @var bool    $has_contact_form
 * @var string  $email_to_seller_form
 * @var Listing $listing
 * @var array   $locations
 * @var int     $listing_id Listing id
 * @author      RadiusTheme
 * @package     petslist/templates
 * @version     1.0.0
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use RtclPro\Helpers\Fns;
use Rtcl\Helpers\Functions;
use RadiusTheme\Petslist\Helper;
use RadiusTheme\Petslist\Listing_Functions;

global $listing;
$owner_id          = $listing->get_owner_id();
$ownerUrl          = get_author_posts_url($owner_id);
$listingAddress    = get_post_meta( $listing->get_id(), 'address', true );
$listingPhone      = get_post_meta( $listing->get_id(), 'phone', true );
$listingWhatsapp   = get_post_meta( $listing->get_id(), '_rtcl_whatsapp_number', true );
$listingEmail      = get_post_meta( $listing->get_id(), 'email', true );
$website           = get_post_meta( $listing->get_id(), 'website', true );
$listingWebsite    = str_replace(['https://', 'http://'], '', $website );
$has_contact_form  = Functions::get_option_item( 'rtcl_moderation_settings', 'has_contact_form', false, 'checkbox' )

?>

<div class="listing-author listing-owner">
    <div class="author-logo-wrapper">
        <?php
            $pp_id = absint( get_user_meta( $listing->get_owner_id(), '_rtcl_pp_id', true ) );
            if ( $listing->can_add_user_link() ): ?>
                <a href="<?php echo esc_url($listing->get_the_author_url()); ?>"><?php echo wp_kses_post( $pp_id ? wp_get_attachment_image( $pp_id, [40, 40]) : get_avatar( $listing->get_author_id(), 40 ) ); ?></a>
            <?php else:
                echo wp_kses_post( $pp_id ? wp_get_attachment_image( $pp_id, [40, 40]) : get_avatar( $listing->get_author_id(), 40 ) );
            endif;
        ?>
    </div>
    <div class="author-info-wrapper">
        <h4 class="author-name">
            <?php if ( $listing->can_add_user_link() && ! is_author() ) : ?>
                <a class="author-link" href="<?php echo esc_url( $ownerUrl ); ?>">
                    <?php echo esc_html( $listing->get_owner_name() ); ?>
                </a>
            <?php else: ?>
                <?php echo wp_kses_post( $listing->get_owner_name() ); ?>
            <?php endif; ?>
        </h4>
        <div class="member-since">
            <?php 
                $since = date( "Y", strtotime(get_userdata($owner_id)->user_registered ));
                echo ( sprintf( __( "Member Since : %s", "petslist" ), $since ));
            ?>
        </div>
        <?php
            $status = apply_filters( 'rtcl_user_offline_text', esc_html__( 'Offline Now', 'petslist' ) );
            if ( Fns::is_online( $listing->get_owner_id() ) ) {
                $status = apply_filters( 'rtcl_user_online_text', esc_html__( 'Online Available', 'petslist' ) );
            }
        ?>
        <span class="rtcl-user-status <?php echo esc_attr( strtolower( $status ) ); ?>"><span class="user-staus-text"><?php echo wp_kses_post( $status ); ?></span></span>
        
        <div class="rtin-user-item">
            <?php do_action('rtcl_after_author_meta', $listing->get_owner_id() ); ?>
        </div>
    </div>
</div>

<ul class="info-list">
    <?php if ( $listingAddress ){ ?>
        <li>  
            <div class="icon d-flex justify-content-center align-items-center">
                <i class="icon-pl-location"></i>
            </div>
            <?php echo esc_html( $listingAddress ); ?>
        </li>
    <?php } if ( $listingEmail ){ ?>
        <li>
            <div class="icon d-flex justify-content-center align-items-center">
                <i class="icon-pl-message-box"></i>
            </div>
            <a class="rtcl-phone-link" href="mailto:<?php echo esc_attr( $listingEmail ); ?>" target="_blank">
                <?php echo esc_html( $listingEmail ); ?>
            </a>
        </li>
    <?php } if ( $website ){ ?>
        <li>
            <div class="icon d-flex justify-content-center align-items-center">
                <?php echo Helper::website_icon(); ?>
            </div>
            <a class="rtcl-website-link" href="<?php echo esc_url( $website ); ?>" target="_blank"
                <?php echo Functions::is_external( $website ) ? ' rel="nofollow"' : ''; ?>>
                <?php echo esc_html( $listingWebsite ); ?>
            </a>
        </li>
    <?php } ?>
</ul>

<?php 
    $social_list = Functions::get_user_social_profile( $owner_id );
    if ( ! empty( $social_list ) ) {
?>
    <div class="rtcl-user-social">
        <span><?php esc_html_e( 'Follow Us On:', 'petslist' ); ?></span>
        <div class="social-list">
            <?php
                foreach ( $social_list as $key => $value ) {
                    ?>
                    <a target="_blank" href="<?php echo esc_url( $value ) ?>">
                        <i class="rtcl-icon rtcl-icon-<?php echo esc_attr( $key ) ?>"></i>
                    </a>
                    <?php
                }
            ?>
        </div>
    </div>
<?php } ?>

<?php 
    Listing_Functions::the_phone($listingPhone, '', ''); 
    Listing_Functions::the_phone('', $listingWhatsapp, '');
?>
    