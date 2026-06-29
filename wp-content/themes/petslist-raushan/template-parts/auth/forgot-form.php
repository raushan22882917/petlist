<?php
/**
 * Forgot Password Form
 * @package Petslist Dog Directory
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( is_user_logged_in() ) {
    echo '<p class="dd-notice dd-notice--info">' . sprintf( __( 'You are logged in. <a href="%s">Go to Dashboard</a>.', 'petslist' ), esc_url( dd_dashboard_url() ) ) . '</p>';
    return;
}
?>
<div id="rtcl-user-login-wrapper" class="separate-registration-form dd-auth-split-layout">
    <div class="dd-auth-split-image">
        <!-- Dog background pattern overlay -->
        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: url('http://localhost:8000/wp-content/uploads/2023/08/banner-bg.png'); background-size: cover; opacity: 0.15; pointer-events: none; z-index: 1;"></div>
        
        <!-- Dog illustration -->
        <img src="http://localhost:8000/wp-content/uploads/2023/08/banner-img-1.png" alt="Dog" style="max-width: 80%; max-height: 60%; position: absolute; bottom: 20px; z-index: 2; pointer-events: none;">

        <div class="dd-auth-split-overlay">
            <h3>🐾 Reset Password</h3>
            <p>Provide your email address to recover access to your breeder account and pedigree directory listings.</p>
        </div>
    </div>
    <div class="dd-auth-split-form">
        <div class="rtcl-login-form-wrap">
            <h2><?php _e( 'Reset Password', 'petslist' ); ?></h2>
            
            <div id="dd-forgot-message" class="alert" style="display:none; margin-bottom: 20px;"></div>

            <form id="dd-forgot-form" class="form-horizontal" novalidate>
                <div class="rtcl-form-group">
                    <label for="dd-forgot-email" class="rtcl-field-label">
                        <?php _e( 'Email Address', 'petslist' ); ?>
                        <strong class="rtcl-required">*</strong>
                    </label>
                    <input type="email" id="dd-forgot-email" name="email" class="rtcl-form-control" placeholder="<?php esc_attr_e( 'you@example.com', 'petslist' ); ?>" required autocomplete="email">
                </div>

                <div class="rtcl-form-group">
                    <button type="submit" class="rtcl-btn btn btn-primary" style="width: 100%;">
                        <span class="dd-btn__text"><?php _e( 'Send Reset Link', 'petslist' ); ?></span>
                        <span class="dd-btn__loader" style="display:none; margin-left: 8px;"><i class="fa-solid fa-spinner fa-spin"></i></span>
                    </button>
                </div>
            </form>

            <div style="margin-top: 20px; font-size: 14px; text-align: center;">
                <p><?php printf( __( 'Remembered? <a href="%s">Back to login</a>', 'petslist' ), esc_url( dd_login_url() ) ); ?></p>
            </div>
        </div>
    </div>
</div>
