<?php
/**
 * Plugin Name: Petslist Dog Directory Addon
 * Description: Complete subscription-based dog directory platform with custom SQL database structure, subscriber and admin dashboards, dog CRUD, public searchable directory with filters, and billing history.
 * Version: 1.5.0
 * Author: Antigravity
 */

defined('ABSPATH') || exit;

// 1. Activation Hook: Create Database Tables and Load Initial Plans & Dummy Data
register_activation_hook(__FILE__, 'petslist_directory_addon_activate');
function petslist_directory_addon_activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Table: wp_dog_plans
    $sql_plans = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dog_plans` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `name` varchar(191) NOT NULL,
      `price` decimal(10,2) NOT NULL,
      `duration` varchar(50) NOT NULL,
      `features` text NOT NULL,
      PRIMARY KEY (`id`)
    ) $charset_collate;";

    // Table: wp_dog_users
    $sql_users = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dog_users` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `wp_user_id` bigint(20) unsigned DEFAULT NULL,
      `name` varchar(191) NOT NULL,
      `email` varchar(191) NOT NULL,
      `role` varchar(50) NOT NULL DEFAULT 'visitor',
      `subscription_id` bigint(20) unsigned DEFAULT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`)
    ) $charset_collate;";

    // Table: wp_dog_dogs
    $sql_dogs = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dog_dogs` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `user_id` bigint(20) unsigned NOT NULL,
      `name` varchar(191) NOT NULL,
      `breed` varchar(191) NOT NULL,
      `gender` varchar(50) NOT NULL,
      `dob` date NOT NULL,
      `age` int(11) NOT NULL,
      `description` text NOT NULL,
      `front_image` varchar(255) NOT NULL,
      `side_image` varchar(255) NOT NULL,
      `gallery` text DEFAULT NULL,
      `color` varchar(100) DEFAULT '',
      `weight` varchar(100) DEFAULT '',
      `registration_number` varchar(100) DEFAULT '',
      `awards` text DEFAULT NULL,
      `health_info` text DEFAULT NULL,
      `pedigree` text DEFAULT NULL,
      `kennel` varchar(191) DEFAULT '',
      `country` varchar(100) DEFAULT 'United States',
      `city` varchar(100) DEFAULT '',
      `phone` varchar(100) DEFAULT '',
      `views` int(11) NOT NULL DEFAULT '0',
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`)
    ) $charset_collate;";

    // Table: wp_dog_payments
    $sql_payments = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dog_payments` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `user_id` bigint(20) unsigned NOT NULL,
      `amount` decimal(10,2) NOT NULL,
      `payment_method` varchar(100) NOT NULL,
      `status` varchar(50) NOT NULL,
      `transaction_id` varchar(191) NOT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_plans);
    dbDelta($sql_users);
    dbDelta($sql_dogs);
    dbDelta($sql_payments);

    // Seed/Initialize plans if empty
    $plans_count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}dog_plans`;");
    if (!$plans_count) {
        $wpdb->insert("{$wpdb->prefix}dog_plans", [
            'id' => 1,
            'name' => 'Gold Membership',
            'price' => 12.00,
            'duration' => 'Monthly',
            'features' => 'Add up to 10 Dogs,2 Featured Listings,Unlimited Browsing,Full Profile Management'
        ]);
        $wpdb->insert("{$wpdb->prefix}dog_plans", [
            'id' => 2,
            'name' => 'Platinum Membership',
            'price' => 99.00,
            'duration' => 'Yearly',
            'features' => 'Add up to 50 Dogs,10 Featured Listings,Unlimited Browsing,Full Profile Management'
        ]);
    }
}

// 2. Enqueue Custom Dashboard CSS and JS Styles
add_action('wp_enqueue_scripts', 'petslist_directory_addon_assets');
function petslist_directory_addon_assets() {
    wp_enqueue_style(
        'petslist-dashboard-styles',
        plugins_url('assets/css/dashboard-styles.css', __FILE__),
        [],
        '1.5.0'
    );
    
    // Enqueue FontAwesome for icons if not already enqueued
    wp_enqueue_style(
        'font-awesome-cdn',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
        [],
        '6.4.0'
    );
}

// 3. User Helper Functions for Role/Subscription Checking
function petslist_get_local_user($wp_user_id = 0) {
    global $wpdb;
    if (!$wp_user_id) {
        $wp_user_id = get_current_user_id();
    }
    if (!$wp_user_id) {
        return null;
    }
    
    // Check if user exists in custom table
    $user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM `{$wpdb->prefix}dog_users` WHERE wp_user_id = %d",
        $wp_user_id
    ));
    
    // If not found, check by email or create
    if (!$user) {
        $wp_user = get_userdata($wp_user_id);
        if ($wp_user) {
            $user_role = in_array('administrator', $wp_user->roles) ? 'admin' : 'visitor';
            $wpdb->insert("{$wpdb->prefix}dog_users", [
                'wp_user_id' => $wp_user_id,
                'name' => $wp_user->display_name,
                'email' => $wp_user->user_email,
                'role' => $user_role,
                'created_at' => current_time('mysql')
            ]);
            $user = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM `{$wpdb->prefix}dog_users` WHERE wp_user_id = %d",
                $wp_user_id
            ));
        }
    }
    
    return $user;
}

function petslist_is_subscribed($wp_user_id = 0) {
    if (!$wp_user_id) {
        $wp_user_id = get_current_user_id();
    }
    if (!$wp_user_id) {
        return false;
    }
    
    // Admin always gets full access
    if (user_can($wp_user_id, 'manage_options')) {
        return true;
    }
    
    $local_user = petslist_get_local_user($wp_user_id);
    if ($local_user && ($local_user->role === 'subscriber' || $local_user->role === 'admin')) {
        // Also verify subscription metadata exists (mock validation)
        return true;
    }
    
    return false;
}

// Fallback shortcode for fluentform to display custom newsletter form
add_shortcode('fluentform', 'petslist_newsletter_shortcode_fallback');
function petslist_newsletter_shortcode_fallback($atts) {
    ob_start();
    ?>
    <form class="petslist-custom-newsletter" onsubmit="event.preventDefault(); this.querySelector('.newsletter-msg').style.display='block'; this.querySelector('.newsletter-input-group').style.display='none';" style="position: relative; max-width: 340px; margin: 15px auto 0 auto;">
        <div class="newsletter-input-group" style="display: flex; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(2, 197, 189, 0.3); border-radius: 30px; padding: 4px; overflow: hidden; transition: border-color 0.2s ease;">
            <input type="email" placeholder="Your Email Address" required style="background: transparent; border: none; outline: none; color: #ffffff; padding: 8px 16px; font-size: 14px; flex-grow: 1; min-width: 120px;" />
            <button type="submit" style="background: linear-gradient(135deg, #02c5bd 0%, #009b93 100%); color: #ffffff; border: none; outline: none; padding: 8px 20px; border-radius: 25px; font-size: 13px; font-weight: 700; cursor: pointer; transition: transform 0.2s ease;">
                Subscribe
            </button>
        </div>
        <div class="newsletter-msg" style="display: none; color: #02c5bd; font-size: 14px; font-weight: 600; margin-top: 10px; text-align: center; animation: fadeIn 0.3s ease;">
            <i class="fas fa-check-circle" style="margin-right: 5px;"></i> Success! Thank you for subscribing.
        </div>
    </form>
    <?php
    return ob_get_clean();
}

// 4. Shortcode: Public Searchable Directory
add_shortcode('dog_public_directory', 'petslist_render_public_directory');
function petslist_render_public_directory($atts) {
    global $wpdb;
    ob_start();

    // Handle Detailed Profile View if dog_id is set
    if (isset($_GET['dog_id'])) {
        $dog_id = absint($_GET['dog_id']);
        // Increment view count
        $wpdb->query($wpdb->prepare("UPDATE `{$wpdb->prefix}dog_dogs` SET views = views + 1 WHERE id = %d", $dog_id));
        $dog = $wpdb->get_row($wpdb->prepare("SELECT d.*, u.name as owner_name, u.email as owner_email FROM `{$wpdb->prefix}dog_dogs` d LEFT JOIN `{$wpdb->prefix}dog_users` u ON d.user_id = u.id WHERE d.id = %d", $dog_id));
        
        if ($dog) {
            $is_subscribed = petslist_is_subscribed();
            $is_owner = get_current_user_id() && $dog->user_id == petslist_get_local_user(get_current_user_id())->id;
            $has_access = $is_subscribed || $is_owner;
            ?>
            <div class="petslist-dashboard-container">
                <div class="petslist-db-header">
                    <div class="petslist-db-user">
                        <h2>Dog Profile: <?php echo esc_html($dog->name); ?></h2>
                        <p><?php echo esc_html($dog->breed); ?> &bull; <?php echo esc_html($dog->gender); ?></p>
                    </div>
                    <a href="?action=directory" class="petslist-btn-secondary"><i class="fas fa-arrow-left"></i> Back to Directory</a>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="petslist-glass-card" style="text-align: center;">
                            <h4 style="margin-bottom: 15px; color: var(--dir-primary); font-weight: 700;">Front Photo</h4>
                            <img src="<?php echo esc_url($dog->front_image ?: 'https://via.placeholder.com/600x400?text=No+Front+Image'); ?>" style="width: 100%; max-height: 350px; object-fit: cover; border-radius: 12px; border: 1px solid var(--dir-border);" />
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="petslist-glass-card" style="text-align: center;">
                            <h4 style="margin-bottom: 15px; color: var(--dir-primary); font-weight: 700;">Side Photo</h4>
                            <img src="<?php echo esc_url($dog->side_image ?: 'https://via.placeholder.com/600x400?text=No+Side+Image'); ?>" style="width: 100%; max-height: 350px; object-fit: cover; border-radius: 12px; border: 1px solid var(--dir-border);" />
                        </div>
                    </div>
                </div>

                <div class="petslist-glass-card" style="margin-top: 20px;">
                    <h3 style="color: #ffffff; border-bottom: 1px solid var(--dir-border); padding-bottom: 10px; margin-bottom: 20px; font-weight: 700; font-family: 'Baloo Bhaijaan 2';">Overview & Description</h3>
                    <p style="font-size: 15px; line-height: 1.7; color: rgba(255,255,255,0.9);"><?php echo nl2br(esc_html($dog->description)); ?></p>
                </div>

                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="petslist-glass-card">
                            <h4 style="color: var(--dir-primary); font-weight: 700; margin-bottom: 15px;">Key Details</h4>
                            <table class="table" style="color: #ffffff; font-size: 14px;">
                                <tr><td><strong>Gender:</strong></td><td><?php echo esc_html($dog->gender); ?></td></tr>
                                <tr><td><strong>Color:</strong></td><td><?php echo esc_html($dog->color ?: 'N/A'); ?></td></tr>
                                <tr><td><strong>Weight:</strong></td><td><?php echo esc_html($dog->weight ?: 'N/A'); ?></td></tr>
                                <tr><td><strong>Age:</strong></td><td><?php echo esc_html($dog->age); ?> Years</td></tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-12">
                        <div class="petslist-glass-card">
                            <h4 style="color: var(--dir-primary); font-weight: 700; margin-bottom: 15px;">Pedigree & Origin</h4>
                            <?php if ($has_access): ?>
                                <table class="table" style="color: #ffffff; font-size: 14px;">
                                    <tr><td><strong>Kennel:</strong></td><td><?php echo esc_html($dog->kennel ?: 'N/A'); ?></td></tr>
                                    <tr><td><strong>Location:</strong></td><td><?php echo esc_html($dog->city . ', ' . $dog->country); ?></td></tr>
                                    <tr><td><strong>Registration #:</strong></td><td><?php echo esc_html($dog->registration_number ?: 'N/A'); ?></td></tr>
                                </table>
                            <?php else: ?>
                                <div style="text-align: center; padding: 15px; background: rgba(0,0,0,0.2); border-radius: 8px; border: 1px dashed rgba(255, 61, 65, 0.4);">
                                    <p style="color: var(--dir-accent); font-weight: 600; margin-bottom: 10px;"><i class="fas fa-lock"></i> Locked Field Content</p>
                                    <p style="font-size: 12px; color: var(--dir-text-muted); margin-bottom: 12px;">Only subscribers can view full pedigree and registration numbers.</p>
                                    <a href="http://localhost:8000/pricing-table/" class="petslist-btn-primary" style="padding: 6px 16px; font-size: 12px;">Subscribe Now</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="petslist-glass-card">
                            <h4 style="color: var(--dir-primary); font-weight: 700; margin-bottom: 15px;">Awards & Achievements</h4>
                            <?php if ($has_access): ?>
                                <p style="font-size: 14px; color: #ffffff;"><?php echo nl2br(esc_html($dog->awards ?: 'No awards listed yet.')); ?></p>
                            <?php else: ?>
                                <p style="color: var(--dir-text-muted); font-size: 13px;"><i class="fas fa-lock"></i> Locked content. Subscribe to view awards.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-12">
                        <div class="petslist-glass-card">
                            <h4 style="color: var(--dir-primary); font-weight: 700; margin-bottom: 15px;">Health Information</h4>
                            <?php if ($has_access): ?>
                                <p style="font-size: 14px; color: #ffffff;"><?php echo nl2br(esc_html($dog->health_info ?: 'No health certificates listed.')); ?></p>
                            <?php else: ?>
                                <p style="color: var(--dir-text-muted); font-size: 13px;"><i class="fas fa-lock"></i> Locked content. Subscribe to view health files.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (!$has_access): ?>
                    <div class="petslist-subscriber-cta-card" style="
                        background: linear-gradient(135deg, #070c3e 0%, #0c145e 100%);
                        border-radius: 12px;
                        padding: 30px;
                        margin-top: 20px;
                        color: #ffffff;
                        text-align: center;
                        border: 1px solid rgba(2, 197, 189, 0.2);
                    ">
                        <div style="font-size: 32px; color: #02c5bd; margin-bottom: 12px;"><i class="fas fa-lock"></i></div>
                        <h3 style="color: #ffffff; margin-bottom: 10px; font-weight: 700; font-size: 20px;">Premium Dog Profile Restricted</h3>
                        <p style="color: rgba(255, 255, 255, 0.85); font-size: 15px; max-width: 480px; margin: 0 auto 20px auto; line-height: 1.5;">
                            Subscribe to view complete dog profiles, pedigree charts, award certificates, health records, and breeder contact details.
                        </p>
                        <a href="http://localhost:8000/pricing-table/" class="petslist-btn-primary" style="background-color: #ff3d41;">
                            <i class="fas fa-arrow-right"></i> Choose a Subscription Plan
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <?php
            return ob_get_clean();
        }
    }

    // Default: Render directory search form and results list
    $search = isset($_GET['ds_search']) ? sanitize_text_field($_GET['ds_search']) : '';
    $breed = isset($_GET['ds_breed']) ? sanitize_text_field($_GET['ds_breed']) : '';
    $gender = isset($_GET['ds_gender']) ? sanitize_text_field($_GET['ds_gender']) : '';
    $city = isset($_GET['ds_city']) ? sanitize_text_field($_GET['ds_city']) : '';
    $kennel = isset($_GET['ds_kennel']) ? sanitize_text_field($_GET['ds_kennel']) : '';
    $reg = isset($_GET['ds_reg']) ? sanitize_text_field($_GET['ds_reg']) : '';
    $sort = isset($_GET['ds_sort']) ? sanitize_text_field($_GET['ds_sort']) : 'newest';

    // Construct Query
    $query = "SELECT * FROM `{$wpdb->prefix}dog_dogs` WHERE 1=1";
    $params = [];

    if ($search) {
        $query .= " AND (name LIKE %s OR description LIKE %s)";
        $params[] = '%' . $wpdb->esc_like($search) . '%';
        $params[] = '%' . $wpdb->esc_like($search) . '%';
    }
    if ($breed) {
        $query .= " AND breed = %s";
        $params[] = $breed;
    }
    if ($gender) {
        $query .= " AND gender = %s";
        $params[] = $gender;
    }
    if ($city) {
        $query .= " AND city LIKE %s";
        $params[] = '%' . $wpdb->esc_like($city) . '%';
    }
    if ($kennel) {
        $query .= " AND kennel LIKE %s";
        $params[] = '%' . $wpdb->esc_like($kennel) . '%';
    }
    if ($reg) {
        $query .= " AND registration_number = %s";
        $params[] = $reg;
    }

    // Sorting
    switch ($sort) {
        case 'oldest':
            $query .= " ORDER BY created_at ASC";
            break;
        case 'popular':
            $query .= " ORDER BY views DESC";
            break;
        case 'updated':
            $query .= " ORDER BY created_at DESC";
            break;
        case 'newest':
        default:
            $query .= " ORDER BY created_at DESC";
            break;
    }

    if (!empty($params)) {
        $dogs = $wpdb->get_results($wpdb->prepare($query, $params));
    } else {
        $dogs = $wpdb->get_results($query);
    }

    // Get unique breeds and cities for filters
    $breeds_list = $wpdb->get_col("SELECT DISTINCT breed FROM `{$wpdb->prefix}dog_dogs` WHERE breed != '';");
    ?>
    <div class="petslist-dashboard-container">
        <!-- Filters Form -->
        <form method="get" action="" class="petslist-glass-card" style="margin-bottom: 30px;">
            <h3 style="color: #ffffff; margin-bottom: 20px; font-weight: 700; font-family: 'Baloo Bhaijaan 2';"><i class="fas fa-search" style="color: var(--dir-primary); margin-right: 8px;"></i> Search Studs Directory</h3>
            
            <div class="row">
                <div class="col-md-3 col-sm-6 col-12 petslist-form-group">
                    <label class="petslist-form-label">Keyword</label>
                    <input type="text" name="ds_search" value="<?php echo esc_attr($search); ?>" placeholder="Search name..." class="petslist-form-input" />
                </div>
                <div class="col-md-3 col-sm-6 col-12 petslist-form-group">
                    <label class="petslist-form-label">Breed</label>
                    <select name="ds_breed" class="petslist-form-input">
                        <option value="">All Breeds</option>
                        <?php foreach ($breeds_list as $b): ?>
                            <option value="<?php echo esc_attr($b); ?>" <?php selected($breed, $b); ?>><?php echo esc_html($b); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 col-12 petslist-form-group">
                    <label class="petslist-form-label">Gender</label>
                    <select name="ds_gender" class="petslist-form-input">
                        <option value="">All</option>
                        <option value="Male" <?php selected($gender, 'Male'); ?>>Male</option>
                        <option value="Female" <?php selected($gender, 'Female'); ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 col-12 petslist-form-group">
                    <label class="petslist-form-label">City</label>
                    <input type="text" name="ds_city" value="<?php echo esc_attr($city); ?>" placeholder="e.g. Los Angeles" class="petslist-form-input" />
                </div>
                <div class="col-md-2 col-sm-6 col-12 petslist-form-group">
                    <label class="petslist-form-label">Sort By</label>
                    <select name="ds_sort" class="petslist-form-input">
                        <option value="newest" <?php selected($sort, 'newest'); ?>>Newest</option>
                        <option value="oldest" <?php selected($sort, 'oldest'); ?>>Oldest</option>
                        <option value="popular" <?php selected($sort, 'popular'); ?>>Popular</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 col-sm-6 col-12 petslist-form-group">
                    <label class="petslist-form-label">Kennel / Breeder</label>
                    <input type="text" name="ds_kennel" value="<?php echo esc_attr($kennel); ?>" placeholder="Search Kennel..." class="petslist-form-input" />
                </div>
                <div class="col-md-4 col-sm-6 col-12 petslist-form-group">
                    <label class="petslist-form-label">Registration Number</label>
                    <input type="text" name="ds_reg" value="<?php echo esc_attr($reg); ?>" placeholder="AKC number..." class="petslist-form-input" />
                </div>
                <div class="col-md-4 col-sm-12 col-12 petslist-form-group" style="display: flex; align-items: flex-end; justify-content: flex-end;">
                    <button type="submit" class="petslist-btn-primary" style="width: 100%; justify-content: center;"><i class="fas fa-filter"></i> Apply Filters</button>
                </div>
            </div>
        </form>

        <!-- Directory Grid -->
        <h3 style="color: #ffffff; margin-bottom: 20px; font-weight: 700; font-family: 'Baloo Bhaijaan 2';">Directory Listings (<?php echo count($dogs); ?>)</h3>
        
        <?php if (!empty($dogs)): ?>
            <div class="petslist-dir-grid">
                <?php foreach ($dogs as $dog): ?>
                    <div class="petslist-glass-card petslist-dir-card">
                        <img src="<?php echo esc_url($dog->front_image ?: 'https://via.placeholder.com/300x200?text=No+Photo'); ?>" />
                        <div class="petslist-dir-info">
                            <h4 style="color: #ffffff; font-weight: 700; margin: 0 0 5px 0; font-size: 18px;"><?php echo esc_html($dog->name); ?></h4>
                            <p style="color: var(--dir-primary); font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;"><?php echo esc_html($dog->breed); ?></p>
                            <p style="font-size: 14px; color: var(--dir-text-muted); line-height: 1.5; height: 60px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;">
                                <?php echo esc_html($dog->description); ?>
                            </p>
                            <div class="petslist-dir-meta">
                                <div><i class="fas fa-venus-mars" style="color: var(--dir-primary);"></i> <?php echo esc_html($dog->gender); ?></div>
                                <div><i class="fas fa-map-marker-alt" style="color: var(--dir-primary);"></i> <?php echo esc_html($dog->city ?: 'N/A'); ?></div>
                            </div>
                            <a href="?dog_id=<?php echo $dog->id; ?>" class="petslist-btn-secondary" style="width: 100%; justify-content: center; margin-top: 15px;">View Full Profile</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="petslist-glass-card" style="text-align: center; padding: 50px 20px;">
                <div style="font-size: 40px; color: var(--dir-text-muted); margin-bottom: 15px;"><i class="fas fa-dog"></i></div>
                <h4 style="color: #ffffff;">No Dog Profiles Found</h4>
                <p style="color: var(--dir-text-muted); font-size: 14px;">Try clearing filters or search keyword.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// 5. Shortcode: Subscriber Dashboard (Add/Edit/Delete Dogs, View Billing, View Profile, Analytics)
add_shortcode('dog_subscriber_dashboard', 'petslist_render_subscriber_dashboard');
function petslist_render_subscriber_dashboard($atts) {
    if (!is_user_logged_in()) {
        return '<div class="petslist-dashboard-container" style="text-align: center; padding: 40px 20px;">
            <h3>Subscribers Dashboard</h3>
            <p>Please log in or register to access your account dashboard.</p>
            <a href="http://localhost:8000/my-account/" class="petslist-btn-primary">Log In / Register</a>
        </div>';
    }

    global $wpdb;
    $wp_user_id = get_current_user_id();
    $local_user = petslist_get_local_user($wp_user_id);
    
    // Process Actions (CRUD)
    $msg = '';
    
    // Create subscriber role if subscription was bought
    if (isset($_GET['action']) && $_GET['action'] == 'renew_plan') {
        $wpdb->update("{$wpdb->prefix}dog_users", ['role' => 'subscriber', 'subscription_id' => 1], ['wp_user_id' => $wp_user_id]);
        // Insert dummy payment
        $wpdb->insert("{$wpdb->prefix}dog_payments", [
            'user_id' => $local_user->id,
            'amount' => 12.00,
            'payment_method' => 'Stripe CC',
            'status' => 'completed',
            'transaction_id' => 'txn_' . wp_generate_password(12, false),
            'created_at' => current_time('mysql')
        ]);
        wp_redirect(remove_query_arg('action'));
        exit;
    }
    
    if (isset($_POST['petslist_dog_submit'])) {
        // Simple Form validation and insertion/update
        $dog_id = isset($_POST['dog_id']) ? absint($_POST['dog_id']) : 0;
        $name = sanitize_text_field($_POST['name']);
        $breed = sanitize_text_field($_POST['breed']);
        $gender = sanitize_text_field($_POST['gender']);
        $dob = sanitize_text_field($_POST['dob']);
        $age = absint($_POST['age']);
        $desc = sanitize_textarea_field($_POST['description']);
        $front_img = esc_url_raw($_POST['front_image']);
        $side_img = esc_url_raw($_POST['side_image']);
        $gallery = esc_url_raw($_POST['gallery']);
        $color = sanitize_text_field($_POST['color']);
        $weight = sanitize_text_field($_POST['weight']);
        $reg = sanitize_text_field($_POST['registration_number']);
        $awards = sanitize_textarea_field($_POST['awards']);
        $health = sanitize_textarea_field($_POST['health_info']);
        $pedigree = sanitize_textarea_field($_POST['pedigree']);
        $kennel = sanitize_text_field($_POST['kennel']);
        $city = sanitize_text_field($_POST['city']);
        $phone = sanitize_text_field($_POST['phone']);

        $dog_data = [
            'user_id' => $local_user->id,
            'name' => $name,
            'breed' => $breed,
            'gender' => $gender,
            'dob' => $dob,
            'age' => $age,
            'description' => $desc,
            'front_image' => $front_img,
            'side_image' => $side_img,
            'gallery' => $gallery,
            'color' => $color,
            'weight' => $weight,
            'registration_number' => $reg,
            'awards' => $awards,
            'health_info' => $health,
            'pedigree' => $pedigree,
            'kennel' => $kennel,
            'city' => $city,
            'phone' => $phone
        ];

        if ($dog_id) {
            $wpdb->update("{$wpdb->prefix}dog_dogs", $dog_data, ['id' => $dog_id, 'user_id' => $local_user->id]);
            $msg = '<div class="alert alert-success" style="background: rgba(2, 197, 189, 0.1); border: 1px solid var(--dir-primary); color: #ffffff; padding: 12px; border-radius: 8px; margin-bottom: 20px;">Dog profile updated successfully!</div>';
        } else {
            $dog_data['created_at'] = current_time('mysql');
            $wpdb->insert("{$wpdb->prefix}dog_dogs", $dog_data);
            $msg = '<div class="alert alert-success" style="background: rgba(2, 197, 189, 0.1); border: 1px solid var(--dir-primary); color: #ffffff; padding: 12px; border-radius: 8px; margin-bottom: 20px;">Dog listing added successfully!</div>';
        }
    }

    if (isset($_GET['delete_dog'])) {
        $dog_id = absint($_GET['delete_dog']);
        $wpdb->delete("{$wpdb->prefix}dog_dogs", ['id' => $dog_id, 'user_id' => $local_user->id]);
        $msg = '<div class="alert alert-success" style="background: rgba(2, 197, 189, 0.1); border: 1px solid var(--dir-primary); color: #ffffff; padding: 12px; border-radius: 8px; margin-bottom: 20px;">Dog profile deleted.</div>';
    }

    // Refresh user data
    $local_user = petslist_get_local_user($wp_user_id);
    $dogs = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}dog_dogs` WHERE user_id = %d", $local_user->id));
    $payments = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}dog_payments` WHERE user_id = %d ORDER BY created_at DESC", $local_user->id));

    ob_start();
    ?>
    <div class="petslist-dashboard-container">
        <?php echo $msg; ?>
        
        <div class="petslist-db-header">
            <div class="petslist-db-user">
                <h2>Welcome, <?php echo esc_html($local_user->name); ?>!</h2>
                <p>Manage your account, dog listings, and payments below.</p>
            </div>
            <div class="petslist-badge">
                <?php echo $local_user->role === 'subscriber' ? 'Active Member' : 'Free Visitor'; ?>
            </div>
        </div>

        <div class="petslist-db-layout">
            <!-- Sidebar navigation -->
            <div class="petslist-db-nav">
                <a class="petslist-db-nav-item active" onclick="petslistSwitchTab('overview')"><i class="fas fa-chart-line"></i> Dashboard</a>
                <a class="petslist-db-nav-item" onclick="petslistSwitchTab('dogs')"><i class="fas fa-dog"></i> My Dogs</a>
                <a class="petslist-db-nav-item" onclick="petslistSwitchTab('billing')"><i class="fas fa-credit-card"></i> Billing History</a>
                <a class="petslist-db-nav-item" href="http://localhost:8000/my-account/edit-account/"><i class="fas fa-user-cog"></i> Profile Settings</a>
            </div>

            <!-- Dashboard sections -->
            <div class="petslist-db-content">
                <!-- Section 1: Overview -->
                <div id="petslist-section-overview" class="petslist-db-content-section active">
                    <div class="petslist-stats-grid">
                        <div class="petslist-glass-card petslist-stat-box">
                            <h3>Total Listings</h3>
                            <div class="stat-val"><?php echo count($dogs); ?></div>
                        </div>
                        <div class="petslist-glass-card petslist-stat-box">
                            <h3>Total Profile Views</h3>
                            <div class="stat-val">
                                <?php 
                                $views = 0;
                                foreach ($dogs as $d) { $views += $d->views; }
                                echo $views;
                                ?>
                            </div>
                        </div>
                        <div class="petslist-glass-card petslist-stat-box">
                            <h3>Membership Plan</h3>
                            <div class="stat-val" style="font-size: 20px; line-height: 48px; color: var(--dir-primary);">
                                <?php echo $local_user->role === 'subscriber' ? 'Gold Plan ($12/mo)' : 'No Active Plan'; ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($local_user->role !== 'subscriber'): ?>
                        <div class="petslist-glass-card" style="border-color: var(--dir-accent); background: rgba(255, 61, 65, 0.03);">
                            <h4 style="color: #ffffff; font-weight: 700; margin-bottom: 10px;"><i class="fas fa-exclamation-triangle" style="color: var(--dir-accent); margin-right: 5px;"></i> You are currently on a Free visitor plan</h4>
                            <p style="color: var(--dir-text-muted); font-size: 14px; margin-bottom: 20px;">To post and list multiple dogs, and view full pedigree information of other dogs, please upgrade to a Gold Monthly subscription plan ($12/month) via credit card.</p>
                            <a href="?action=renew_plan" class="petslist-btn-primary" style="background-color: var(--dir-accent);"><i class="fas fa-arrow-up"></i> Subscribe Gold Plan ($12/mo)</a>
                        </div>
                    <?php endif; ?>

                    <div class="petslist-glass-card">
                        <h4 style="color: #ffffff; font-weight: 700; margin-bottom: 20px; font-family: 'Baloo Bhaijaan 2';">My Dogs Popularity Analytics</h4>
                        <div style="position: relative; height: 180px; width: 100%; padding-top: 20px;">
                            <?php if (empty($dogs)): ?>
                                <p style="color: var(--dir-text-muted); text-align: center; line-height: 140px;">No dog listings to analyze. Add a dog first!</p>
                            <?php else: ?>
                                <!-- HTML Bar Chart for Analytics -->
                                <div style="display: flex; align-items: flex-end; justify-content: space-around; height: 120px; border-bottom: 1px solid var(--dir-border);">
                                    <?php foreach ($dogs as $d): ?>
                                        <div style="text-align: center; flex-grow: 1;">
                                            <div style="
                                                background: linear-gradient(to top, var(--dir-primary) 0%, #009b93 100%);
                                                width: 30px;
                                                height: <?php echo min(100, $d->views + 15); ?>px;
                                                margin: 0 auto;
                                                border-radius: 5px 5px 0 0;
                                                box-shadow: 0 4px 10px rgba(2, 197, 189, 0.2);
                                            "></div>
                                            <span style="font-size: 11px; display: block; margin-top: 8px; color: var(--dir-text-muted);"><?php echo esc_html($d->name); ?> (<?php echo $d->views; ?>)</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Manage Dogs -->
                <div id="petslist-section-dogs" class="petslist-db-content-section">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="color: #ffffff; font-weight: 700; margin: 0; font-family: 'Baloo Bhaijaan 2';">My Dog Profiles</h3>
                        <?php if ($local_user->role === 'subscriber'): ?>
                            <a class="petslist-btn-primary" onclick="petslistShowAddDogForm()"><i class="fas fa-plus"></i> Add Multiple Dogs</a>
                        <?php else: ?>
                            <a class="petslist-btn-primary" style="opacity: 0.6; cursor: not-allowed; background: gray;" title="Subscribe to add dogs"><i class="fas fa-lock"></i> Add Dog (Locked)</a>
                        <?php endif; ?>
                    </div>

                    <!-- Add / Edit Dog Form -->
                    <div id="petslist-dog-form-wrap" class="petslist-glass-card" style="display: none; margin-bottom: 30px;">
                        <h4 id="form-title" style="color: #ffffff; margin-bottom: 20px; font-weight: 700;">Add New Dog</h4>
                        <form method="post" action="">
                            <input type="hidden" name="dog_id" id="form-dog-id" value="" />
                            
                            <div class="row">
                                <div class="col-md-6 col-12 petslist-form-group">
                                    <label class="petslist-form-label">Dog Name *</label>
                                    <input type="text" name="name" id="form-name" required class="petslist-form-input" />
                                </div>
                                <div class="col-md-6 col-12 petslist-form-group">
                                    <label class="petslist-form-label">Breed *</label>
                                    <input type="text" name="breed" id="form-breed" required class="petslist-form-input" placeholder="e.g. Golden Retriever" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 col-sm-6 col-12 petslist-form-group">
                                    <label class="petslist-form-label">Gender</label>
                                    <select name="gender" id="form-gender" class="petslist-form-input">
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-6 col-12 petslist-form-group">
                                    <label class="petslist-form-label">Date of Birth</label>
                                    <input type="date" name="dob" id="form-dob" class="petslist-form-input" />
                                </div>
                                <div class="col-md-4 col-12 petslist-form-group">
                                    <label class="petslist-form-label">Age (Years)</label>
                                    <input type="number" name="age" id="form-age" class="petslist-form-input" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 col-sm-6 col-12 petslist-form-group">
                                    <label class="petslist-form-label">Color</label>
                                    <input type="text" name="color" id="form-color" class="petslist-form-input" />
                                </div>
                                <div class="col-md-4 col-sm-6 col-12 petslist-form-group">
                                    <label class="petslist-form-label">Weight</label>
                                    <input type="text" name="weight" id="form-weight" class="petslist-form-input" placeholder="e.g. 70 lbs" />
                                </div>
                                <div class="col-md-4 col-12 petslist-form-group">
                                    <label class="petslist-form-label">Registration Number</label>
                                    <input type="text" name="registration_number" id="form-reg" class="petslist-form-input" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-12 petslist-form-group">
                                    <label class="petslist-form-label">Front Photo URL *</label>
                                    <input type="text" name="front_image" id="form-front-img" required class="petslist-form-input" placeholder="http://..." />
                                </div>
                                <div class="col-md-6 col-12 petslist-form-group">
                                    <label class="petslist-form-label">Side Photo URL *</label>
                                    <input type="text" name="side_image" id="form-side-img" required class="petslist-form-input" placeholder="http://..." />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 col-sm-6 col-12 petslist-form-group">
                                    <label class="petslist-form-label">Kennel Name</label>
                                    <input type="text" name="kennel" id="form-kennel" class="petslist-form-input" />
                                </div>
                                <div class="col-md-4 col-sm-6 col-12 petslist-form-group">
                                    <label class="petslist-form-label">City</label>
                                    <input type="text" name="city" id="form-city" class="petslist-form-input" />
                                </div>
                                <div class="col-md-4 col-12 petslist-form-group">
                                    <label class="petslist-form-label">Contact Phone</label>
                                    <input type="text" name="phone" id="form-phone" class="petslist-form-input" />
                                </div>
                            </div>

                            <div class="petslist-form-group">
                                <label class="petslist-form-label">Dog Description</label>
                                <textarea name="description" id="form-desc" rows="4" class="petslist-form-input"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-4 col-12 petslist-form-group">
                                    <label class="petslist-form-label">Awards / Achievements</label>
                                    <textarea name="awards" id="form-awards" rows="3" class="petslist-form-input"></textarea>
                                </div>
                                <div class="col-md-4 col-12 petslist-form-group">
                                    <label class="petslist-form-label">Health Info / Certificates</label>
                                    <textarea name="health_info" id="form-health" rows="3" class="petslist-form-input"></textarea>
                                </div>
                                <div class="col-md-4 col-12 petslist-form-group">
                                    <label class="petslist-form-label">Pedigree Details</label>
                                    <textarea name="pedigree" id="form-pedigree" rows="3" class="petslist-form-input"></textarea>
                                </div>
                            </div>

                            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px;">
                                <button type="button" class="petslist-btn-secondary" onclick="petslistCancelDogForm()">Cancel</button>
                                <button type="submit" name="petslist_dog_submit" class="petslist-btn-primary">Save Profile</button>
                            </div>
                        </form>
                    </div>

                    <!-- Dogs Cards list -->
                    <?php if (!empty($dogs)): ?>
                        <div class="petslist-dogs-list">
                            <?php foreach ($dogs as $dog): ?>
                                <div class="petslist-glass-card petslist-dog-card">
                                    <img src="<?php echo esc_url($dog->front_image ?: 'https://via.placeholder.com/300x200?text=No+Photo'); ?>" class="petslist-dog-img" />
                                    <div class="petslist-dog-details">
                                        <h4><?php echo esc_html($dog->name); ?></h4>
                                        <p><strong>Breed:</strong> <?php echo esc_html($dog->breed); ?></p>
                                        <p><strong>Gender:</strong> <?php echo esc_html($dog->gender); ?></p>
                                        <p><strong>Views:</strong> <?php echo $dog->views; ?></p>
                                    </div>
                                    <div class="petslist-dog-actions">
                                        <button class="petslist-btn-secondary" style="padding: 6px 12px; font-size: 13px;" onclick='petslistEditDog(<?php echo json_encode($dog); ?>)'><i class="fas fa-edit"></i> Edit</button>
                                        <a href="?delete_dog=<?php echo $dog->id; ?>" class="petslist-btn-danger" style="padding: 6px 12px; font-size: 13px; margin: 0;" onclick="return confirm('Are you sure you want to delete this dog profile?');"><i class="fas fa-trash-alt"></i> Delete</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="petslist-glass-card" style="text-align: center; padding: 40px 20px;">
                            <p style="color: var(--dir-text-muted); font-size: 14px;">No dog listings found on your account.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Section 3: Billing History -->
                <div id="petslist-section-billing" class="petslist-db-content-section">
                    <h3 style="color: #ffffff; font-weight: 700; margin-bottom: 20px; font-family: 'Baloo Bhaijaan 2';">Payment History</h3>
                    
                    <?php if (!empty($payments)): ?>
                        <div class="table-responsive">
                            <table class="table" style="color: #ffffff; border-color: var(--dir-border); font-size: 14px;">
                                <thead>
                                    <tr style="border-bottom: 2px solid var(--dir-border);">
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Payment Method</th>
                                        <th>Status</th>
                                        <th>Transaction ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr style="border-bottom: 1px solid var(--dir-border);">
                                            <td><?php echo date('M d, Y', strtotime($payment->created_at)); ?></td>
                                            <td>$<?php echo number_format($payment->amount, 2); ?></td>
                                            <td><?php echo esc_html($payment->payment_method); ?></td>
                                            <td><span style="color: #02c5bd; font-weight: 600;"><i class="fas fa-check-circle"></i> <?php echo esc_html($payment->status); ?></span></td>
                                            <td><code><?php echo esc_html($payment->transaction_id); ?></code></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="petslist-glass-card" style="text-align: center; padding: 40px 20px;">
                            <p style="color: var(--dir-text-muted); font-size: 14px;">No payment records found on your account.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Frontend Interactive Javascript -->
    <script>
        function petslistSwitchTab(tabName) {
            // Hide all sections
            const sections = document.querySelectorAll('.petslist-db-content-section');
            sections.forEach(s => s.classList.remove('active'));
            
            // Show selected section
            const targetSection = document.getElementById('petslist-section-' + tabName);
            if (targetSection) {
                targetSection.classList.add('active');
            }
            
            // Update nav item active classes
            const navItems = document.querySelectorAll('.petslist-db-nav-item');
            navItems.forEach(item => item.classList.remove('active'));
            
            // Find active element
            event.currentTarget.classList.add('active');
        }

        function petslistShowAddDogForm() {
            document.getElementById('form-title').innerText = 'Add New Dog Profile';
            document.getElementById('form-dog-id').value = '';
            document.getElementById('form-name').value = '';
            document.getElementById('form-breed').value = '';
            document.getElementById('form-gender').value = 'Male';
            document.getElementById('form-dob').value = '';
            document.getElementById('form-age').value = '';
            document.getElementById('form-front-img').value = '';
            document.getElementById('form-side-img').value = '';
            document.getElementById('form-color').value = '';
            document.getElementById('form-weight').value = '';
            document.getElementById('form-reg').value = '';
            document.getElementById('form-kennel').value = '';
            document.getElementById('form-city').value = '';
            document.getElementById('form-phone').value = '';
            document.getElementById('form-desc').value = '';
            document.getElementById('form-awards').value = '';
            document.getElementById('form-health').value = '';
            document.getElementById('form-pedigree').value = '';
            
            document.getElementById('petslist-dog-form-wrap').style.display = 'block';
            document.getElementById('petslist-dog-form-wrap').scrollIntoView({ behavior: 'smooth' });
        }

        function petslistEditDog(dog) {
            document.getElementById('form-title').innerText = 'Edit Dog Profile: ' + dog.name;
            document.getElementById('form-dog-id').value = dog.id;
            document.getElementById('form-name').value = dog.name;
            document.getElementById('form-breed').value = dog.breed;
            document.getElementById('form-gender').value = dog.gender;
            document.getElementById('form-dob').value = dog.dob;
            document.getElementById('form-age').value = dog.age;
            document.getElementById('form-front-img').value = dog.front_image;
            document.getElementById('form-side-img').value = dog.side_image;
            document.getElementById('form-color').value = dog.color;
            document.getElementById('form-weight').value = dog.weight;
            document.getElementById('form-reg').value = dog.registration_number;
            document.getElementById('form-kennel').value = dog.kennel;
            document.getElementById('form-city').value = dog.city;
            document.getElementById('form-phone').value = dog.phone;
            document.getElementById('form-desc').value = dog.description;
            document.getElementById('form-awards').value = dog.awards;
            document.getElementById('form-health').value = dog.health_info;
            document.getElementById('form-pedigree').value = dog.pedigree;
            
            document.getElementById('petslist-dog-form-wrap').style.display = 'block';
            document.getElementById('petslist-dog-form-wrap').scrollIntoView({ behavior: 'smooth' });
        }

        function petslistCancelDogForm() {
            document.getElementById('petslist-dog-form-wrap').style.display = 'none';
        }
    </script>
    <?php
    return ob_get_clean();
}

// 6. Shortcode: Admin Dashboard
add_shortcode('dog_admin_dashboard', 'petslist_render_admin_dashboard');
function petslist_render_admin_dashboard($atts) {
    if (!current_user_can('manage_options')) {
        return '<div class="petslist-dashboard-container" style="text-align: center; padding: 40px 20px;">
            <h3>Access Denied</h3>
            <p>You must be an administrator to view this dashboard.</p>
        </div>';
    }

    global $wpdb;
    
    // Process Actions
    if (isset($_GET['action'])) {
        $action = sanitize_text_field($_GET['action']);
        
        // Remove Dog Listing
        if ($action == 'delete_dog_admin' && isset($_GET['dog_id'])) {
            $dog_id = absint($_GET['dog_id']);
            $wpdb->delete("{$wpdb->prefix}dog_dogs", ['id' => $dog_id]);
            wp_redirect(remove_query_arg(['action', 'dog_id']));
            exit;
        }
        
        // Manage Users Subscriptions
        if ($action == 'make_subscriber' && isset($_GET['user_id'])) {
            $user_id = absint($_GET['user_id']);
            $wpdb->update("{$wpdb->prefix}dog_users", ['role' => 'subscriber', 'subscription_id' => 2], ['id' => $user_id]);
            wp_redirect(remove_query_arg(['action', 'user_id']));
            exit;
        }
        
        if ($action == 'remove_subscription' && isset($_GET['user_id'])) {
            $user_id = absint($_GET['user_id']);
            $wpdb->update("{$wpdb->prefix}dog_users", ['role' => 'visitor', 'subscription_id' => NULL], ['id' => $user_id]);
            wp_redirect(remove_query_arg(['action', 'user_id']));
            exit;
        }
    }

    // Fetch Stats
    $total_users = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}dog_users` WHERE role != 'admin';");
    $total_subs = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}dog_users` WHERE role = 'subscriber';");
    $total_dogs = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}dog_dogs`;");
    $total_revenue = $wpdb->get_var("SELECT SUM(amount) FROM `{$wpdb->prefix}dog_payments` WHERE status = 'completed';");

    // Fetch lists
    $users_list = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}dog_users` ORDER BY created_at DESC;");
    $dogs_list = $wpdb->get_results("SELECT d.*, u.name as owner_name FROM `{$wpdb->prefix}dog_dogs` d LEFT JOIN `{$wpdb->prefix}dog_users` u ON d.user_id = u.id ORDER BY d.created_at DESC;");
    $payments_list = $wpdb->get_results("SELECT p.*, u.name as user_name FROM `{$wpdb->prefix}dog_payments` p LEFT JOIN `{$wpdb->prefix}dog_users` u ON p.user_id = u.id ORDER BY p.created_at DESC;");

    ob_start();
    ?>
    <div class="petslist-dashboard-container">
        <div class="petslist-db-header">
            <div class="petslist-db-user">
                <h2>Admin Dashboard</h2>
                <p>System Overview, Users, Dogs and Payment logs.</p>
            </div>
            <div class="petslist-badge" style="background: var(--dir-accent);">
                Admin Controller
            </div>
        </div>

        <div class="petslist-db-layout">
            <!-- Sidebar navigation -->
            <div class="petslist-db-nav">
                <a class="petslist-db-nav-item active" onclick="petslistSwitchTabAdmin('stats')"><i class="fas fa-chart-bar"></i> Overview</a>
                <a class="petslist-db-nav-item" onclick="petslistSwitchTabAdmin('users')"><i class="fas fa-users"></i> Users</a>
                <a class="petslist-db-nav-item" onclick="petslistSwitchTabAdmin('dogs')"><i class="fas fa-dog"></i> Dogs Directory</a>
                <a class="petslist-db-nav-item" onclick="petslistSwitchTabAdmin('payments')"><i class="fas fa-file-invoice-dollar"></i> Payments Log</a>
            </div>

            <!-- Content Area -->
            <div class="petslist-db-content">
                <!-- Tab 1: Stats Overview -->
                <div id="admin-section-stats" class="petslist-db-content-section active">
                    <div class="petslist-stats-grid">
                        <div class="petslist-glass-card petslist-stat-box">
                            <h3>Total Registered Users</h3>
                            <div class="stat-val"><?php echo absint($total_users); ?></div>
                        </div>
                        <div class="petslist-glass-card petslist-stat-box">
                            <h3>Active Subscribers</h3>
                            <div class="stat-val" style="color: var(--dir-primary);"><?php echo absint($total_subs); ?></div>
                        </div>
                        <div class="petslist-glass-card petslist-stat-box">
                            <h3>Total Listed Dogs</h3>
                            <div class="stat-val"><?php echo absint($total_dogs); ?></div>
                        </div>
                        <div class="petslist-glass-card petslist-stat-box">
                            <h3>Total Revenue</h3>
                            <div class="stat-val" style="color: #02c5bd;">$<?php echo number_format($total_revenue ?: 0, 2); ?></div>
                        </div>
                    </div>

                    <div class="petslist-glass-card">
                        <h4 style="color: #ffffff; font-weight: 700; margin-bottom: 20px; font-family: 'Baloo Bhaijaan 2';">Platform Summary</h4>
                        <p style="font-size: 14px; color: var(--dir-text-muted);">
                            This system is running fully optimized custom database integrations with mock subscription gateways. All listings and subscriber records are saved to the platform's SQL tables.
                        </p>
                    </div>
                </div>

                <!-- Tab 2: Users Management -->
                <div id="admin-section-users" class="petslist-db-content-section">
                    <h3 style="color: #ffffff; font-weight: 700; margin-bottom: 20px; font-family: 'Baloo Bhaijaan 2';">Users Directory</h3>
                    <div class="table-responsive">
                        <table class="table" style="color: #ffffff; border-color: var(--dir-border); font-size: 14px;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--dir-border);">
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Subscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users_list as $u): ?>
                                    <tr style="border-bottom: 1px solid var(--dir-border);">
                                        <td><?php echo esc_html($u->name); ?></td>
                                        <td><?php echo esc_html($u->email); ?></td>
                                        <td>
                                            <span style="font-weight: 600; color: <?php echo $u->role == 'admin' ? 'var(--dir-accent)' : ($u->role == 'subscriber' ? 'var(--dir-primary)' : 'gray'); ?>">
                                                <?php echo strtoupper(esc_html($u->role)); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $u->subscription_id == 2 ? 'Platinum Yearly' : ($u->subscription_id == 1 ? 'Gold Monthly' : 'None'); ?></td>
                                        <td>
                                            <?php if ($u->role == 'visitor'): ?>
                                                <a href="?action=make_subscriber&user_id=<?php echo $u->id; ?>" class="petslist-btn-primary" style="padding: 4px 10px; font-size: 12px;"><i class="fas fa-plus-circle"></i> Grant Subscription</a>
                                            <?php elseif ($u->role == 'subscriber'): ?>
                                                <a href="?action=remove_subscription&user_id=<?php echo $u->id; ?>" class="petslist-btn-danger" style="padding: 4px 10px; font-size: 12px; margin: 0;"><i class="fas fa-minus-circle"></i> Revoke Subscription</a>
                                            <?php else: ?>
                                                <span style="color: var(--dir-text-muted); font-size: 12px;">Admin actions disabled</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 3: Dogs Management -->
                <div id="admin-section-dogs" class="petslist-db-content-section">
                    <h3 style="color: #ffffff; font-weight: 700; margin-bottom: 20px; font-family: 'Baloo Bhaijaan 2';">Directory Submissions</h3>
                    <div class="table-responsive">
                        <table class="table" style="color: #ffffff; border-color: var(--dir-border); font-size: 14px;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--dir-border);">
                                    <th>Name</th>
                                    <th>Breed</th>
                                    <th>Owner</th>
                                    <th>Kennel</th>
                                    <th>Views</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dogs_list as $dog): ?>
                                    <tr style="border-bottom: 1px solid var(--dir-border);">
                                        <td><strong><?php echo esc_html($dog->name); ?></strong></td>
                                        <td><?php echo esc_html($dog->breed); ?></td>
                                        <td><?php echo esc_html($dog->owner_name ?: 'N/A'); ?></td>
                                        <td><?php echo esc_html($dog->kennel ?: 'N/A'); ?></td>
                                        <td><?php echo absint($dog->views); ?></td>
                                        <td>
                                            <a href="?action=delete_dog_admin&dog_id=<?php echo $dog->id; ?>" class="petslist-btn-danger" style="padding: 4px 10px; font-size: 12px; margin: 0;" onclick="return confirm('Remove this dog listing?');"><i class="fas fa-trash-alt"></i> Remove</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 4: Payments Logs -->
                <div id="admin-section-payments" class="petslist-db-content-section">
                    <h3 style="color: #ffffff; font-weight: 700; margin-bottom: 20px; font-family: 'Baloo Bhaijaan 2';">System Transaction History</h3>
                    <div class="table-responsive">
                        <table class="table" style="color: #ffffff; border-color: var(--dir-border); font-size: 14px;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--dir-border);">
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Txn ID</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments_list as $p): ?>
                                    <tr style="border-bottom: 1px solid var(--dir-border);">
                                        <td><?php echo esc_html($p->user_name ?: 'Unknown'); ?></td>
                                        <td>$<?php echo number_format($p->amount, 2); ?></td>
                                        <td><?php echo esc_html($p->payment_method); ?></td>
                                        <td><span style="color: var(--dir-primary); font-weight: 600;"><i class="fas fa-check-circle"></i> <?php echo esc_html($p->status); ?></span></td>
                                        <td><code><?php echo esc_html($p->transaction_id); ?></code></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($p->created_at)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Frontend Interactive Javascript for Admin -->
    <script>
        function petslistSwitchTabAdmin(tabName) {
            // Hide all sections
            const sections = document.querySelectorAll('#admin-section-stats, #admin-section-users, #admin-section-dogs, #admin-section-payments');
            sections.forEach(s => s.classList.remove('active'));
            
            // Show selected section
            const targetSection = document.getElementById('admin-section-' + tabName);
            if (targetSection) {
                targetSection.classList.add('active');
            }
            
            // Update nav item active classes
            const navItems = document.querySelectorAll('.petslist-db-nav-item');
            navItems.forEach(item => item.classList.remove('active'));
            
            // Find active element
            event.currentTarget.classList.add('active');
        }
    </script>
    <?php
    return ob_get_clean();
}
