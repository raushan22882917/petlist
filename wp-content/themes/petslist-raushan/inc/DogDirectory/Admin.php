<?php
/**
 * Dog Directory - Admin Dashboard & Settings
 * @package Petslist Dog Directory
 */

namespace RadiusTheme\Petslist\DogDirectory;

if ( ! defined( 'ABSPATH' ) ) exit;

class Admin {

    protected static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_menus' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_init', [ $this, 'create_default_pages' ] );
    }

    public function add_admin_menus() {
        add_menu_page(
            __('Dog Directory', 'petslist'),
            __('Dog Directory', 'petslist'),
            'manage_options',
            'dd-settings',
            [ $this, 'render_settings_page' ],
            'dashicons-pets',
            25
        );

        add_submenu_page(
            'dd-settings',
            __('Settings', 'petslist'),
            __('Settings', 'petslist'),
            'manage_options',
            'dd-settings',
            [ $this, 'render_settings_page' ]
        );

        add_submenu_page(
            'dd-settings',
            __('Subscription Plans', 'petslist'),
            __('Plans', 'petslist'),
            'manage_options',
            'dd-plans',
            [ $this, 'render_plans_page' ]
        );

        add_submenu_page(
            'dd-settings',
            __('Subscribers', 'petslist'),
            __('Subscribers', 'petslist'),
            'manage_options',
            'dd-subscribers',
            [ $this, 'render_subscribers_page' ]
        );

        add_submenu_page(
            'dd-settings',
            __('Payments', 'petslist'),
            __('Payments', 'petslist'),
            'manage_options',
            'dd-payments',
            [ $this, 'render_payments_page' ]
        );

        add_submenu_page(
            'dd-settings',
            __('Analytics', 'petslist'),
            __('Analytics', 'petslist'),
            'manage_options',
            'dd-analytics',
            [ $this, 'render_analytics_page' ]
        );
    }

    public function register_settings() {
        register_setting('dd_settings_group', 'dd_stripe_publishable_key', 'sanitize_text_field');
        register_setting('dd_settings_group', 'dd_stripe_secret_key', 'sanitize_text_field');
        register_setting('dd_settings_group', 'dd_stripe_webhook_secret', 'sanitize_text_field');
        register_setting('dd_settings_group', 'dd_stripe_mode', 'sanitize_text_field');
        register_setting('dd_settings_group', 'dd_page_login', 'absint');
        register_setting('dd_settings_group', 'dd_page_register', 'absint');
        register_setting('dd_settings_group', 'dd_page_pricing', 'absint');
        register_setting('dd_settings_group', 'dd_page_checkout', 'absint');
        register_setting('dd_settings_group', 'dd_page_dashboard', 'absint');
        register_setting('dd_settings_group', 'dd_page_forgot', 'absint');
        register_setting('dd_settings_group', 'dd_require_approval', 'absint');
        register_setting('dd_settings_group', 'dd_dogs_per_page', 'absint');
        register_setting('dd_settings_group', 'dd_email_from_name', 'sanitize_text_field');
        register_setting('dd_settings_group', 'dd_email_from_email', 'sanitize_email');
        register_setting('dd_settings_group', 'dd_smtp_enable', 'absint');
        register_setting('dd_settings_group', 'dd_smtp_host', 'sanitize_text_field');
        register_setting('dd_settings_group', 'dd_smtp_port', 'absint');
        register_setting('dd_settings_group', 'dd_smtp_encryption', 'sanitize_text_field');
        register_setting('dd_settings_group', 'dd_smtp_auth', 'absint');
        register_setting('dd_settings_group', 'dd_smtp_username', 'sanitize_text_field');
        register_setting('dd_settings_group', 'dd_smtp_password', 'sanitize_text_field');
    }

    public function render_settings_page() {
        ?>
        <div class="wrap dd-admin-wrap">
            <h1 class="dd-admin-title">🐾 <?php _e('Dog Directory Settings', 'petslist'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('dd_settings_group'); ?>
                <div class="dd-admin-grid">

                    <div class="dd-admin-card">
                        <h2><?php _e('Stripe Payment Settings', 'petslist'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th><?php _e('Mode', 'petslist'); ?></th>
                                <td>
                                    <select name="dd_stripe_mode">
                                        <option value="test" <?php selected(dd_stripe_mode(),'test'); ?>>Test</option>
                                        <option value="live" <?php selected(dd_stripe_mode(),'live'); ?>>Live</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Publishable Key', 'petslist'); ?></th>
                                <td><input type="text" name="dd_stripe_publishable_key" value="<?php echo esc_attr(dd_stripe_publishable_key()); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><?php _e('Secret Key', 'petslist'); ?></th>
                                <td><input type="password" name="dd_stripe_secret_key" value="<?php echo esc_attr(dd_stripe_secret_key()); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><?php _e('Webhook Secret', 'petslist'); ?></th>
                                <td>
                                    <input type="password" name="dd_stripe_webhook_secret" value="<?php echo esc_attr(dd_stripe_webhook_secret()); ?>" class="regular-text">
                                    <p class="description"><?php printf(__('Webhook URL: %s', 'petslist'), '<code>' . admin_url('admin-ajax.php?action=dd_stripe_webhook') . '</code>'); ?></p>
                                    <p class="description"><?php _e('Values can also be set via the root <code>.env</code> file (<code>STRIPE_MODE</code>, <code>STRIPE_PUBLISHABLE_KEY</code>, <code>STRIPE_SECRET_KEY</code>, <code>STRIPE_WEBHOOK_SECRET</code>).', 'petslist'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="dd-admin-card">
                        <h2><?php _e('Page Settings', 'petslist'); ?></h2>
                        <?php
                        $pages_settings = [
                            'dd_page_login'     => __('Login Page', 'petslist'),
                            'dd_page_register'  => __('Register Page', 'petslist'),
                            'dd_page_pricing'   => __('Pricing/Plans Page', 'petslist'),
                            'dd_page_checkout'  => __('Checkout Page', 'petslist'),
                            'dd_page_dashboard' => __('Subscriber Dashboard Page', 'petslist'),
                            'dd_page_forgot'    => __('Forgot Password Page', 'petslist'),
                        ];
                        echo '<table class="form-table">';
                        foreach ( $pages_settings as $key => $label ) {
                            $current = get_option($key);
                            echo '<tr><th>' . esc_html($label) . '</th><td>';
                            wp_dropdown_pages(['name' => $key, 'selected' => $current, 'show_option_none' => __('— Select Page —', 'petslist')]);
                            echo '</td></tr>';
                        }
                        echo '</table>';
                        ?>
                    </div>

                    <div class="dd-admin-card">
                        <h2><?php _e('Directory Settings', 'petslist'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th><?php _e('Require Admin Approval', 'petslist'); ?></th>
                                <td><input type="checkbox" name="dd_require_approval" value="1" <?php checked(get_option('dd_require_approval'), 1); ?>></td>
                            </tr>
                            <tr>
                                <th><?php _e('Dogs Per Page', 'petslist'); ?></th>
                                <td><input type="number" name="dd_dogs_per_page" value="<?php echo esc_attr(get_option('dd_dogs_per_page', 12)); ?>" min="1" max="100"></td>
                            </tr>
                        </table>
                    </div>

                    <div class="dd-admin-card" style="grid-column: span 2;">
                        <h2>📧 <?php _e('Email & SMTP Settings', 'petslist'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th><?php _e('From Name', 'petslist'); ?></th>
                                <td><input type="text" name="dd_email_from_name" value="<?php echo esc_attr(get_option('dd_email_from_name', get_bloginfo('name'))); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><?php _e('From Email', 'petslist'); ?></th>
                                <td><input type="email" name="dd_email_from_email" value="<?php echo esc_attr(get_option('dd_email_from_email', get_option('admin_email'))); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><?php _e('Enable SMTP Mailer', 'petslist'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="dd_smtp_enable" value="1" <?php checked(get_option('dd_smtp_enable'), 1); ?>>
                                        <?php _e('Route outbound WordPress emails via custom SMTP server', 'petslist'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('SMTP Host', 'petslist'); ?></th>
                                <td><input type="text" name="dd_smtp_host" value="<?php echo esc_attr(get_option('dd_smtp_host')); ?>" placeholder="e.g. smtp.gmail.com or smtp.mailtrap.io" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><?php _e('SMTP Port', 'petslist'); ?></th>
                                <td><input type="number" name="dd_smtp_port" value="<?php echo esc_attr(get_option('dd_smtp_port', 587)); ?>" placeholder="587" class="small-text"></td>
                            </tr>
                            <tr>
                                <th><?php _e('Encryption', 'petslist'); ?></th>
                                <td>
                                    <select name="dd_smtp_encryption">
                                        <option value="tls" <?php selected(get_option('dd_smtp_encryption', 'tls'), 'tls'); ?>>TLS (Recommended)</option>
                                        <option value="ssl" <?php selected(get_option('dd_smtp_encryption'), 'ssl'); ?>>SSL</option>
                                        <option value="none" <?php selected(get_option('dd_smtp_encryption'), 'none'); ?>>None</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('SMTP Authentication', 'petslist'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="dd_smtp_auth" value="1" <?php checked(get_option('dd_smtp_auth', 1), 1); ?>>
                                        <?php _e('Use Username & Password authentication', 'petslist'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('SMTP Username', 'petslist'); ?></th>
                                <td><input type="text" name="dd_smtp_username" value="<?php echo esc_attr(get_option('dd_smtp_username')); ?>" class="regular-text" autocomplete="off"></td>
                            </tr>
                            <tr>
                                <th><?php _e('SMTP Password', 'petslist'); ?></th>
                                <td><input type="password" name="dd_smtp_password" value="<?php echo esc_attr(get_option('dd_smtp_password')); ?>" class="regular-text" autocomplete="off"></td>
                            </tr>
                        </table>

                        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #e2e8f0;">
                        <h3>🧪 <?php _e('Test SMTP Configuration', 'petslist'); ?></h3>
                        <p class="description"><?php _e('Send a test email to verify your SMTP and email template settings.', 'petslist'); ?></p>
                        <div style="display: flex; gap: 10px; align-items: center; margin-top: 10px;">
                            <input type="email" id="dd-test-email-address" value="<?php echo esc_attr(get_option('admin_email')); ?>" placeholder="test@example.com" class="regular-text">
                            <button type="button" id="dd-send-test-email" class="button button-secondary">
                                <span class="dashicons dashicons-email-alt" style="vertical-align: middle; margin-top: -2px;"></span>
                                <?php _e('Send Test Email', 'petslist'); ?>
                            </button>
                        </div>
                        <div id="dd-smtp-test-result" style="margin-top: 10px; display: none;"></div>
                    </div>

                </div>
                <?php submit_button(__('Save Settings', 'petslist')); ?>
            </form>
        </div>
        <?php
    }

    public function render_plans_page() {
        global $wpdb;
        $plans = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dd_plans ORDER BY price ASC");
        ?>
        <div class="wrap dd-admin-wrap">
            <h1>🏷️ <?php _e('Subscription Plans', 'petslist'); ?></h1>
            <table class="widefat striped dd-admin-table">
                <thead>
                    <tr>
                        <th><?php _e('Name', 'petslist'); ?></th>
                        <th><?php _e('Price', 'petslist'); ?></th>
                        <th><?php _e('Duration (days)', 'petslist'); ?></th>
                        <th><?php _e('Status', 'petslist'); ?></th>
                        <th><?php _e('Subscribers', 'petslist'); ?></th>
                        <th><?php _e('Actions', 'petslist'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $plans as $plan ) :
                        $count = $wpdb->get_var($wpdb->prepare(
                            "SELECT COUNT(*) FROM {$wpdb->prefix}dd_subscriptions WHERE plan_id = %d AND status = 'active'",
                            $plan->id
                        ));
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html($plan->name); ?></strong></td>
                        <td>$<?php echo number_format($plan->price, 2); ?></td>
                        <td><?php echo (int)$plan->duration; ?></td>
                        <td><?php echo $plan->is_active ? '<span style="color:green">Active</span>' : '<span style="color:red">Inactive</span>'; ?></td>
                        <td><?php echo (int)$count; ?></td>
                        <td>
                            <button class="button dd-edit-plan" data-id="<?php echo $plan->id; ?>">Edit</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function render_subscribers_page() {
        global $wpdb;

        // Process WP-Admin direct actions with nonce verification
        $notice = '';
        $notice_type = 'success';

        if ( ! current_user_can('manage_options') ) {
            wp_die( __('Access denied.', 'petslist') );
        }

        // Action: Grant Subscription
        if ( isset($_POST['dd_action']) && $_POST['dd_action'] === 'grant_sub' ) {
            check_admin_referer('dd_admin_sub_nonce');
            $user_id  = absint($_POST['grant_user_id'] ?? 0);
            $plan_id  = absint($_POST['grant_plan_id'] ?? 0);
            $duration = max(1, absint($_POST['grant_duration'] ?? 30));
            $notes    = sanitize_text_field($_POST['grant_notes'] ?? '');

            $res = Subscription::admin_grant_subscription($user_id, $plan_id, $duration, $notes);
            if ( is_wp_error($res) ) {
                $notice = $res->get_error_message();
                $notice_type = 'error';
            } else {
                $notice = $res['message'];
            }
        }

        // Action: Extend Subscription
        if ( isset($_GET['dd_action']) && $_GET['dd_action'] === 'extend' ) {
            check_admin_referer('dd_sub_action_' . absint($_GET['sub_id'] ?? 0));
            $sub_id = absint($_GET['sub_id'] ?? 0);
            $days   = max(1, absint($_GET['days'] ?? 30));
            $res = Subscription::admin_extend_subscription($sub_id, $days);
            if ( is_wp_error($res) ) {
                $notice = $res->get_error_message();
                $notice_type = 'error';
            } else {
                $notice = $res['message'];
            }
        }

        // Action: Cancel Subscription
        if ( isset($_GET['dd_action']) && $_GET['dd_action'] === 'cancel' ) {
            check_admin_referer('dd_sub_action_' . absint($_GET['sub_id'] ?? 0));
            $sub_id = absint($_GET['sub_id'] ?? 0);
            $res = Subscription::admin_cancel_subscription($sub_id);
            if ( is_wp_error($res) ) {
                $notice = $res->get_error_message();
                $notice_type = 'error';
            } else {
                $notice = $res['message'];
            }
        }

        // Action: Delete Subscription
        if ( isset($_GET['dd_action']) && $_GET['dd_action'] === 'delete' ) {
            check_admin_referer('dd_sub_action_' . absint($_GET['sub_id'] ?? 0));
            $sub_id = absint($_GET['sub_id'] ?? 0);
            $res = Subscription::admin_delete_subscription($sub_id);
            if ( is_wp_error($res) ) {
                $notice = $res->get_error_message();
                $notice_type = 'error';
            } else {
                $notice = $res['message'];
            }
        }

        $filter = sanitize_key($_GET['status'] ?? 'all');
        $search = sanitize_text_field($_GET['s'] ?? '');

        $where_clauses = ['1=1'];
        if ( $filter !== 'all' ) {
            $where_clauses[] = $wpdb->prepare("s.status = %s", $filter);
        }
        if ( ! empty($search) ) {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where_clauses[] = $wpdb->prepare("(u.display_name LIKE %s OR u.user_email LIKE %s OR u.user_login LIKE %s OR s.id = %d)", $like, $like, $like, absint($search));
        }
        $where = 'WHERE ' . implode(' AND ', $where_clauses);

        $subs = $wpdb->get_results(
            "SELECT s.*, u.display_name, u.user_email, u.user_login, p.name as plan_name, p.price as plan_price
             FROM {$wpdb->prefix}dd_subscriptions s
             LEFT JOIN {$wpdb->prefix}users u ON s.user_id = u.ID
             LEFT JOIN {$wpdb->prefix}dd_plans p ON s.plan_id = p.id
             $where
             ORDER BY s.created_at DESC LIMIT 150"
        );

        $plans = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dd_plans WHERE is_active = 1 ORDER BY price ASC");
        $users = $wpdb->get_results("SELECT ID, display_name, user_email, user_login FROM {$wpdb->users} ORDER BY display_name ASC LIMIT 200");

        $total_active = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dd_subscriptions WHERE status = 'active'");
        $total_all    = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dd_subscriptions");
        ?>
        <div class="wrap dd-admin-wrap">
            <h1 class="wp-heading-inline">👥 <?php _e('Subscription Management', 'petslist'); ?></h1>
            <button type="button" class="page-title-action" onclick="document.getElementById('dd-grant-box').style.display = (document.getElementById('dd-grant-box').style.display === 'none' ? 'block' : 'none');">
                + <?php _e('Grant Subscription', 'petslist'); ?>
            </button>
            <hr class="wp-header-end">

            <?php if ( ! empty($notice) ) : ?>
            <div class="notice notice-<?php echo esc_attr($notice_type); ?> is-dismissible" style="margin-top:15px;">
                <p><strong><?php echo esc_html($notice); ?></strong></p>
            </div>
            <?php endif; ?>

            <!-- Grant Subscription Form Box -->
            <div id="dd-grant-box" style="display:none;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #02c5bd;padding:20px;margin:15px 0;box-shadow:0 1px 3px rgba(0,0,0,0.05);border-radius:4px;">
                <h3 style="margin-top:0;"><?php _e('Grant New Subscription (Manual Override)', 'petslist'); ?></h3>
                <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=dd-subscribers')); ?>">
                    <?php wp_nonce_field('dd_admin_sub_nonce'); ?>
                    <input type="hidden" name="dd_action" value="grant_sub">
                    <table class="form-table" style="margin-bottom:10px;">
                        <tr>
                            <th scope="row"><label for="grant_user_id"><?php _e('User', 'petslist'); ?></label></th>
                            <td>
                                <select name="grant_user_id" id="grant_user_id" required style="max-width:350px;width:100%;">
                                    <option value=""><?php _e('-- Select User --', 'petslist'); ?></option>
                                    <?php foreach ($users as $u): ?>
                                    <option value="<?php echo $u->ID; ?>"><?php echo esc_html($u->display_name ?: $u->user_login); ?> (<?php echo esc_html($u->user_email); ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="grant_plan_id"><?php _e('Plan', 'petslist'); ?></label></th>
                            <td>
                                <select name="grant_plan_id" id="grant_plan_id" required style="max-width:350px;width:100%;">
                                    <?php foreach ($plans as $p): ?>
                                    <option value="<?php echo $p->id; ?>"><?php echo esc_html($p->name); ?> ($<?php echo number_format($p->price, 2); ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="grant_duration"><?php _e('Duration (Days)', 'petslist'); ?></label></th>
                            <td>
                                <input type="number" name="grant_duration" id="grant_duration" value="30" min="1" max="3650" style="width:100px;">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="grant_notes"><?php _e('Admin Note', 'petslist'); ?></label></th>
                            <td>
                                <input type="text" name="grant_notes" id="grant_notes" placeholder="<?php esc_attr_e('e.g. VIP Member, Cash, Promo', 'petslist'); ?>" style="max-width:350px;width:100%;">
                            </td>
                        </tr>
                    </table>
                    <p class="submit" style="margin:0;">
                        <input type="submit" class="button button-primary" value="<?php esc_attr_e('Grant & Activate Subscription', 'petslist'); ?>">
                        <button type="button" class="button" onclick="document.getElementById('dd-grant-box').style.display='none';"><?php _e('Cancel', 'petslist'); ?></button>
                    </p>
                </form>
            </div>

            <!-- Filters -->
            <ul class="subsubsub">
                <li><a href="<?php echo esc_url(admin_url('admin.php?page=dd-subscribers')); ?>" class="<?php echo $filter==='all'?'current':''; ?>"><?php _e('All', 'petslist'); ?> <span class="count">(<?php echo $total_all; ?>)</span></a> |</li>
                <li><a href="<?php echo esc_url(admin_url('admin.php?page=dd-subscribers&status=active')); ?>" class="<?php echo $filter==='active'?'current':''; ?>"><?php _e('Active', 'petslist'); ?> <span class="count">(<?php echo $total_active; ?>)</span></a> |</li>
                <li><a href="<?php echo esc_url(admin_url('admin.php?page=dd-subscribers&status=expired')); ?>" class="<?php echo $filter==='expired'?'current':''; ?>"><?php _e('Expired', 'petslist'); ?></a> |</li>
                <li><a href="<?php echo esc_url(admin_url('admin.php?page=dd-subscribers&status=cancelled')); ?>" class="<?php echo $filter==='cancelled'?'current':''; ?>"><?php _e('Cancelled', 'petslist'); ?></a></li>
            </ul>

            <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="float:right;margin-bottom:10px;">
                <input type="hidden" name="page" value="dd-subscribers">
                <?php if ($filter !== 'all'): ?><input type="hidden" name="status" value="<?php echo esc_attr($filter); ?>"><?php endif; ?>
                <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search subscribers...', 'petslist'); ?>">
                <input type="submit" class="button" value="<?php esc_attr_e('Search', 'petslist'); ?>">
            </form>

            <table class="widefat striped dd-admin-table" style="clear:both;">
                <thead>
                    <tr>
                        <th><?php _e('ID / User', 'petslist'); ?></th>
                        <th><?php _e('Email', 'petslist'); ?></th>
                        <th><?php _e('Plan', 'petslist'); ?></th>
                        <th><?php _e('Status', 'petslist'); ?></th>
                        <th><?php _e('Started', 'petslist'); ?></th>
                        <th><?php _e('Expires', 'petslist'); ?></th>
                        <th><?php _e('Dogs', 'petslist'); ?></th>
                        <th style="text-align:right;"><?php _e('Actions', 'petslist'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $subs as $sub ) :
                        $dogs = dd_get_user_dog_count($sub->user_id);
                        $status_colors = ['active'=>'#16a34a','expired'=>'#dc2626','cancelled'=>'#d97706','pending'=>'#2563eb'];
                        $color = $status_colors[$sub->status] ?? '#64748b';
                        $nonce = wp_create_nonce('dd_sub_action_' . $sub->id);
                        $extend_url = wp_nonce_url(admin_url('admin.php?page=dd-subscribers&dd_action=extend&sub_id='.$sub->id.'&days=30'), 'dd_sub_action_'.$sub->id);
                        $cancel_url = wp_nonce_url(admin_url('admin.php?page=dd-subscribers&dd_action=cancel&sub_id='.$sub->id), 'dd_sub_action_'.$sub->id);
                        $delete_url = wp_nonce_url(admin_url('admin.php?page=dd-subscribers&dd_action=delete&sub_id='.$sub->id), 'dd_sub_action_'.$sub->id);
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($sub->display_name ?: $sub->user_login); ?></strong>
                            <small style="color:#94a3b8;">(#<?php echo $sub->id; ?>)</small>
                        </td>
                        <td><?php echo esc_html($sub->user_email); ?></td>
                        <td><?php echo esc_html($sub->plan_name ?: 'Custom'); ?></td>
                        <td><span style="background:<?php echo $color; ?>15;color:<?php echo $color; ?>;padding:2px 8px;border-radius:4px;font-weight:700;font-size:11px;"><?php echo strtoupper($sub->status); ?></span></td>
                        <td><?php echo date('M j, Y', strtotime($sub->starts_at)); ?></td>
                        <td>
                            <strong><?php echo date('M j, Y', strtotime($sub->expires_at)); ?></strong>
                            <?php if ($sub->status === 'active' && strtotime($sub->expires_at) < time()): ?>
                                <span style="color:#dc2626;font-size:10px;display:block;"><?php _e('Expired', 'petslist'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(admin_url('edit.php?post_type=dd_dog&author='.$sub->user_id)); ?>">
                                <?php echo $dogs; ?>
                            </a>
                        </td>
                        <td style="text-align:right;">
                            <a href="<?php echo esc_url($extend_url); ?>" class="button button-small" title="<?php esc_attr_e('Extend by 30 days', 'petslist'); ?>">+30d</a>
                            <?php if ($sub->status === 'active'): ?>
                                <a href="<?php echo esc_url($cancel_url); ?>" class="button button-small" onclick="return confirm('<?php echo esc_js(__('Cancel this subscription?', 'petslist')); ?>');"><?php _e('Cancel', 'petslist'); ?></a>
                            <?php else: ?>
                                <a href="<?php echo esc_url($extend_url); ?>" class="button button-small button-primary"><?php _e('Reactivate', 'petslist'); ?></a>
                            <?php endif; ?>
                            <a href="<?php echo esc_url($delete_url); ?>" class="button button-small" style="color:#dc2626;" onclick="return confirm('<?php echo esc_js(__('Delete this subscription record?', 'petslist')); ?>');">&times;</a>
                            <a href="<?php echo esc_url(admin_url('user-edit.php?user_id='.$sub->user_id)); ?>" class="button button-small" title="<?php esc_attr_e('Edit User', 'petslist'); ?>">👤</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if ( empty($subs) ) : ?>
                    <tr><td colspan="8" style="text-align:center;padding:30px;color:#94a3b8;"><?php _e('No subscriptions found.', 'petslist'); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function render_payments_page() {
        global $wpdb;
        $payments = $wpdb->get_results(
            "SELECT py.*, u.display_name, u.user_email, p.name as plan_name
             FROM {$wpdb->prefix}dd_payments py
             LEFT JOIN {$wpdb->prefix}users u ON py.user_id = u.ID
             LEFT JOIN {$wpdb->prefix}dd_subscriptions s ON py.subscription_id = s.id
             LEFT JOIN {$wpdb->prefix}dd_plans p ON s.plan_id = p.id
             ORDER BY py.created_at DESC LIMIT 200"
        );
        $total = array_sum(array_column($payments, 'amount'));
        ?>
        <div class="wrap dd-admin-wrap">
            <h1>💳 <?php _e('Payment History', 'petslist'); ?></h1>
            <div class="dd-admin-stat-bar">
                <span><?php printf(__('Total Revenue: <strong>$%s</strong>', 'petslist'), number_format($total, 2)); ?></span>
                <span><?php printf(__('Total Payments: <strong>%d</strong>', 'petslist'), count($payments)); ?></span>
            </div>
            <table class="widefat striped dd-admin-table">
                <thead>
                    <tr>
                        <th><?php _e('User', 'petslist'); ?></th>
                        <th><?php _e('Plan', 'petslist'); ?></th>
                        <th><?php _e('Amount', 'petslist'); ?></th>
                        <th><?php _e('Status', 'petslist'); ?></th>
                        <th><?php _e('Transaction ID', 'petslist'); ?></th>
                        <th><?php _e('Date', 'petslist'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $payments as $pay ) : ?>
                    <tr>
                        <td><?php echo esc_html($pay->display_name); ?> <small><?php echo esc_html($pay->user_email); ?></small></td>
                        <td><?php echo esc_html($pay->plan_name); ?></td>
                        <td><strong>$<?php echo number_format($pay->amount, 2); ?></strong></td>
                        <td><?php echo esc_html(ucfirst($pay->status)); ?></td>
                        <td><small><?php echo esc_html($pay->transaction_id); ?></small></td>
                        <td><?php echo date('M j, Y g:i a', strtotime($pay->created_at)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function render_analytics_page() {
        global $wpdb;
        $total_dogs  = wp_count_posts('dd_dog');
        $total_subs  = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dd_subscriptions WHERE status='active'");
        $total_rev   = $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}dd_payments WHERE status='completed'") ?: 0;
        $new_30d     = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dd_subscriptions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $by_plan     = $wpdb->get_results("SELECT p.name, COUNT(s.id) as total FROM {$wpdb->prefix}dd_subscriptions s LEFT JOIN {$wpdb->prefix}dd_plans p ON s.plan_id=p.id WHERE s.status='active' GROUP BY s.plan_id");
        ?>
        <div class="wrap dd-admin-wrap">
            <h1>📊 <?php _e('Analytics', 'petslist'); ?></h1>
            <div class="dd-stats-grid">
                <div class="dd-stat-card">
                    <div class="dd-stat-icon">🐕</div>
                    <div class="dd-stat-value"><?php echo number_format($total_dogs->publish); ?></div>
                    <div class="dd-stat-label"><?php _e('Published Dogs', 'petslist'); ?></div>
                </div>
                <div class="dd-stat-card">
                    <div class="dd-stat-icon">⏳</div>
                    <div class="dd-stat-value"><?php echo number_format($total_dogs->pending); ?></div>
                    <div class="dd-stat-label"><?php _e('Pending Approval', 'petslist'); ?></div>
                </div>
                <div class="dd-stat-card">
                    <div class="dd-stat-icon">✅</div>
                    <div class="dd-stat-value"><?php echo number_format($total_subs); ?></div>
                    <div class="dd-stat-label"><?php _e('Active Subscribers', 'petslist'); ?></div>
                </div>
                <div class="dd-stat-card">
                    <div class="dd-stat-icon">📈</div>
                    <div class="dd-stat-value"><?php echo number_format($new_30d); ?></div>
                    <div class="dd-stat-label"><?php _e('New Subscribers (30d)', 'petslist'); ?></div>
                </div>
                <div class="dd-stat-card dd-stat-card--revenue">
                    <div class="dd-stat-icon">💰</div>
                    <div class="dd-stat-value">$<?php echo number_format($total_rev, 2); ?></div>
                    <div class="dd-stat-label"><?php _e('Total Revenue', 'petslist'); ?></div>
                </div>
            </div>
            <h2><?php _e('Subscriptions by Plan', 'petslist'); ?></h2>
            <table class="widefat dd-admin-table" style="max-width:400px">
                <thead><tr><th><?php _e('Plan', 'petslist'); ?></th><th><?php _e('Active Subscribers', 'petslist'); ?></th></tr></thead>
                <tbody>
                    <?php foreach ($by_plan as $row) : ?>
                    <tr><td><?php echo esc_html($row->name); ?></td><td><?php echo (int)$row->total; ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Auto-create required pages on first activation
     */
    public function create_default_pages() {
        if ( get_option('dd_pages_created') ) return;

        $pages = [
            'dd_page_login'     => ['Dog Login',           '[dd_login]'],
            'dd_page_register'  => ['Dog Register',        '[dd_register]'],
            'dd_page_pricing'   => ['Dog Directory Plans', '[dd_pricing]'],
            'dd_page_checkout'  => ['Dog Checkout',        '[dd_checkout]'],
            'dd_page_dashboard' => ['Dog Dashboard',       '[dd_dashboard]'],
            'dd_page_forgot'    => ['Dog Forgot Password', '[dd_forgot]'],
        ];

        foreach ( $pages as $option => $data ) {
            if ( ! get_option($option) ) {
                $page_id = wp_insert_post([
                    'post_title'   => $data[0],
                    'post_content' => $data[1],
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                ]);
                if ( $page_id && ! is_wp_error($page_id) ) {
                    update_option($option, $page_id);
                }
            }
        }

        update_option('dd_pages_created', 1);
        flush_rewrite_rules();
    }
}
