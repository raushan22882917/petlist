<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

use RadiusTheme\Petslist\Helper;
use RadiusTheme\Petslist\Options;
use Rtcl\Helpers\Link;

$nav_menu_args = Helper::nav_menu_args();

$site_name          = get_bloginfo( 'name' );
$custom_logo_id     = get_theme_mod( 'custom_logo' );
$default_logo       = $custom_logo_id ? wp_get_attachment_image_src( $custom_logo_id, 'full' ) : [
	Helper::get_img( 'logo.png' ),
	196,
	41
];
$default_light_logo = $custom_logo_id ? wp_get_attachment_image_src( $custom_logo_id, 'full' ) : [
	Helper::get_img( 'logo-white.png' ),
	157,
	40
];
$main_logo          = ( isset( Options::$options['logo'] ) && 0 != Options::$options['logo'] ) ? wp_get_attachment_image_src( Options::$options['logo'], 'full' ) : $default_logo;
$light_logo         = ( isset( Options::$options['logo_dark'] ) && 0 != Options::$options['logo_dark'] ) ? wp_get_attachment_image_src( Options::$options['logo_dark'], 'full' )
	: $default_light_logo;
$mobile_logo        = ( isset( Options::$options['mobile_logo'] ) && 0 != Options::$options['mobile_logo'] ) ? wp_get_attachment_image_src( Options::$options['mobile_logo'],
	'full' )
	: '';

if ( ( isset( Options::$options['logo'] ) && 0 != Options::$options['logo'] ) && ! ( isset( Options::$options['logo_dark'] ) && 0 != Options::$options['logo_dark'] ) ) {
	$mobile_logo = $main_logo;
}

if ( ! ( isset( Options::$options['logo'] ) && 0 != Options::$options['logo'] ) && ( isset( Options::$options['logo_dark'] ) && 0 != Options::$options['logo_dark'] ) ) {
	$mobile_logo = $light_logo;
}

if ( Options::$has_tr_header ) {
	$logo = $light_logo;
} else {
	$logo = $main_logo;
}
?>

<div class="rt-mobile-menu header-style-<?php echo esc_attr( Options::$header_style ); ?> headroom-sticky-header headroom-mobile-sticky-header header--fixed headroom">
    <div class="mobile-menu-bar">
        <div class="mobile-logo-area <?php echo esc_attr( ! empty( $mobile_logo ) ? 'has-mobile-logo' : '' ) ?>">
		    <?php if ( ! empty( $logo ) ): ?>
                <a class="custom-logo site-main-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <img class="img-fluid" src="<?php echo esc_url( $logo[0] ); ?>" width="<?php echo esc_attr( $logo[1] ); ?>" height="<?php echo esc_attr( $logo[2] ); ?>"
                         alt="<?php echo esc_attr( $site_name ); ?>">
                </a>
		    <?php else: ?>
                <h1 class="site-title site-main-logo">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php esc_attr_e( 'Home', 'petslist' ); ?>" rel="home">
					    <?php echo esc_html( $site_name ); ?>
                    </a>
                </h1>
		    <?php endif; ?>
		    <?php if ( ! empty( $mobile_logo ) ) : ?>
                <a class="custom-logo site-mobile-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <img class="img-fluid" src="<?php echo esc_url( $mobile_logo[0] ); ?>" width="<?php echo esc_attr( $mobile_logo[1] ); ?>"
                         height="<?php echo esc_attr( $mobile_logo[2] ); ?>" alt="<?php echo esc_attr( $site_name ); ?>">
                </a>
		    <?php endif; ?>
        </div>
		<div class="mobile-menu-right-part">
			<?php
			$html = '';
			$html .= '<span class="mobile-search-icon"><i aria-hidden="true" class=" icon-pl-search"></i></span>';
			if ( Options::$options['header_btn'] && Options::$options['header_btn_txt'] ) {
				$html .= '<a class="header-btn header-btn-mob" href="' . esc_url( Options::$options['header_btn_url'] ) . '"><i class="fas fa-plus" aria-hidden="true"></i><span>' . esc_html( Options::$options['header_btn_txt'] ) . '</span></a>';
			}

			if ( Helper::is_chat_enabled() ) {
				$html .= '<a class="header-chat-icon header-chat-icon-mobile rtcl-chat-unread-count" href="' . esc_url( Link::get_my_account_page_link( 'chat' ) ) . '"><i class="icon-pl-chat"></i></a>';
			}

			if ( class_exists( 'Rtcl' ) && Options::$options['header_login_icon'] ) {
				$html .= '<a class="header-login-icon header-login-icon-mobile" href="' . esc_url( Link::get_my_account_page_link() ) . '"><i class="icon-pl-account"></i></a>';
			}

			if ( $html ) {
				printf( '<div class="header-mobile-icons">%s</div>', $html );
			}
			?>
			<span class="sidebarBtn"><span></span></span>
		</div>
    </div>
    <div class="rt-slide-nav">
        <div class="offscreen-navigation">
			<?php wp_nav_menu( $nav_menu_args ); ?>
        </div>
    </div>
	<?php get_template_part( 'template-parts/header/header-search', 'mobile' ) ?>
</div>

