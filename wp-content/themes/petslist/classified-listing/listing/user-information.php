<?php
/**
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
 * @package     classified-listing/templates
 * @version     1.0.0
 *
 */

use Rtcl\Helpers\Link;
use Rtcl\Helpers\Text;
use RtclPro\Helpers\Fns;
use Rtcl\Helpers\Functions;
use RadiusTheme\Petslist\Helper;
use RadiusTheme\Petslist\Options;

?>
<div class="rtcl-listing-user-info">
    <div class="list-group">
        <?php if (Fns::registered_user_only('listing_seller_information') && !is_user_logged_in()) { ?>
            <p class="login-message">
                <?php echo wp_kses(sprintf(__("Please <a href='%s'>login</a> to view the seller information.", "petslist"), esc_url(Link::get_my_account_page_link())), ['a' => ['href' => []]]); ?>
            </p>
        <?php } else {
            if ( Options::$options['single_sidebar_listing_info'] == 'listing_info' ){
                Helper::get_custom_listing_template( 'listing-info');
            } else {
                Helper::get_custom_listing_template( 'listing-owner-info');
            }
        } 
        ?>

    <?php
        if (Fns::is_enable_chat() && ((is_user_logged_in() && $listing->get_author_id() !== get_current_user_id()) || !is_user_logged_in())):
            $chat_btn_class = [ 'rtcl-chat-link' ];
            $chat_url = Link::get_my_account_page_link();
            $chat_label = esc_html__( "Quick Chat", 'petslist');
            $chant_enable_class = "rtcl-contact-seller";
            if ( is_user_logged_in() ) {
                $chat_url = '#';
                array_push( $chat_btn_class, 'rtcl-contact-seller' );
            } else {
                array_push( $chat_btn_class, 'rtcl-no-contact-seller' );
                $chat_label = "Login for Chat";
                $chant_enable_class = 'need-to-logedin';
            }
        ?>
        <div class="chat-form">
            <div class=<?php echo esc_attr( $chant_enable_class ); ?>>
                <a class="<?php echo esc_attr( implode( ' ', $chat_btn_class ) ) ?>" href="<?php echo esc_url( $chat_url ) ?>" data-listing_id="<?php the_ID() ?>">
                    <i class="icon-pl-chat"></i>
                    <?php echo esc_html( $chat_label ); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>


        <?php if ($has_contact_form && $email) : ?>
        <div class="contact-form">
            <div class='rtcl-do-email list-group-item'>
                <div class='media'>
                    <span class='rtcl-icon rtcl-icon-mail'></span>
                    <div class='media-body'>
                        <a class="rtcl-do-email-link" href='#'>
                            <?php echo Text::get_single_listing_email_button_text(); ?>
                        </a>
                    </div>
                </div>
                <?php Functions::print_html($email_to_seller_form, true); ?>
            </div>
        </div>
    <?php endif; ?>

    </div>
</div>