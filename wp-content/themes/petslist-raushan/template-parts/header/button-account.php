<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

 use RadiusTheme\Petslist\Options;
 use Rtcl\Helpers\Link;

$login_icon_title = is_user_logged_in() ? esc_html__( 'Account', 'petslist' ) : esc_html__( 'Login', 'petslist' );

?>
<?php if ( class_exists( 'Rtcl' ) && Options::$options['header_login_icon'] ): ?>
    <a class="header-login-icon" data-toggle="tooltip" title="<?php echo esc_html( $login_icon_title ); ?>" href="<?php echo esc_url( Link::get_my_account_page_link() ); ?>">
        <?php if ( is_user_logged_in() ) : 
            $user_id = get_current_user_id();
            $pp_id = absint( get_user_meta( $user_id, '_rtcl_pp_id', true ) );
            $avatar_url = $pp_id ? wp_get_attachment_image_url( $pp_id, 'thumbnail' ) : get_avatar_url( $user_id, ['size' => 56] );
        ?>
            <span class="header-login-avatar-wrap">
                <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr( $login_icon_title ); ?>" class="header-login-avatar" width="32" height="32" style="width:32px;height:32px;border-radius:50%;object-fit:cover;display:block;flex-shrink:0;">
            </span>
        <?php else: ?>
            <i class="icon-pl-account"></i>
        <?php endif; ?>
        <span class="header-login-text"><?php echo esc_html( $login_icon_title ); ?></span>
    </a>
<?php endif; ?>