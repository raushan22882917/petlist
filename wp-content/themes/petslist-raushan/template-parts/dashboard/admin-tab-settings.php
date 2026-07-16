<?php
/**
 * Admin Dashboard — Settings Tab (front-end version)
 */
if ( ! defined('ABSPATH') ) exit;

$saved = false;
if ( isset($_POST['dd_admin_settings_nonce']) && wp_verify_nonce($_POST['dd_admin_settings_nonce'], 'dd_admin_settings') ) {
    $fields = [
        'dd_paypal_client_id' => 'sanitize_text_field',
        'dd_paypal_secret'    => 'sanitize_text_field',
        'dd_paypal_mode'      => 'sanitize_text_field',
        'dd_require_approval' => 'absint',
        'dd_dogs_per_page'    => 'absint',
        'dd_email_from_name'  => 'sanitize_text_field',
        'dd_email_from_email' => 'sanitize_email',
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

        <button type="submit" class="ddu-btn-primary" style="min-width:180px"><?php _e('Save All Settings','petslist'); ?></button>
    </form>
</div>
