<?php
/**
 * Dashboard Tab: Profile Settings
 * @package Petslist Dog Directory
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$user    = wp_get_current_user();
$phone   = get_user_meta( $user->ID, 'dd_phone', true );
$website = $user->user_url;
?>

<div class="dd-tab-profile">

    <div class="dd-tab-dogs__header">
        <h2><?php _e( 'Profile Settings', 'petslist' ); ?></h2>
        <p class="dd-tab-dogs__subtitle"><?php _e('Manage your display name, contact phone, website, and public biography.', 'petslist'); ?></p>
    </div>

    <div id="dd-profile-message" class="dd-auth-message" style="display:none; margin-bottom: 20px;"></div>

    <div class="dd-dogs-card-panel">

        <div class="dd-profile-layout">

            <!-- Avatar -->
            <div class="dd-profile-avatar-section">
                <div class="dd-profile-avatar" style="position: relative; cursor: pointer;" id="dd-profile-avatar-upload-trigger">
                    <?php
                    $pp_id = absint( get_user_meta( $user->ID, '_rtcl_pp_id', true ) );
                    $avatar_url = $pp_id ? wp_get_attachment_image_url( $pp_id, 'thumbnail' ) : get_avatar_url( $user->ID );
                    ?>
                    <img src="<?php echo esc_url($avatar_url); ?>" class="dd-profile-avatar__img" id="dd-profile-avatar-preview" width="100" height="100">
                    <div class="dd-profile-avatar__overlay">
                        <i class="fa-solid fa-camera"></i>
                        <span><?php _e('Upload', 'petslist'); ?></span>
                    </div>
                </div>
                <div class="dd-profile-avatar__info">
                    <strong><?php echo esc_html( $user->display_name ); ?></strong>
                    <span><?php echo esc_html( $user->user_email ); ?></span>
                    <small><?php printf( __( 'Member since %s', 'petslist' ), date('M Y', strtotime($user->user_registered)) ); ?></small>
                    <p style="font-size: 11px; color: #64748b; margin: 6px 0 0 0;"><?php _e('Click image to upload custom photo', 'petslist'); ?></p>
                </div>
            </div>

            <!-- Form -->
            <form id="dd-profile-form" class="dd-profile-form">
                <input type="hidden" id="dd-profile-avatar-id" name="avatar_id" value="<?php echo $pp_id; ?>">
                <div class="dd-dog-form__grid">

                    <div class="dd-form-group">
                        <label for="dd-profile-name"><?php _e( 'Display Name', 'petslist' ); ?></label>
                        <input type="text" id="dd-profile-name" name="display_name" value="<?php echo esc_attr( $user->display_name ); ?>" required>
                    </div>

                    <div class="dd-form-group">
                        <label for="dd-profile-email"><?php _e( 'Email Address', 'petslist' ); ?></label>
                        <input type="email" id="dd-profile-email" value="<?php echo esc_attr( $user->user_email ); ?>" disabled style="background: #f1f5f9; cursor: not-allowed;">
                        <small class="dd-form-note" style="color: #64748b; font-size: 11px; margin-top: 4px; display: block;"><?php _e( 'Email cannot be changed from here.', 'petslist' ); ?></small>
                    </div>

                    <div class="dd-form-group">
                        <label for="dd-profile-phone"><?php _e( 'Phone Number', 'petslist' ); ?></label>
                        <input type="tel" id="dd-profile-phone" name="phone" value="<?php echo esc_attr( $phone ); ?>" placeholder="+1 555 000 0000">
                    </div>

                    <div class="dd-form-group">
                        <label for="dd-profile-website"><?php _e( 'Website / Kennel URL', 'petslist' ); ?></label>
                        <input type="url" id="dd-profile-website" name="website" value="<?php echo esc_url( $website ); ?>" placeholder="https://my-kennel.com">
                    </div>

                    <div class="dd-form-group dd-form-group--full">
                        <label for="dd-profile-bio"><?php _e( 'Bio / About', 'petslist' ); ?></label>
                        <textarea id="dd-profile-bio" name="bio" rows="4" placeholder="<?php esc_attr_e( 'Tell us about yourself and your dogs...', 'petslist' ); ?>"><?php echo esc_textarea( $user->description ); ?></textarea>
                    </div>

                </div>

                <div class="dd-profile-form__submit" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #edf2f7; text-align: right;">
                    <button type="submit" class="dd-btn dd-btn--primary" id="dd-profile-submit" style="min-width: 140px;">
                        <span class="dd-btn__text"><?php _e( 'Save Profile', 'petslist' ); ?></span>
                        <span class="dd-btn__loader" style="display:none"><i class="fa-solid fa-spinner fa-spin"></i></span>
                    </button>
                </div>
            </form>

        </div>

    </div>

</div>
