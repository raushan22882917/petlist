<?php
/**
 * Admin Dashboard — Settings Tab (front-end version)
 */
if ( ! defined('ABSPATH') ) exit;

$saved = false;
if ( isset($_POST['dd_admin_settings_nonce']) && wp_verify_nonce($_POST['dd_admin_settings_nonce'], 'dd_admin_settings') ) {
    $fields = [
        'dd_stripe_publishable_key' => 'sanitize_text_field',
        'dd_stripe_secret_key'      => 'sanitize_text_field',
        'dd_stripe_webhook_secret' => 'sanitize_text_field',
        'dd_stripe_mode'            => 'sanitize_text_field',
        'dd_paypal_client_id'       => 'sanitize_text_field',
        'dd_paypal_secret'          => 'sanitize_text_field',
        'dd_paypal_mode'            => 'sanitize_text_field',
        'dd_require_approval'       => 'absint',
        'dd_dogs_per_page'          => 'absint',
        'dd_email_from_name'        => 'sanitize_text_field',
        'dd_email_from_email'       => 'sanitize_email',
        'dd_smtp_enable'            => 'absint',
        'dd_smtp_host'              => 'sanitize_text_field',
        'dd_smtp_port'              => 'absint',
        'dd_smtp_encryption'        => 'sanitize_text_field',
        'dd_smtp_auth'              => 'absint',
        'dd_smtp_username'          => 'sanitize_text_field',
        'dd_smtp_password'          => 'sanitize_text_field',
    ];
    foreach ($fields as $key => $sanitize) {
        $val = isset($_POST[$key]) ? call_user_func($sanitize, $_POST[$key]) : '';
        update_option($key, $val);
    }
    $saved = true;
}

// Page assignments — also handle save
if ( isset($_POST['dd_admin_settings_nonce']) && wp_verify_nonce($_POST['dd_admin_settings_nonce'], 'dd_admin_settings') ) {
    $page_opts = ['dd_page_login','dd_page_register','dd_page_pricing','dd_page_checkout','dd_page_dashboard','dd_page_forgot'];
    foreach ($page_opts as $opt) {
        if ( isset($_POST[$opt]) ) update_option($opt, absint($_POST[$opt]));
    }
}
?>

<div class="dda-settings">
    <?php if ($saved) : ?>
    <div class="dd-notice dd-notice--success" style="margin-bottom:20px">✅ <?php _e('Settings saved successfully.','petslist'); ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <?php wp_nonce_field('dd_admin_settings','dd_admin_settings_nonce'); ?>

        <!-- Stripe -->
        <div class="ddu-panel" style="margin-bottom:20px">
            <div class="ddu-panel__head">
                <h3 class="ddu-panel__title">💳 <?php _e('Stripe Payment Settings','petslist'); ?></h3>
            </div>
            <div class="dda-settings-grid">
                <div class="dd-form-group">
                    <label><?php _e('Mode','petslist'); ?></label>
                    <select name="dd_stripe_mode">
                        <option value="test" <?php selected(get_option('dd_stripe_mode','test'),'test'); ?>>🧪 Test</option>
                        <option value="live" <?php selected(get_option('dd_stripe_mode','test'),'live'); ?>>🟢 Live</option>
                    </select>
                </div>
                <div class="dd-form-group">
                    <label><?php _e('Publishable / API Key','petslist'); ?></label>
                    <input type="text" name="dd_stripe_publishable_key" value="<?php echo esc_attr(get_option('dd_stripe_publishable_key')); ?>" placeholder="pk_test_...">
                </div>
                <div class="dd-form-group">
                    <label><?php _e('Secret Key','petslist'); ?></label>
                    <input type="password" name="dd_stripe_secret_key" value="<?php echo esc_attr(get_option('dd_stripe_secret_key')); ?>" placeholder="sk_test_...">
                </div>
                <div class="dd-form-group">
                    <label><?php _e('Webhook Secret (Optional)','petslist'); ?></label>
                    <input type="password" name="dd_stripe_webhook_secret" value="<?php echo esc_attr(get_option('dd_stripe_webhook_secret')); ?>" placeholder="whsec_...">
                </div>
            </div>
        </div>

        <!-- PayPal -->
        <div class="ddu-panel" style="margin-bottom:20px">
            <div class="ddu-panel__head">
                <h3 class="ddu-panel__title">💳 <?php _e('PayPal Payment Settings','petslist'); ?></h3>
            </div>
            <div class="dda-settings-grid">
                <div class="dd-form-group">
                    <label><?php _e('Mode','petslist'); ?></label>
                    <select name="dd_paypal_mode">
                        <option value="sandbox" <?php selected(get_option('dd_paypal_mode','sandbox'),'sandbox'); ?>>🧪 Sandbox</option>
                        <option value="live" <?php selected(get_option('dd_paypal_mode','sandbox'),'live'); ?>>🟢 Live</option>
                    </select>
                </div>
                <div class="dd-form-group">
                    <label><?php _e('Client ID','petslist'); ?></label>
                    <input type="text" name="dd_paypal_client_id" value="<?php echo esc_attr(get_option('dd_paypal_client_id')); ?>" placeholder="Client ID...">
                </div>
                <div class="dd-form-group">
                    <label><?php _e('Secret Key','petslist'); ?></label>
                    <input type="password" name="dd_paypal_secret" value="<?php echo esc_attr(get_option('dd_paypal_secret')); ?>" placeholder="Secret Key...">
                </div>
            </div>
        </div>

        <!-- Pages -->
        <div class="ddu-panel" style="margin-bottom:20px">
            <div class="ddu-panel__head">
                <h3 class="ddu-panel__title">📄 <?php _e('Page Assignments','petslist'); ?></h3>
            </div>
            <div class="dda-settings-grid">
                <?php
                $page_labels = [
                    'dd_page_login'     => __('Login Page','petslist'),
                    'dd_page_register'  => __('Register Page','petslist'),
                    'dd_page_pricing'   => __('Pricing Page','petslist'),
                    'dd_page_checkout'  => __('Checkout Page','petslist'),
                    'dd_page_dashboard' => __('Dashboard Page','petslist'),
                    'dd_page_forgot'    => __('Forgot Password Page','petslist'),
                ];
                foreach ($page_labels as $opt => $lbl) :
                    $current = get_option($opt);
                ?>
                <div class="dd-form-group">
                    <label><?php echo esc_html($lbl); ?></label>
                    <?php wp_dropdown_pages(['name'=>$opt,'selected'=>$current,'show_option_none'=>__('— Select —','petslist')]); ?>
                    <?php if ($current) : ?>
                    <small><a href="<?php echo esc_url(get_permalink($current)); ?>" target="_blank"><?php _e('View page →','petslist'); ?></a></small>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- General -->
        <div class="ddu-panel" style="margin-bottom:20px">
            <div class="ddu-panel__head">
                <h3 class="ddu-panel__title">⚙️ <?php _e('General Settings','petslist'); ?></h3>
            </div>
            <div class="dda-settings-grid">
                <div class="dd-form-group">
                    <label><?php _e('Require Admin Approval for Dogs','petslist'); ?></label>
                    <select name="dd_require_approval">
                        <option value="1" <?php selected(get_option('dd_require_approval'),1); ?>><?php _e('Yes — review before publishing','petslist'); ?></option>
                        <option value="0" <?php selected(get_option('dd_require_approval'),0); ?>><?php _e('No — publish immediately','petslist'); ?></option>
                    </select>
                </div>
                <div class="dd-form-group">
                    <label><?php _e('Dogs Per Page (Directory)','petslist'); ?></label>
                    <input type="number" name="dd_dogs_per_page" value="<?php echo esc_attr(get_option('dd_dogs_per_page',12)); ?>" min="4" max="100">
                </div>
                <div class="dd-form-group">
                    <label><?php _e('Email From Name','petslist'); ?></label>
                    <input type="text" name="dd_email_from_name" value="<?php echo esc_attr(get_option('dd_email_from_name', get_bloginfo('name'))); ?>">
                </div>
                <div class="dd-form-group">
                    <label><?php _e('Email From Address','petslist'); ?></label>
                    <input type="email" name="dd_email_from_email" value="<?php echo esc_attr(get_option('dd_email_from_email', get_option('admin_email'))); ?>">
                </div>
            </div>
        </div>

        <!-- SMTP Settings -->
        <div class="ddu-panel" style="margin-bottom:20px">
            <div class="ddu-panel__head">
                <h3 class="ddu-panel__title">📧 <?php _e('SMTP Email Settings','petslist'); ?></h3>
            </div>
            <div class="dda-settings-grid">
                <div class="dd-form-group">
                    <label><?php _e('Enable SMTP Mailer','petslist'); ?></label>
                    <select name="dd_smtp_enable">
                        <option value="1" <?php selected(get_option('dd_smtp_enable'),1); ?>><?php _e('Enabled','petslist'); ?></option>
                        <option value="0" <?php selected(get_option('dd_smtp_enable'),0); ?>><?php _e('Disabled (PHP Mail)','petslist'); ?></option>
                    </select>
                </div>
                <div class="dd-form-group">
                    <label><?php _e('SMTP Host','petslist'); ?></label>
                    <input type="text" name="dd_smtp_host" value="<?php echo esc_attr(get_option('dd_smtp_host', 'mail.studs4you.com')); ?>" placeholder="mail.studs4you.com">
                </div>
                <div class="dd-form-group">
                    <label><?php _e('SMTP Port','petslist'); ?></label>
                    <input type="number" name="dd_smtp_port" value="<?php echo esc_attr(get_option('dd_smtp_port', 465)); ?>" placeholder="465">
                </div>
                <div class="dd-form-group">
                    <label><?php _e('Encryption','petslist'); ?></label>
                    <select name="dd_smtp_encryption">
                        <option value="ssl" <?php selected(get_option('dd_smtp_encryption', 'ssl'), 'ssl'); ?>>SSL (Port 465)</option>
                        <option value="tls" <?php selected(get_option('dd_smtp_encryption'), 'tls'); ?>>TLS (Port 587)</option>
                        <option value="none" <?php selected(get_option('dd_smtp_encryption'), 'none'); ?>>None</option>
                    </select>
                </div>
                <div class="dd-form-group">
                    <label><?php _e('SMTP Authentication','petslist'); ?></label>
                    <select name="dd_smtp_auth">
                        <option value="1" <?php selected(get_option('dd_smtp_auth', 1), 1); ?>><?php _e('Yes (Required)','petslist'); ?></option>
                        <option value="0" <?php selected(get_option('dd_smtp_auth'), 0); ?>><?php _e('No','petslist'); ?></option>
                    </select>
                </div>
                <div class="dd-form-group">
                    <label><?php _e('SMTP Username','petslist'); ?></label>
                    <input type="text" name="dd_smtp_username" value="<?php echo esc_attr(get_option('dd_smtp_username', 'noreply@studs4you.com')); ?>" placeholder="noreply@studs4you.com" autocomplete="off">
                </div>
                <div class="dd-form-group">
                    <label><?php _e('SMTP Password','petslist'); ?></label>
                    <input type="password" name="dd_smtp_password" value="<?php echo esc_attr(get_option('dd_smtp_password')); ?>" placeholder="••••••••" autocomplete="off">
                </div>
            </div>
        </div>

        <button type="submit" class="ddu-btn-primary" style="min-width:180px"><?php _e('Save All Settings','petslist'); ?></button>
    </form>
</div>
