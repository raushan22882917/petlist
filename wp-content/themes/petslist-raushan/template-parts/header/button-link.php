<?php
/**
 * Header auth button: Login (guest) or profile avatar → Dashboard (logged in).
 *
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

use RadiusTheme\Petslist\Helper;
use RadiusTheme\Petslist\Options;

if ( ! Options::$options['header_btn'] ) {
	return;
}

$is_logged_in = is_user_logged_in();
$btn_url      = $is_logged_in
	? ( function_exists( 'dd_dashboard_url' ) ? dd_dashboard_url() : home_url( '/my-account/' ) )
	: ( function_exists( 'dd_login_url' ) ? dd_login_url() : ( Options::$options['header_btn_url'] ?: home_url( '/login/' ) ) );
$btn_label    = $is_logged_in ? __( 'Dashboard', 'petslist' ) : __( 'Login', 'petslist' );
$btn_class    = 'button-style-1 btn-anim header-auth-btn' . ( $is_logged_in ? ' header-auth-btn--logged-in' : '' );
?>
<div class="header-btn-area">
	<a class="<?php echo esc_attr( $btn_class ); ?>" href="<?php echo esc_url( $btn_url ); ?>">
		<?php if ( $is_logged_in ) :
			$user_id    = get_current_user_id();
			$pp_id      = absint( get_user_meta( $user_id, '_rtcl_pp_id', true ) );
			$avatar_url = $pp_id ? wp_get_attachment_image_url( $pp_id, 'thumbnail' ) : get_avatar_url( $user_id, array( 'size' => 56 ) );
			?>
			<img src="<?php echo esc_url( $avatar_url ); ?>" alt="" class="header-auth-avatar" width="28" height="28" loading="lazy">
			<span><?php echo esc_html( $btn_label ); ?></span>
		<?php else : ?>
			<?php echo Helper::plus_icon(); ?>
			<span><?php echo esc_html( $btn_label ); ?></span>
		<?php endif; ?>
	</a>
</div>
