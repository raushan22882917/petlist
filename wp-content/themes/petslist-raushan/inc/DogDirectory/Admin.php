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
                                        <option value="test" <?php selected(get_option('dd_stripe_mode','test'),'test'); ?>>Test</option>
                                        <option value="live" <?php selected(get_option('dd_stripe_mode','test'),'live'); ?>>Live</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Publishable Key', 'petslist'); ?></th>
                                <td><input type="text" name="dd_stripe_publishable_key" value="<?php echo esc_attr(get_option('dd_stripe_publishable_key')); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><?php _e('Secret Key', 'petslist'); ?></th>
                                <td><input type="password" name="dd_stripe_secret_key" value="<?php echo esc_attr(get_option('dd_stripe_secret_key')); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><?php _e('Webhook Secret', 'petslist'); ?></th>
                                <td>
                                    <input type="password" name="dd_stripe_webhook_secret" value="<?php echo esc_attr(get_option('dd_stripe_webhook_secret')); ?>" class="regular-text">
                                    <p class="description"><?php printf(__('Webhook URL: %s', 'petslist'), '<code>' . admin_url('admin-ajax.php?action=dd_stripe_webhook') . '</code>'); ?></p>
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

                    <div class="dd-admin-card">
                        <h2><?php _e('Email Settings', 'petslist'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th><?php _e('From Name', 'petslist'); ?></th>
                                <td><input type="text" name="dd_email_from_name" value="<?php echo esc_attr(get_option('dd_email_from_name', get_bloginfo('name'))); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><?php _e('From Email', 'petslist'); ?></th>
                                <td><input type="email" name="dd_email_from_email" value="<?php echo esc_attr(get_option('dd_email_from_email', get_option('admin_email'))); ?>" class="regular-text"></td>
                            </tr>
                        </table>
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
        $subs = $wpdb->get_results(
            "SELECT s.*, u.display_name, u.user_email, p.name as plan_name
             FROM {$wpdb->prefix}dd_subscriptions s
             LEFT JOIN {$wpdb->prefix}users u ON s.user_id = u.ID
             LEFT JOIN {$wpdb->prefix}dd_plans p ON s.plan_id = p.id
             ORDER BY s.created_at DESC LIMIT 100"
        );
        ?>
        <div class="wrap dd-admin-wrap">
            <h1>👥 <?php _e('Subscribers', 'petslist'); ?> <span class="dd-count">(<?php echo count($subs); ?>)</span></h1>
            <table class="widefat striped dd-admin-table">
                <thead>
                    <tr>
                        <th><?php _e('User', 'petslist'); ?></th>
                        <th><?php _e('Email', 'petslist'); ?></th>
                        <th><?php _e('Plan', 'petslist'); ?></th>
                        <th><?php _e('Status', 'petslist'); ?></th>
                        <th><?php _e('Started', 'petslist'); ?></th>
                        <th><?php _e('Expires', 'petslist'); ?></th>
                        <th><?php _e('Dogs', 'petslist'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $subs as $sub ) :
                        $dogs = dd_get_user_dog_count($sub->user_id);
                        $status_colors = ['active'=>'green','expired'=>'red','cancelled'=>'orange','pending'=>'blue'];
                        $color = $status_colors[$sub->status] ?? 'gray';
                    ?>
                    <tr>
                        <td><?php echo esc_html($sub->display_name); ?></td>
                        <td><?php echo esc_html($sub->user_email); ?></td>
                        <td><?php echo esc_html($sub->plan_name); ?></td>
                        <td><span style="color:<?php echo $color; ?>;font-weight:600"><?php echo ucfirst($sub->status); ?></span></td>
                        <td><?php echo date('M j, Y', strtotime($sub->starts_at)); ?></td>
                        <td><?php echo date('M j, Y', strtotime($sub->expires_at)); ?></td>
                        <td><?php echo $dogs; ?></td>
                    </tr>
                    <?php endforeach; ?>
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
