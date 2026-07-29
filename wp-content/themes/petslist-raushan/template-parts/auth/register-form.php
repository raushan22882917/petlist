<?php
/**
 * Register Form Template Part
 * @package Petslist Dog Directory
 */
if (!defined('ABSPATH'))
    exit;
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
        <div class="rtcl-registration-form-wrap">
            <h2><?php esc_html_e( 'Register', 'classified-listing' ); ?></h2>

            <div id="dd-register-message" class="alert" style="display:none; margin-bottom: 20px;"></div>

            <form id="dd-register-form" class="form-horizontal" novalidate>
                <div class="dd-form-section-title" style="font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #bd8c42; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-user-gear" style="color: #bd8c42;"></i> <?php _e('Owner Profile Details', 'petslist'); ?>
                </div>

                <div class="rtcl-form-group">
                    <label for="dd-reg-name" class="rtcl-field-label">
                        <?php _e('Name', 'petslist'); ?>
                        <strong class="rtcl-required">*</strong>
                    </label>
                    <input type="text" id="dd-reg-name" name="name" class="rtcl-form-control" placeholder="<?php esc_attr_e('Your full name', 'petslist'); ?>" required autocomplete="name">
                </div>

                <div class="rtcl-form-group">
                    <label for="dd-reg-location" class="rtcl-field-label">
                        <?php _e('Location', 'petslist'); ?>
                        <strong class="rtcl-required">*</strong>
                    </label>
                    <input type="text" id="dd-reg-location" name="location" class="rtcl-form-control" placeholder="<?php esc_attr_e('City, State / Country', 'petslist'); ?>" required>
                </div>

                <div class="rtcl-form-group">
                    <label for="dd-reg-phone" class="rtcl-field-label">
                        <?php _e('Phone', 'petslist'); ?>
                        <strong class="rtcl-required">*</strong>
                    </label>
                    <input type="tel" id="dd-reg-phone" name="phone" class="rtcl-form-control" placeholder="<?php esc_attr_e('+1 (555) 000-0000', 'petslist'); ?>" required autocomplete="tel">
                </div>

                <div class="rtcl-form-group">
                    <label for="dd-reg-email" class="rtcl-field-label">
                        <?php _e('Email', 'petslist'); ?>
                        <strong class="rtcl-required">*</strong>
                    </label>
                    <input type="email" id="dd-reg-email" name="email" class="rtcl-form-control" placeholder="<?php esc_attr_e('you@example.com', 'petslist'); ?>" required autocomplete="email">
                </div>

                <div class="rtcl-form-group">
                    <label class="rtcl-field-label">
                        <?php _e('Fulltime Breeder', 'petslist'); ?>
                        <strong class="rtcl-required">*</strong>
                    </label>
                    <div style="display: flex; gap: 24px; margin-top: 6px; padding: 10px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500; font-size: 14px; color: #1e293b;">
                            <input type="radio" name="fulltime_breeder" value="yes" required> <?php _e('Yes', 'petslist'); ?>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500; font-size: 14px; color: #1e293b;">
                            <input type="radio" name="fulltime_breeder" value="no" checked required> <?php _e('No', 'petslist'); ?>
                        </label>
                    </div>
                </div>

                <div class="rtcl-form-group" style="margin-top: 16px;">
                    <label for="dd-reg-pass" class="rtcl-field-label">
                        <?php _e('Password', 'petslist'); ?>
                        <strong class="rtcl-required">*</strong>
                    </label>
                    <div class="rtcl-user-pass-wrap">
                        <input type="password" id="dd-reg-pass" name="password" class="rtcl-form-control rtcl-password" placeholder="<?php esc_attr_e('Create a strong password', 'petslist'); ?>" required minlength="8" autocomplete="new-password">
                        <span class="rtcl-toggle-pass rtcl-icon-eye-off dd-toggle-pass"></span>
                    </div>
                </div>

                <div class="rtcl-form-group">
                    <label class="rtcl-checkbox-label">
                        <input type="checkbox" name="terms" id="dd-terms" required>
                        <?php printf(
                            __('I agree to the <a href="%s" target="_blank">Terms of Service</a> and <a href="%s" target="_blank">Privacy Policy</a>', 'petslist'),
                            esc_url(get_privacy_policy_url()),
                            esc_url(get_privacy_policy_url())
                        ); ?>
                    </label>
                </div>

                <div class="rtcl-form-group">
                    <button type="submit" class="rtcl-btn btn btn-primary" id="dd-register-submit" style="width: 100%;">
                        <span class="dd-btn__text"><?php _e('Create Free Account', 'petslist'); ?></span>
                        <span class="dd-btn__loader" style="display:none; margin-left: 8px;"><i class="fa-solid fa-spinner fa-spin"></i></span>
                    </button>
                </div>
                
                <p style="margin-top: 10px; font-size: 13px; color: #666; text-align: center;">
                    <?php _e('After registration, choose a subscription plan to list dogs.', 'petslist'); ?>
                </p>
            </form>

            <div style="margin-top: 20px; font-size: 14px; text-align: center;">
                <p><?php printf(
                    __('Already have an account? <a href="%s">Sign in</a>', 'petslist'),
                    esc_url(dd_login_url())
                ); ?></p>
            </div>
        </div>
    </div>
</div>