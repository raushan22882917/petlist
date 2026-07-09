<?php
/**
 * Dog Directory - AJAX Handlers
 * @package Petslist Dog Directory
 */

namespace RadiusTheme\Petslist\DogDirectory;

if ( ! defined( 'ABSPATH' ) ) exit;

class Ajax {

    protected static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Dog CRUD
        add_action( 'wp_ajax_dd_save_dog', [ $this, 'save_dog' ] );
        add_action( 'wp_ajax_dd_delete_dog', [ $this, 'delete_dog' ] );
        add_action( 'wp_ajax_dd_get_dog', [ $this, 'get_dog' ] );
        add_action( 'wp_ajax_dd_get_dog_drawer', [ $this, 'get_dog_drawer' ] );

        // Auth
        add_action( 'wp_ajax_nopriv_dd_register', [ $this, 'register_user' ] );
        add_action( 'wp_ajax_nopriv_dd_login', [ $this, 'login_user' ] );
        add_action( 'wp_ajax_dd_login', [ $this, 'login_user' ] );
        add_action( 'wp_ajax_dd_update_profile', [ $this, 'update_profile' ] );
        add_action( 'wp_ajax_dd_change_password', [ $this, 'change_password' ] );
        add_action( 'wp_ajax_nopriv_dd_forgot_password', [ $this, 'forgot_password' ] );
        add_action( 'wp_ajax_dd_forgot_password', [ $this, 'forgot_password' ] );

        // Directory search
        add_action( 'wp_ajax_dd_search', [ $this, 'search_dogs' ] );
        add_action( 'wp_ajax_nopriv_dd_search', [ $this, 'search_dogs' ] );

        // Image upload
        add_action( 'wp_ajax_dd_upload_image', [ $this, 'upload_image' ] );

        // Admin actions
        add_action( 'wp_ajax_dd_admin_approve_dog', [ $this, 'admin_approve_dog' ] );
        add_action( 'wp_ajax_dd_admin_reject_dog', [ $this, 'admin_reject_dog' ] );
        add_action( 'wp_ajax_dd_admin_update_plan', [ $this, 'admin_update_plan' ] );
        add_action( 'wp_ajax_dd_admin_toggle_sponsored', [ $this, 'admin_toggle_sponsored' ] );
        add_action( 'wp_ajax_dd_get_user_drawer', [ $this, 'get_user_drawer' ] );
    }

    // =========================================================
    // DOG CRUD
    // =========================================================

    public function save_dog() {
        check_ajax_referer( 'dd_dog_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( ['message' => __('You must be logged in.', 'petslist')] );
        }
        if ( ! Subscription::can_access_directory() ) {
            wp_send_json_error( ['message' => __('An active subscription is required to add dogs.', 'petslist'), 'subscribe' => true] );
        }

        $user_id = get_current_user_id();
        $post_id = absint( $_POST['post_id'] ?? 0 );
        $data    = $_POST['dog_data'] ?? [];

        // Sanitise
        $title   = sanitize_text_field( $data['dog_name'] ?? '' );
        $content = wp_kses_post( $data['description'] ?? '' );

        if ( empty( $title ) ) {
            wp_send_json_error( ['message' => __('Dog name is required.', 'petslist')] );
        }

        // Ownership check for edits
        if ( $post_id ) {
            $existing = get_post( $post_id );
            if ( ! $existing || ( (int) $existing->post_author !== $user_id && ! current_user_can('manage_options') ) ) {
                wp_send_json_error( ['message' => __('Permission denied.', 'petslist')] );
            }
        }

        $post_data = [
            'post_title'   => $title,
            'post_content' => $content,
            'post_type'    => 'dd_dog',
            'post_status'  => 'publish',
            'post_author'  => $user_id,
        ];

        if ( $post_id ) {
            $post_data['ID'] = $post_id;
            $result = wp_update_post( $post_data, true );
        } else {
            $result = wp_insert_post( $post_data, true );
        }

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( ['message' => $result->get_error_message()] );
        }

        // Save meta
        $meta_fields = ['breed','gender','dob','color','weight','registration_no','country','city','contact_phone','contact_email','contact_website'];
        $meta        = [];
        foreach ( $meta_fields as $field ) {
            $meta[$field] = sanitize_text_field( $data[$field] ?? '' );
        }
        $meta['dog_name'] = $title;
        update_post_meta( $result, '_dd_dog_meta', $meta );

        // Health data
        if ( ! empty( $data['health'] ) && is_array( $data['health'] ) ) {
            $health = array_map( 'sanitize_textarea_field', $data['health'] );
            update_post_meta( $result, '_dd_dog_health', $health );
        }

        // Front/side photos
        if ( ! empty( $data['front_photo'] ) ) {
            update_post_meta( $result, '_dd_front_photo', absint($data['front_photo']) );
        }
        if ( ! empty( $data['side_photo'] ) ) {
            update_post_meta( $result, '_dd_side_photo', absint($data['side_photo']) );
        }
        if ( ! empty( $data['thumbnail_id'] ) ) {
            set_post_thumbnail( $result, absint($data['thumbnail_id']) );
        }

        // Breed taxonomy
        if ( ! empty( $data['breed'] ) ) {
            $breed_name = dd_match_breed_name( $data['breed'] );
            $meta['breed'] = $breed_name;
            update_post_meta( $result, '_dd_dog_meta', $meta );

            $term = term_exists( $breed_name, 'dd_breed' );
            if ( ! $term ) {
                $term = wp_insert_term( $breed_name, 'dd_breed' );
            }
            if ( ! is_wp_error( $term ) ) {
                wp_set_post_terms( $result, array( (int) $term['term_id'] ), 'dd_breed', false );
            }
        }

        wp_send_json_success( [
            'message'    => $post_id ? __('Dog updated successfully!', 'petslist') : __('Dog added! Pending approval.', 'petslist'),
            'post_id'    => $result,
            'edit_url'   => dd_dashboard_url( 'dogs' ),
            'view_url'   => get_permalink( $result ),
        ] );
    }

    public function delete_dog() {
        check_ajax_referer( 'dd_dog_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) wp_send_json_error(['message' => __('Not logged in.', 'petslist')]);

        $post_id = absint( $_POST['post_id'] ?? 0 );
        $user_id = get_current_user_id();
        $post    = get_post( $post_id );

        if ( ! $post || $post->post_type !== 'dd_dog' ) {
            wp_send_json_error(['message' => __('Dog not found.', 'petslist')]);
        }
        if ( (int) $post->post_author !== $user_id && ! current_user_can('manage_options') ) {
            wp_send_json_error(['message' => __('Permission denied.', 'petslist')]);
        }

        wp_delete_post( $post_id, true );
        wp_send_json_success(['message' => __('Dog deleted.', 'petslist')]);
    }

    public function get_dog() {
        check_ajax_referer( 'dd_dog_nonce', 'nonce' );
        $post_id = absint( $_POST['post_id'] ?? 0 );
        $post    = get_post( $post_id );

        if ( ! $post || $post->post_type !== 'dd_dog' ) {
            wp_send_json_error(['message' => __('Dog not found.', 'petslist')]);
        }
        if ( (int) $post->post_author !== get_current_user_id() && ! current_user_can('manage_options') ) {
            wp_send_json_error(['message' => __('Permission denied.', 'petslist')]);
        }

        $meta   = get_post_meta( $post_id, '_dd_dog_meta', true ) ?: [];
        $health = get_post_meta( $post_id, '_dd_dog_health', true ) ?: [];

        wp_send_json_success([
            'post_id'     => $post_id,
            'title'       => $post->post_title,
            'content'     => $post->post_content,
            'meta'        => $meta,
            'health'      => $health,
            'front_photo' => get_post_meta( $post_id, '_dd_front_photo', true ),
            'side_photo'  => get_post_meta( $post_id, '_dd_side_photo', true ),
            'thumbnail'   => get_post_thumbnail_id( $post_id ),
        ]);
    }

    public function get_dog_drawer() {
        check_ajax_referer( 'dd_dog_nonce', 'nonce' );
        $post_id = absint( $_POST['post_id'] ?? 0 );
        $post    = get_post( $post_id );

        if ( ! $post || $post->post_type !== 'dd_dog' ) {
            wp_send_json_error(['message' => __('Dog not found.', 'petslist')]);
        }
        if ( (int) $post->post_author !== get_current_user_id() && ! current_user_can('manage_options') ) {
            wp_send_json_error(['message' => __('Permission denied.', 'petslist')]);
        }

        $meta   = dd_get_dog_meta($post_id);
        $health = dd_get_dog_health($post_id);
        $age    = dd_get_dog_age($meta['dob'] ?? '');
        
        $front_url = dd_get_front_photo_url($post_id, 'large');
        $side_url  = dd_get_side_photo_url($post_id, 'large');
        $thumb_url = get_the_post_thumbnail_url($post_id, 'large') ?: dd_placeholder_image();
        
        $status_map = [
            'publish' => [ 'label' => __( 'Live', 'petslist' ), 'class' => 'active' ],
            'pending' => [ 'label' => __( 'Pending', 'petslist' ), 'class' => 'pending' ],
            'draft'   => [ 'label' => __( 'Draft', 'petslist' ), 'class' => 'draft' ],
        ];
        $st_info = $status_map[$post->post_status] ?? ['label' => ucfirst($post->post_status), 'class' => 'draft'];

        ob_start();
        ?>
        <div class="dd-drawer-profile">
            <!-- Header/Photo Cover -->
            <div class="dd-drawer-profile__cover">
                <img src="<?php echo esc_url($front_url ?: $thumb_url); ?>" alt="<?php echo esc_attr($post->post_title); ?>" id="dd-drawer-main-photo">
                <div class="dd-drawer-profile__gallery-nav">
                    <button class="dd-drawer-gallery-btn active" data-src="<?php echo esc_url($front_url ?: $thumb_url); ?>"><?php _e('Front', 'petslist'); ?></button>
                    <?php if ( $side_url && $side_url !== dd_placeholder_image() ) : ?>
                    <button class="dd-drawer-gallery-btn" data-src="<?php echo esc_url($side_url); ?>"><?php _e('Side', 'petslist'); ?></button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Header Info -->
            <div class="dd-drawer-profile__header">
                <h3>
                    <a href="<?php echo esc_url(get_permalink($post_id)); ?>" target="_blank" style="color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <?php echo esc_html($post->post_title); ?>
                        <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 11px; opacity: 0.5;"></i>
                    </a>
                </h3>
                <div class="dd-drawer-profile__badges">
                    <?php if (!empty($meta['gender'])) : ?>
                    <span class="dd-gender-tag dd-gender-tag--<?php echo strtolower($meta['gender']); ?>">
                        <?php echo $meta['gender'] === 'Male' ? '♂' : '♀'; ?> <?php echo esc_html($meta['gender']); ?>
                    </span>
                    <?php endif; ?>
                    <span class="dd-status-pill dd-status-pill--<?php echo $st_info['class']; ?>"><?php echo $st_info['label']; ?></span>
                </div>
            </div>

            <!-- Profile Details Grid -->
            <div class="dd-drawer-profile__section">
                <h4><?php _e('Dog Profile', 'petslist'); ?></h4>
                <div class="dd-drawer-grid">
                    <div class="dd-drawer-item">
                        <span class="dd-drawer-label"><?php _e('Breed', 'petslist'); ?></span>
                        <span class="dd-drawer-value"><?php echo esc_html($meta['breed'] ?: '—'); ?></span>
                    </div>
                    <div class="dd-drawer-item">
                        <span class="dd-drawer-label"><?php _e('Date of Birth', 'petslist'); ?></span>
                        <span class="dd-drawer-value"><?php echo esc_html($meta['dob'] ?: '—'); ?><?php if ($age) echo " ({$age})"; ?></span>
                    </div>
                    <div class="dd-drawer-item">
                        <span class="dd-drawer-label"><?php _e('Color', 'petslist'); ?></span>
                        <span class="dd-drawer-value"><?php echo esc_html($meta['color'] ?: '—'); ?></span>
                    </div>
                    <div class="dd-drawer-item">
                        <span class="dd-drawer-label"><?php _e('Weight', 'petslist'); ?></span>
                        <span class="dd-drawer-value"><?php echo !empty($meta['weight']) ? esc_html($meta['weight'] . ' kg') : '—'; ?></span>
                    </div>
                    <div class="dd-drawer-item">
                        <span class="dd-drawer-label"><?php _e('Registration No.', 'petslist'); ?></span>
                        <span class="dd-drawer-value"><?php echo esc_html($meta['registration_no'] ?: '—'); ?></span>
                    </div>
                    <div class="dd-drawer-item">
                        <span class="dd-drawer-label"><?php _e('Location', 'petslist'); ?></span>
                        <span class="dd-drawer-value">
                            <?php 
                            $loc = [];
                            if (!empty($meta['city'])) $loc[] = $meta['city'];
                            if (!empty($meta['country'])) $loc[] = $meta['country'];
                            echo esc_html(implode(', ', $loc) ?: '—');
                            ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <?php if ( ! empty($post->post_content) ) : ?>
            <div class="dd-drawer-profile__section">
                <h4><?php _e('About This Dog', 'petslist'); ?></h4>
                <div class="dd-drawer-content">
                    <?php echo wpautop(esc_html($post->post_content)); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Health & Pedigree -->
            <?php if ( array_filter($health) ) : ?>
            <div class="dd-drawer-profile__section">
                <h4><?php _e('Health & Pedigree', 'petslist'); ?></h4>
                <div class="dd-drawer-health">
                    <?php if ( ! empty($health['health_clearances']) ) : ?>
                    <div class="dd-drawer-health-item">
                        <strong>🩺 <?php _e('Health Clearances', 'petslist'); ?></strong>
                        <p><?php echo nl2br(esc_html($health['health_clearances'])); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ( ! empty($health['vaccinations']) ) : ?>
                    <div class="dd-drawer-health-item">
                        <strong>💉 <?php _e('Vaccinations', 'petslist'); ?></strong>
                        <p><?php echo nl2br(esc_html($health['vaccinations'])); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ( ! empty($health['pedigree']) ) : ?>
                    <div class="dd-drawer-health-item">
                        <strong>📜 <?php _e('Pedigree', 'petslist'); ?></strong>
                        <p><?php echo nl2br(esc_html($health['pedigree'])); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ( ! empty($health['awards']) ) : ?>
                    <div class="dd-drawer-health-item">
                        <strong>🏆 <?php _e('Awards & Titles', 'petslist'); ?></strong>
                        <p><?php echo nl2br(esc_html($health['awards'])); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ( ! empty($health['microchip']) ) : ?>
                    <div class="dd-drawer-health-item">
                        <strong>📡 <?php _e('Microchip', 'petslist'); ?></strong>
                        <p><?php echo nl2br(esc_html($health['microchip'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
        $html = ob_get_clean();
        wp_send_json_success(['html' => $html]);
    }

    public function get_user_drawer() {
        check_ajax_referer( 'dd_dog_nonce', 'nonce' );
        if ( ! current_user_can('manage_options') ) {
            wp_send_json_error(['message' => __('Permission denied.', 'petslist')]);
        }
        $user_id = absint( $_POST['user_id'] ?? 0 );
        $user    = get_userdata( $user_id );

        if ( ! $user ) {
            wp_send_json_error(['message' => __('User not found.', 'petslist')]);
        }

        global $wpdb;
        $sub = $wpdb->get_row($wpdb->prepare("
            SELECT s.*, p.name as plan_name, p.price
            FROM {$wpdb->prefix}dd_subscriptions s
            LEFT JOIN {$wpdb->prefix}dd_plans p ON s.plan_id = p.id
            WHERE s.user_id = %d AND s.status = 'active'
            LIMIT 1
        ", $user_id));

        $dogs = dd_get_user_dogs($user_id, 'any', -1);

        ob_start();
        ?>
        <div class="dd-drawer-profile">
            <!-- Header Cover -->
            <div class="dd-drawer-profile__cover" style="height:120px; background: linear-gradient(135deg, #02c5bd 0%, #02a39d 100%); display:flex; align-items:center; justify-content:center; position:relative;">
                <div style="border: 4px solid #ffffff; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.1); overflow:hidden; background:#fff; position:absolute; bottom:-40px; left:50%; transform:translateX(-50%); width:80px; height:80px;">
                    <?php echo get_avatar($user_id, 80, '', '', ['style'=>'display:block; width:100%; height:100%; object-fit:cover;']); ?>
                </div>
            </div>

            <!-- Header Info -->
            <div class="dd-drawer-profile__header" style="margin-top:50px; text-align:center; padding: 0 20px;">
                <h3 style="margin-bottom:6px; font-size: 18px; font-weight: 700; color: #0f172a;"><?php echo esc_html($user->display_name); ?></h3>
                <div style="font-size:13px; color:#64748b; margin-bottom:12px; word-break: break-all;"><?php echo esc_html($user->user_email); ?></div>
                <div class="dd-drawer-profile__badges" style="justify-content:center; display:flex; gap:6px;">
                    <?php if ( $sub ) : ?>
                    <span class="ddu-pill ddu-pill--active" style="font-size: 11px; padding: 4px 10px;"><?php echo esc_html($sub->plan_name); ?></span>
                    <?php else : ?>
                    <span class="ddu-pill ddu-pill--draft" style="font-size: 11px; padding: 4px 10px;"><?php _e('Free Account', 'petslist'); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- User Info Section -->
            <div class="dd-drawer-profile__section" style="padding: 20px; border-bottom: 1px solid #edf2f7;">
                <h4 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;"><?php _e('Account details', 'petslist'); ?></h4>
                <div class="dd-drawer-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="dd-drawer-item">
                        <span class="dd-drawer-label" style="display:block; font-size:11px; color:#64748b; text-transform:uppercase; margin-bottom:2px;"><?php _e('Role', 'petslist'); ?></span>
                        <span class="dd-drawer-value" style="font-size:13px; font-weight:600; color:#334155; text-transform: capitalize;"><?php echo esc_html(implode(', ', $user->roles)); ?></span>
                    </div>
                    <div class="dd-drawer-item">
                        <span class="dd-drawer-label" style="display:block; font-size:11px; color:#64748b; text-transform:uppercase; margin-bottom:2px;"><?php _e('Joined', 'petslist'); ?></span>
                        <span class="dd-drawer-value" style="font-size:13px; font-weight:600; color:#334155;"><?php echo date('M j, Y', strtotime($user->user_registered)); ?></span>
                    </div>
                    <?php if ( $sub ) : ?>
                    <div class="dd-drawer-item">
                        <span class="dd-drawer-label" style="display:block; font-size:11px; color:#64748b; text-transform:uppercase; margin-bottom:2px;"><?php _e('Expires', 'petslist'); ?></span>
                        <span class="dd-drawer-value" style="font-size:13px; font-weight:600; color:#334155;"><?php echo date('M j, Y', strtotime($sub->expires_at)); ?></span>
                    </div>
                    <div class="dd-drawer-item">
                        <span class="dd-drawer-label" style="display:block; font-size:11px; color:#64748b; text-transform:uppercase; margin-bottom:2px;"><?php _e('Price', 'petslist'); ?></span>
                        <span class="dd-drawer-value" style="font-size:13px; font-weight:600; color:#334155;">$<?php echo number_format($sub->price, 2); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- User's Dogs Section -->
            <div class="dd-drawer-profile__section" style="padding: 20px;">
                <h4 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;"><?php printf(__('Dogs owned (%d)', 'petslist'), count($dogs)); ?></h4>
                <?php if ( ! empty($dogs) ) : ?>
                <div style="display:flex; flex-direction:column; gap:10px; margin-top:10px;">
                    <?php foreach ( $dogs as $dog ) : 
                        $thumb = get_the_post_thumbnail_url($dog->ID, 'thumbnail') ?: dd_placeholder_image();
                        $meta = dd_get_dog_meta($dog->ID);
                        $gender = $meta['gender'] ?? '';
                    ?>
                    <a href="<?php echo esc_url(get_permalink($dog->ID)); ?>" target="_blank" style="display:flex; align-items:center; gap:12px; padding:10px; border:1px solid #edf2f7; border-radius:8px; background:#f8fafc; text-decoration:none; transition: background 0.15s ease;" onmouseover="this.style.background='#f1f5f9';" onmouseout="this.style.background='#f8fafc';">
                        <img src="<?php echo esc_url($thumb); ?>" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:1px solid #e2e8f0; flex-shrink:0;" />
                        <div style="flex:1; min-width:0;">
                            <strong style="display:block; font-size:13px; color:#0f172a; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;"><?php echo esc_html($dog->post_title); ?></strong>
                            <span style="font-size:11px; color:#64748b;"><?php echo esc_html($meta['breed'] ?? '—'); ?></span>
                        </div>
                        <?php if ( $gender ) : ?>
                        <span class="dd-gender-tag dd-gender-tag--<?php echo strtolower($gender); ?>" style="font-size: 10px; padding: 2px 8px;">
                            <?php echo $gender === 'Male' ? '♂' : '♀'; ?> <?php echo esc_html($gender); ?>
                        </span>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php else : ?>
                <div style="text-align:center; padding:20px; color:#94a3b8; font-size:13px;"><?php _e('No dogs submitted yet.', 'petslist'); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        $html = ob_get_clean();
        wp_send_json_success(['html' => $html]);
    }

    // =========================================================
    // AUTH
    // =========================================================

    public function register_user() {
        check_ajax_referer( 'dd_auth_nonce', 'nonce' );

        $name     = sanitize_text_field( $_POST['name'] ?? '' );
        $email    = sanitize_email( $_POST['email'] ?? '' );
        $password = $_POST['password'] ?? '';

        if ( empty($name) || empty($email) || empty($password) ) {
            wp_send_json_error(['message' => __('All fields are required.', 'petslist')]);
        }
        if ( ! is_email($email) ) {
            wp_send_json_error(['message' => __('Invalid email address.', 'petslist')]);
        }
        if ( email_exists($email) ) {
            wp_send_json_error(['message' => __('Email already registered.', 'petslist')]);
        }
        if ( strlen($password) < 8 ) {
            wp_send_json_error(['message' => __('Password must be at least 8 characters.', 'petslist')]);
        }

        $user_id = wp_create_user( $email, $password, $email );
        if ( is_wp_error($user_id) ) {
            wp_send_json_error(['message' => $user_id->get_error_message()]);
        }

        wp_update_user(['ID' => $user_id, 'display_name' => $name, 'first_name' => $name]);
        $u = new \WP_User( $user_id );
        $u->set_role( 'dd_subscriber' );
        update_user_meta( $user_id, 'dd_member_since', current_time('mysql') );

        // Send verification email
        wp_new_user_notification( $user_id, null, 'user' );

        // Auto login
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );

        // Determine redirect: if redirect_to param is set (e.g. from checkout link), use that; else pricing
        $redirect_to = esc_url_raw( $_POST['redirect_to'] ?? '' );
        if ( empty( $redirect_to ) || ! wp_validate_redirect( $redirect_to, false ) ) {
            $redirect_to = dd_pricing_url();
        }

        wp_send_json_success([
            'message'  => __('Account created! Welcome to Dog Directory.', 'petslist'),
            'redirect' => $redirect_to,
        ]);
    }

    public function login_user() {
        check_ajax_referer( 'dd_auth_nonce', 'nonce' );

        $email    = sanitize_email( $_POST['email'] ?? '' );
        $password = $_POST['password'] ?? '';
        $remember = (bool) ( $_POST['remember'] ?? false );

        if ( empty($email) || empty($password) ) {
            wp_send_json_error(['message' => __('Email and password are required.', 'petslist')]);
        }

        $creds = [
            'user_login'    => $email,
            'user_password' => $password,
            'remember'      => $remember,
        ];

        $user = wp_signon( $creds, is_ssl() );
        if ( is_wp_error($user) ) {
            wp_send_json_error(['message' => __('Invalid credentials. Please try again.', 'petslist')]);
        }

        // Respect explicit redirect_to param (e.g. when gated page sent user to login)
        $redirect_to = esc_url_raw( $_POST['redirect_to'] ?? '' );
        if ( ! empty($redirect_to) && wp_validate_redirect( $redirect_to, false ) ) {
            $final_redirect = $redirect_to;
        } else {
            $final_redirect = dd_dashboard_url();
        }

        wp_send_json_success([
            'message'  => __('Welcome back!', 'petslist'),
            'redirect' => $final_redirect,
        ]);
    }

    public function update_profile() {
        check_ajax_referer( 'dd_dashboard_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) wp_send_json_error(['message' => __('Not logged in.', 'petslist')]);

        $user_id = get_current_user_id();
        $name    = sanitize_text_field( $_POST['display_name'] ?? '' );
        $bio     = sanitize_textarea_field( $_POST['bio'] ?? '' );
        $phone   = sanitize_text_field( $_POST['phone'] ?? '' );
        $website = esc_url_raw( $_POST['website'] ?? '' );
        $avatar  = absint( $_POST['avatar_id'] ?? 0 );

        $result = wp_update_user([
            'ID'           => $user_id,
            'display_name' => $name,
            'description'  => $bio,
        ]);

        if ( is_wp_error($result) ) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        update_user_meta($user_id, 'dd_phone', $phone);
        update_user_meta($user_id, 'user_url', $website);
        
        if ( $avatar ) {
            update_user_meta($user_id, '_rtcl_pp_id', $avatar);
        } else {
            delete_user_meta($user_id, '_rtcl_pp_id');
        }

        wp_send_json_success(['message' => __('Profile updated successfully!', 'petslist')]);
    }

    public function change_password() {
        check_ajax_referer( 'dd_dashboard_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) wp_send_json_error(['message' => __('Not logged in.', 'petslist')]);

        $user_id  = get_current_user_id();
        $current  = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        $user = get_user_by('id', $user_id);
        if ( ! wp_check_password($current, $user->data->user_pass, $user_id) ) {
            wp_send_json_error(['message' => __('Current password is incorrect.', 'petslist')]);
        }
        if ( strlen($new_pass) < 8 ) {
            wp_send_json_error(['message' => __('New password must be at least 8 characters.', 'petslist')]);
        }
        if ( $new_pass !== $confirm ) {
            wp_send_json_error(['message' => __('Passwords do not match.', 'petslist')]);
        }

        wp_set_password($new_pass, $user_id);
        wp_send_json_success(['message' => __('Password changed. Please log in again.', 'petslist'), 'logout' => true]);
    }

    public function forgot_password() {
        check_ajax_referer( 'dd_auth_nonce', 'nonce' );
        $email = sanitize_email( $_POST['email'] ?? '' );
        if ( ! $email || ! email_exists($email) ) {
            wp_send_json_error(['message' => __('Email not found.', 'petslist')]);
        }
        $result = retrieve_password( $email );
        if ( is_wp_error($result) ) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        wp_send_json_success(['message' => __('Password reset link sent to your email.', 'petslist')]);
    }

    // =========================================================
    // SEARCH
    // =========================================================

    public function search_dogs() {
        $breed   = sanitize_text_field( $_POST['breed'] ?? '' );
        $gender  = sanitize_text_field( $_POST['gender'] ?? '' );
        $country = sanitize_text_field( $_POST['country'] ?? '' );
        $city    = sanitize_text_field( $_POST['city'] ?? '' );
        $age_min = absint( $_POST['age_min'] ?? 0 );
        $age_max = absint( $_POST['age_max'] ?? 0 );
        $reg_no  = sanitize_text_field( $_POST['registration_no'] ?? '' );
        $keyword = sanitize_text_field( $_POST['keyword'] ?? '' );
        $orderby = sanitize_text_field( $_POST['orderby'] ?? 'date' );
        $order   = sanitize_text_field( $_POST['order'] ?? 'DESC' );
        $paged   = max(1, absint( $_POST['paged'] ?? 1 ));

        // Gate: logged-in subscribers only see full results
        $is_subscriber = Subscription::can_access_directory();

        $args = [
            'post_type'      => 'dd_dog',
            'post_status'    => 'publish',
            'posts_per_page' => 12,
            'paged'          => $paged,
        ];

        // Meta query
        $meta_query = ['relation' => 'AND'];
        if ( $gender )  $meta_query[] = ['key' => '_dd_dog_meta', 'value' => '"gender":"'.$gender.'"', 'compare' => 'LIKE'];
        if ( $country ) $meta_query[] = ['key' => '_dd_dog_meta', 'value' => '"country":"'.$country.'"', 'compare' => 'LIKE'];
        if ( $city )    $meta_query[] = ['key' => '_dd_dog_meta', 'value' => '"city":"'.$city.'"', 'compare' => 'LIKE'];
        if ( $reg_no )  $meta_query[] = ['key' => '_dd_dog_meta', 'value' => '"registration_no":"'.$reg_no.'"', 'compare' => 'LIKE'];
        if ( count($meta_query) > 1 ) $args['meta_query'] = $meta_query;

        // Taxonomy
        $tax_query = [];
        if ( $breed ) {
            $tax_query[] = ['taxonomy' => 'dd_breed', 'field' => 'name', 'terms' => $breed];
        }
        if ( $tax_query ) $args['tax_query'] = $tax_query;

        // Keyword search
        if ( $keyword ) $args['s'] = $keyword;

        // Order
        $allowed_order = ['date','title','modified','rand'];
        $args['orderby'] = in_array($orderby, $allowed_order) ? $orderby : 'date';
        $args['order']   = $order === 'ASC' ? 'ASC' : 'DESC';

        $query = new \WP_Query( $args );
        $dogs  = [];

        while ( $query->have_posts() ) {
            $query->the_post();
            $pid  = get_the_ID();
            $meta = get_post_meta($pid, '_dd_dog_meta', true) ?: [];

            $dog_data = [
                'id'          => $pid,
                'title'       => get_the_title(),
                'permalink'   => get_permalink(),
                'thumbnail'   => get_the_post_thumbnail_url($pid, 'medium') ?: dd_placeholder_image(),
                'breed'       => $meta['breed'] ?? '',
                'gender'      => $meta['gender'] ?? '',
                'color'       => $meta['color'] ?? '',
                'country'     => $meta['country'] ?? '',
                'city'        => $meta['city'] ?? '',
                'dob'         => $meta['dob'] ?? '',
                'weight'      => $meta['weight'] ?? '',
                'reg_no'      => $is_subscriber ? ($meta['registration_no'] ?? '') : '',
                'contact'     => $is_subscriber ? ['phone' => $meta['contact_phone'] ?? '', 'email' => $meta['contact_email'] ?? ''] : null,
                'owner'       => get_the_author(),
                'date'        => get_the_date(),
            ];
            $dogs[] = $dog_data;
        }
        wp_reset_postdata();

        wp_send_json_success([
            'dogs'        => $dogs,
            'total'       => $query->found_posts,
            'pages'       => $query->max_num_pages,
            'current'     => $paged,
            'subscriber'  => $is_subscriber,
        ]);
    }

    // =========================================================
    // IMAGE UPLOAD
    // =========================================================

    public function upload_image() {
        check_ajax_referer( 'dd_upload_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) wp_send_json_error(['message' => __('Not logged in.', 'petslist')]);
        if ( ! Subscription::can_access_directory() ) wp_send_json_error(['message' => __('Subscription required.', 'petslist')]);

        if ( empty($_FILES['file']) ) wp_send_json_error(['message' => __('No file uploaded.', 'petslist')]);

        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $attachment_id = media_handle_upload('file', 0);
        if ( is_wp_error($attachment_id) ) {
            wp_send_json_error(['message' => $attachment_id->get_error_message()]);
        }

        wp_send_json_success([
            'attachment_id' => $attachment_id,
            'url'           => wp_get_attachment_url($attachment_id),
            'thumbnail'     => wp_get_attachment_image_url($attachment_id, 'thumbnail'),
        ]);
    }

    // =========================================================
    // ADMIN
    // =========================================================

    public function admin_approve_dog() {
        check_ajax_referer('dd_admin_nonce', 'nonce');
        if ( ! current_user_can('manage_options') ) wp_send_json_error(['message' => 'No permission.']);
        $post_id = absint($_POST['post_id'] ?? 0);
        wp_update_post(['ID' => $post_id, 'post_status' => 'publish']);
        wp_send_json_success(['message' => 'Dog approved.']);
    }

    public function admin_reject_dog() {
        check_ajax_referer('dd_admin_nonce', 'nonce');
        if ( ! current_user_can('manage_options') ) wp_send_json_error(['message' => 'No permission.']);
        $post_id = absint($_POST['post_id'] ?? 0);
        wp_update_post(['ID' => $post_id, 'post_status' => 'draft']);
        wp_send_json_success(['message' => 'Dog rejected.']);
    }

    public function admin_update_plan() {
        check_ajax_referer('dd_admin_nonce', 'nonce');
        if ( ! current_user_can('manage_options') ) wp_send_json_error(['message' => 'No permission.']);

        global $wpdb;
        $plan_id = absint($_POST['plan_id'] ?? 0);
        $table   = $wpdb->prefix . 'dd_plans';
        $data    = [
            'name'      => sanitize_text_field($_POST['name'] ?? ''),
            'price'     => floatval($_POST['price'] ?? 0),
            'duration'  => absint($_POST['duration'] ?? 30),
            'is_active' => absint($_POST['is_active'] ?? 1),
        ];
        if ( isset($_POST['features']) ) {
            $data['features'] = json_encode( array_map('sanitize_text_field', (array)$_POST['features']) );
        }
        $wpdb->update($table, $data, ['id' => $plan_id]);
        wp_send_json_success(['message' => 'Plan updated.']);
    }

    public function admin_toggle_sponsored() {
        check_ajax_referer('dd_dog_nonce', 'nonce');
        if ( ! current_user_can('manage_options') ) wp_send_json_error(['message' => 'No permission.']);
        $post_id = absint($_POST['post_id'] ?? 0);
        $meta = get_post_meta($post_id, '_dd_dog_meta', true) ?: [];
        $is_sponsored = isset($meta['is_sponsored']) && $meta['is_sponsored'] === 'Yes';
        $meta['is_sponsored'] = $is_sponsored ? 'No' : 'Yes';
        update_post_meta($post_id, '_dd_dog_meta', $meta);
        wp_send_json_success([
            'message' => $is_sponsored ? 'Dog unmarked as sponsored.' : 'Dog marked as sponsored.',
            'is_sponsored' => !$is_sponsored
        ]);
    }
}
