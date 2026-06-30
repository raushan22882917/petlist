<?php
/**
 * Login Form Template Part
 * @package Petslist Dog Directory
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$redirect_to = esc_url( $_GET['redirect_to'] ?? dd_dashboard_url() );
$dd_auth_bg     = function_exists( 'petslist_img_url' ) ? petslist_img_url( 'auth_bg' ) : wp_upload_dir()['baseurl'] . '/2023/08/banner-bg.png';
$dd_auth_banner = function_exists( 'petslist_img_url' ) ? petslist_img_url( 'auth_banner' ) : wp_upload_dir()['baseurl'] . '/2023/08/banner-img-1.png';
?>
<div id="rtcl-user-login-wrapper" class="separate-registration-form dd-auth-split-layout">
    <div class="dd-auth-split-image">
        <!-- Dog background pattern overlay -->
        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: url('<?php echo esc_url( $dd_auth_bg ); ?>'); background-size: cover; opacity: 0.15; pointer-events: none; z-index: 1;"></div>
        
        <!-- Dog illustration -->
        <img src="<?php echo esc_url( $dd_auth_banner ); ?>" alt="Dog" style="max-width: 80%; max-height: 60%; position: absolute; bottom: 20px; z-index: 2; pointer-events: none;">

        <div class="dd-auth-split-overlay">
            <h3>🐾 Dog Directory</h3>
            <p>Connect with verified breeders, view pedigrees, search health clearances, and list your companion dogs.</p>
        </div>
    </div>
    <div class="dd-auth-split-form">
        <div class="rtcl-login-form-wrap">
            <h2><?php esc_html_e( 'Login', 'classified-listing' ); ?></h2>
            
            <div id="dd-login-message" class="alert" style="display:none; margin-bottom: 20px;"></div>

            <form id="dd-login-form" class="form-horizontal" novalidate>
                <div class="rtcl-form-group">
                    <label for="dd-login-email" class="rtcl-field-label">
                        <?php _e('Email Address', 'petslist'); ?>
                        <strong class="rtcl-required">*</strong>
                    </label>
                    <input type="email" id="dd-login-email" name="email" class="rtcl-form-control" placeholder="<?php esc_attr_e('you@example.com', 'petslist'); ?>" required autocomplete="email">
                </div>

                <div class="rtcl-form-group">
                    <label for="dd-login-pass" class="rtcl-field-label">
                        <?php _e('Password', 'petslist'); ?>
                        <strong class="rtcl-required">*</strong>
                        <a href="<?php echo esc_url(dd_page_url('dd_page_forgot', 'dog-forgot-password')); ?>" style="float: right; font-size: 13px; font-weight: normal; text-transform: none;"><?php _e('Forgot?', 'petslist'); ?></a>
                    </label>
                    <div class="rtcl-user-pass-wrap">
                        <input type="password" id="dd-login-pass" name="password" class="rtcl-form-control rtcl-password" placeholder="••••••••" required autocomplete="current-password">
                        <span class="rtcl-toggle-pass rtcl-icon-eye-off dd-toggle-pass"></span>
                    </div>
                </div>

                <div class="rtcl-form-group">
                    <label class="rtcl-checkbox-label">
                        <input type="checkbox" name="remember" id="dd-remember">
                        <?php _e('Keep me signed in', 'petslist'); ?>
                    </label>
                </div>

                <input type="hidden" name="redirect_to" value="<?php echo $redirect_to; ?>">

                <div class="rtcl-form-group">
                    <button type="submit" class="rtcl-btn btn btn-primary" id="dd-login-submit" style="width: 100%;">
                        <span class="dd-btn__text"><?php _e('Sign In', 'petslist'); ?></span>
                        <span class="dd-btn__loader" style="display:none; margin-left: 8px;"><i class="fa-solid fa-spinner fa-spin"></i></span>
                    </button>
                </div>
            </form>

            <div style="margin-top: 20px; font-size: 14px; text-align: center;">
                <p><?php printf(
                    __('Don\'t have an account? <a href="%s">Create one free</a>', 'petslist'),
                    esc_url(dd_register_url())
                ); ?></p>
            </div>
        </div>
    </div>
</div>
