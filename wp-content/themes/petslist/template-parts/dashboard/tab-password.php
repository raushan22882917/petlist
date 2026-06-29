<?php
/**
 * Dashboard Tab: Change Password
 * @package Petslist Dog Directory
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="dd-tab-password">

    <div class="dd-tab-dogs__header">
        <h2><?php _e( 'Change Password', 'petslist' ); ?></h2>
        <p class="dd-tab-dogs__subtitle"><?php _e('Update your password to keep your account secure.', 'petslist'); ?></p>
    </div>

    <div id="dd-password-message" class="dd-auth-message" style="display:none; margin-bottom: 20px;"></div>

    <div class="dd-dogs-card-panel">

        <form id="dd-password-form" class="dd-password-form" novalidate style="max-width: 540px; margin: 0;">
            <div class="dd-password-form__inner">

                <div class="dd-form-group" style="margin-bottom: 20px;">
                    <label for="dd-current-pass"><?php _e( 'Current Password', 'petslist' ); ?></label>
                    <div class="dd-form-input-wrap" style="position: relative;">
                        <input type="password" id="dd-current-pass" name="current_password" placeholder="••••••••" required autocomplete="current-password" style="width: 100%; padding-right: 40px;">
                        <button type="button" class="dd-toggle-pass" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: #64748b; cursor: pointer; padding: 0;"><i class="fa-solid fa-eye"></i></button>
                    </div>
                </div>

                <div class="dd-form-group" style="margin-bottom: 20px;">
                    <label for="dd-new-pass"><?php _e( 'New Password', 'petslist' ); ?></label>
                    <div class="dd-form-input-wrap" style="position: relative; margin-bottom: 8px;">
                        <input type="password" id="dd-new-pass" name="new_password" placeholder="••••••••" required minlength="8" autocomplete="new-password" style="width: 100%; padding-right: 40px;">
                        <button type="button" class="dd-toggle-pass" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: #64748b; cursor: pointer; padding: 0;"><i class="fa-solid fa-eye"></i></button>
                    </div>
                    <div class="dd-password-strength" id="dd-new-pass-strength">
                        <div class="dd-password-strength__bar"></div>
                        <span class="dd-password-strength__label"></span>
                    </div>
                </div>

                <div class="dd-form-group" style="margin-bottom: 24px;">
                    <label for="dd-confirm-pass"><?php _e( 'Confirm New Password', 'petslist' ); ?></label>
                    <div class="dd-form-input-wrap" style="position: relative;">
                        <input type="password" id="dd-confirm-pass" name="confirm_password" placeholder="••••••••" required autocomplete="new-password" style="width: 100%; padding-right: 40px;">
                        <button type="button" class="dd-toggle-pass" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: #64748b; cursor: pointer; padding: 0;"><i class="fa-solid fa-eye"></i></button>
                    </div>
                </div>

                <div class="dd-password-tips" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 24px;">
                    <h4 style="font-size: 13px; font-weight: 700; color: #1e293b; margin: 0 0 10px 0; text-transform: uppercase; letter-spacing: 0.5px;"><?php _e( 'Password Requirements', 'petslist' ); ?></h4>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                        <li id="dd-req-length" style="color: #ef4444; display: flex; align-items: center; gap: 8px;"><i class="fa-solid fa-circle-xmark"></i> <?php _e( 'At least 8 characters', 'petslist' ); ?></li>
                        <li id="dd-req-upper" style="color: #ef4444; display: flex; align-items: center; gap: 8px;"><i class="fa-solid fa-circle-xmark"></i> <?php _e( 'At least one uppercase letter', 'petslist' ); ?></li>
                        <li id="dd-req-number" style="color: #ef4444; display: flex; align-items: center; gap: 8px;"><i class="fa-solid fa-circle-xmark"></i> <?php _e( 'At least one number', 'petslist' ); ?></li>
                    </ul>
                </div>

                <div style="padding-top: 16px; border-top: 1px solid #edf2f7;">
                    <button type="submit" class="dd-btn dd-btn--primary" id="dd-password-submit" style="min-width: 150px;">
                        <span class="dd-btn__text"><?php _e( 'Update Password', 'petslist' ); ?></span>
                        <span class="dd-btn__loader" style="display:none"><i class="fa-solid fa-spinner fa-spin"></i></span>
                    </button>
                </div>

            </div>
        </form>

    </div>

</div>
