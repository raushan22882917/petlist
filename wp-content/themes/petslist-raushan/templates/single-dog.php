<?php
/**
 * Dog Directory - Single Dog Profile Template
 * Shows full stud profile details matching the admin dashboard.
 * @package Petslist Dog Directory
 */

use RadiusTheme\Petslist\DogDirectory\Subscription;

get_header();

$post_id       = get_the_ID();
$meta          = dd_get_dog_meta($post_id);
$health        = dd_get_dog_health($post_id);
$is_subscriber = Subscription::can_access_directory();
$is_owner      = is_user_logged_in() && (int) get_post_field('post_author', $post_id) === get_current_user_id();
$is_admin      = current_user_can('manage_options');

$front_url     = dd_get_front_photo_url($post_id, 'large');
$side_url      = dd_get_side_photo_url($post_id, 'large');
$thumb_url     = get_the_post_thumbnail_url($post_id, 'large') ?: dd_placeholder_image();
$age           = dd_get_dog_age($meta['dob'] ?? '');
$breed_terms   = get_the_terms($post_id, 'dd_breed');
$breed_name    = $breed_terms && ! is_wp_error($breed_terms) ? $breed_terms[0]->name : ($meta['breed'] ?? '');
$owner_id      = (int) get_post_field('post_author', $post_id);
$owner         = get_userdata($owner_id);

// Health data fallbacks (sync with both _dd_dog_health and _dd_dog_meta)
$health_clearances = $health['health_clearances'] ?? ($meta['health_testing'] ?? '');
$vaccinations      = $health['vaccinations'] ?? '';
$pedigree          = $health['pedigree'] ?? ($meta['pedigree'] ?? '');
$awards            = $health['awards'] ?? '';
$microchip         = $health['microchip'] ?? '';

// Location resolution
$city_val    = $meta['city'] ?? '';
$state_val   = $meta['state_province'] ?? ( $meta['state'] ?? '' );
$country_val = $meta['country'] ?? ( $meta['dog_country'] ?? '' );
$all_countries_list = function_exists('dd_get_all_countries') ? array_keys(dd_get_all_countries()) : [];
if ( empty($state_val) && $country_val && ! in_array($country_val, $all_countries_list) ) {
    $state_val = $country_val;
    $country_val = '';
}
$full_state  = function_exists('dd_get_state_full_name') ? dd_get_state_full_name($state_val) : $state_val;
$loc_display = trim(implode(', ', array_filter([$city_val, $full_state, $country_val])));

// Gallery IDs
$gallery_ids   = get_post_meta($post_id, '_dd_gallery', true) ?: [];
$is_sponsored  = isset( $meta['is_sponsored'] ) && $meta['is_sponsored'] === 'Yes';
$has_health    = ! empty($health_clearances) || ! empty($vaccinations) || ! empty($pedigree);
?>

<div class="dd-single-page content-area">
<div class="container">

    <!-- Breadcrumb -->
    <nav class="dd-breadcrumb" style="margin-bottom: 24px; font-size: 14px; display: flex; align-items: center; gap: 8px; color: #64748b;">
        <a href="<?php echo home_url('/'); ?>" style="color: #64748b; text-decoration: none;"><?php _e('Home', 'petslist'); ?></a>
        <i class="icon-pl-angle-down-fat" style="font-size: 10px;"></i>
        <a href="<?php echo esc_url(dd_dog_directory_url()); ?>" style="color: #64748b; text-decoration: none;"><?php _e('Dog Directory', 'petslist'); ?></a>
        <i class="icon-pl-angle-down-fat" style="font-size: 10px;"></i>
        <span style="color: #0f172a; font-weight: 600;"><?php the_title(); ?></span>
    </nav>

    <div class="dd-single-layout">

        <!-- LEFT: Main Content -->
        <main class="dd-single-main">

            <!-- Hero Gallery -->
            <div class="dd-single-gallery">
                <div class="dd-gallery-main" id="dd-gallery-main">
                    <img src="<?php echo esc_url($front_url ?: $thumb_url); ?>" alt="<?php printf(__('%s - Front View', 'petslist'), get_the_title()); ?>" id="dd-main-photo" class="dd-gallery-main__img">
                    <div class="dd-gallery-view-label" id="dd-photo-label"><?php _e('Front View', 'petslist'); ?></div>
                </div>
                <div class="dd-gallery-thumbs">
                    <div class="dd-gallery-thumb dd-gallery-thumb--active" data-src="<?php echo esc_url($front_url ?: $thumb_url); ?>" data-label="<?php esc_attr_e('Front View', 'petslist'); ?>">
                        <img src="<?php echo esc_url($front_url ?: $thumb_url); ?>" alt="Front">
                        <span><?php _e('Front', 'petslist'); ?></span>
                    </div>
                    <?php if ( $side_url && $side_url !== dd_placeholder_image() ) : ?>
                    <div class="dd-gallery-thumb" data-src="<?php echo esc_url($side_url); ?>" data-label="<?php esc_attr_e('Side View', 'petslist'); ?>">
                        <img src="<?php echo esc_url($side_url); ?>" alt="Side">
                        <span><?php _e('Side', 'petslist'); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php foreach ( $gallery_ids as $gid ) : 
                        $gurl = wp_get_attachment_image_url($gid, 'medium'); 
                        if ( ! $gurl ) continue; 
                        $full_gurl = wp_get_attachment_image_url($gid, 'large') ?: $gurl;
                    ?>
                    <div class="dd-gallery-thumb" data-src="<?php echo esc_url($full_gurl); ?>" data-label="<?php esc_attr_e('Gallery Photo', 'petslist'); ?>">
                        <img src="<?php echo esc_url($gurl); ?>" alt="Gallery">
                        <span><?php _e('Photo', 'petslist'); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Dog Title & Quick Badges -->
            <div class="dd-single-head" style="margin-top: 24px; margin-bottom: 24px;">
                <div class="dd-single-head__left">
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 8px;">
                        <h1 class="dd-single-head__name" style="margin: 0; font-size: 28px; font-weight: 700; color: #070c3e;">
                            <?php the_title(); ?>
                        </h1>
                        <?php if ( ! empty($meta['gender']) ) : ?>
                        <span class="dd-gender-badge dd-gender-badge--<?php echo strtolower($meta['gender']); ?>" style="padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 700;">
                            <?php echo $meta['gender'] === 'Male' ? '♂ Male' : '♀ Female'; ?>
                        </span>
                        <?php endif; ?>
                        <?php if ( $is_sponsored ) : ?>
                        <span style="background: #fef9c3; color: #854d0e; border: 1px solid #fef08a; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                            <i class="fa-solid fa-star" style="color: #eab308;"></i> <?php _e('Sponsored Stud', 'petslist'); ?>
                        </span>
                        <?php endif; ?>
                        <?php if ( $has_health ) : ?>
                        <span style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                            <i class="fa-solid fa-circle-check"></i> <?php _e('Health Verified', 'petslist'); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php if ( $breed_name || $loc_display ) : ?>
                    <div class="dd-single-head__meta" style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-top: 6px;">
                        <?php if ( $breed_name ) : ?>
                        <div class="dd-single-head__breed" style="display: inline-flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 600; color: #02c5bd;">
                            <i class="fa-solid fa-paw"></i>
                            <a href="<?php echo esc_url(dd_dog_directory_url() . '?breed=' . urlencode($breed_name)); ?>" style="color: inherit; text-decoration: none;"><?php echo esc_html($breed_name); ?></a>
                        </div>
                        <?php endif; ?>
                        <?php if ( $loc_display ) : ?>
                        <div class="dd-single-head__location" style="display: inline-flex; align-items: center; gap: 6px; font-size: 14px; color: #64748b; font-weight: 500;">
                            <i class="fa-solid fa-location-dot" style="color: #bd8c42;"></i>
                            <span><?php echo esc_html( $loc_display ); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="dd-single-head__right">
                    <?php if ( $is_owner || $is_admin ) : ?>
                    <a href="<?php echo esc_url(dd_dashboard_url('dogs') . '&edit=' . $post_id); ?>" class="dd-btn dd-btn--ghost" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; border: 1px solid #cbd5e1; color: #334155; text-decoration: none;">
                        <i class="fa-solid fa-pencil"></i> <?php _e('Edit Stud', 'petslist'); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Profile Details Grid (Always shown - Full details matching Admin Dashboard) -->
            <div class="dd-single-card dd-profile-details">
                <h2 class="dd-single-card__title" style="display: flex; align-items: center; gap: 8px; font-size: 18px; font-weight: 700; margin-bottom: 16px; color: #070c3e;">
                    <i class="fa-solid fa-id-card" style="color: #02c5bd;"></i> <?php _e('Dog Profile Details', 'petslist'); ?>
                </h2>
                <div class="dd-profile-grid">
                    <?php
                    $all_profile_fields = [
                        'breed'             => ['label' => __('Breed', 'petslist'), 'val' => $breed_name],
                        'gender'            => ['label' => __('Gender', 'petslist'), 'val' => $meta['gender'] ?? ''],
                        'age'               => ['label' => __('Age', 'petslist'), 'val' => !empty($meta['age']) ? $meta['age'] : ($meta['dob'] ? $meta['dob'] . ($age ? " ({$age})" : '') : '')],
                        'color'             => ['label' => __('Color', 'petslist'), 'val' => $meta['color'] ?? ''],
                        'height'            => ['label' => __('Height', 'petslist'), 'val' => $meta['height'] ?? ''],
                        'weight'            => ['label' => __('Weight', 'petslist'), 'val' => $meta['weight'] ?? ''],
                        'stud_fee'          => ['label' => __('Stud Fee', 'petslist'), 'val' => $meta['stud_fee'] ?? ''],
                        'semen_type'        => ['label' => __('Semen Type', 'petslist'), 'val' => $meta['semen_type'] ?? ''],
                        'titles'            => ['label' => __('Titles', 'petslist'), 'val' => $meta['titles'] ?? ''],
                        'competition_class' => ['label' => __('Class', 'petslist'), 'val' => $meta['competition_class'] ?? ''],
                        'registration_no'   => ['label' => __('Registration No.', 'petslist'), 'val' => $meta['registration_no'] ?? ''],
                        'location'          => ['label' => __('Location', 'petslist'), 'val' => $loc_display],
                    ];

                    foreach ( $all_profile_fields as $key => $field_data ) :
                        $val = $field_data['val'];
                        if ( empty($val) ) continue;
                    ?>
                    <div class="dd-profile-item">
                        <span class="dd-profile-item__label"><?php echo esc_html($field_data['label']); ?></span>
                        <span class="dd-profile-item__value" <?php echo $key === 'stud_fee' ? 'style="color:#059669; font-weight:700;"' : ''; ?>>
                            <?php echo esc_html($val); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- About / Description -->
            <?php if ( get_the_content() ) : ?>
            <div class="dd-single-card" style="margin-top: 24px;">
                <h2 class="dd-single-card__title" style="display: flex; align-items: center; gap: 8px; font-size: 18px; font-weight: 700; margin-bottom: 16px; color: #070c3e;">
                    <i class="fa-solid fa-align-left" style="color: #02c5bd;"></i> <?php _e('About This Dog', 'petslist'); ?>
                </h2>
                <div class="dd-single-card__content" style="font-size: 15px; line-height: 1.7; color: #334155;">
                    <?php the_content(); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Health & Pedigree (Full Details) -->
            <?php if ( ! empty($health_clearances) || ! empty($vaccinations) || ! empty($pedigree) || ! empty($awards) || ! empty($microchip) ) : ?>
            <div class="dd-single-card" style="margin-top: 24px;">
                <h2 class="dd-single-card__title" style="display: flex; align-items: center; gap: 8px; font-size: 18px; font-weight: 700; margin-bottom: 16px; color: #070c3e;">
                    <i class="fa-solid fa-file-waveform" style="color: #02c5bd;"></i> <?php _e('Health & Pedigree', 'petslist'); ?>
                </h2>
                <div class="dd-health-grid">
                    <?php if ( ! empty($health_clearances) ) : ?>
                    <div class="dd-health-item">
                        <div class="dd-health-item__icon">🩺</div>
                        <div class="dd-health-item__content">
                            <strong><?php _e('Health Clearances / Testing', 'petslist'); ?></strong>
                            <p><?php echo nl2br(esc_html($health_clearances)); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ( ! empty($vaccinations) ) : ?>
                    <div class="dd-health-item">
                        <div class="dd-health-item__icon">💉</div>
                        <div class="dd-health-item__content">
                            <strong><?php _e('Vaccinations', 'petslist'); ?></strong>
                            <p><?php echo nl2br(esc_html($vaccinations)); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ( ! empty($pedigree) ) : ?>
                    <div class="dd-health-item">
                        <div class="dd-health-item__icon">📜</div>
                        <div class="dd-health-item__content">
                            <strong><?php _e('Pedigree & Lineage', 'petslist'); ?></strong>
                            <p><?php echo nl2br(esc_html($pedigree)); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ( ! empty($awards) ) : ?>
                    <div class="dd-health-item">
                        <div class="dd-health-item__icon">🏆</div>
                        <div class="dd-health-item__content">
                            <strong><?php _e('Awards & Titles', 'petslist'); ?></strong>
                            <p><?php echo nl2br(esc_html($awards)); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ( ! empty($microchip) ) : ?>
                    <div class="dd-health-item">
                        <div class="dd-health-item__icon">📡</div>
                        <div class="dd-health-item__content">
                            <strong><?php _e('Microchip ID', 'petslist'); ?></strong>
                            <p><?php echo nl2br(esc_html($microchip)); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </main><!-- .dd-single-main -->

        <!-- RIGHT: Sidebar -->
        <aside class="dd-single-sidebar">

            <!-- Owner / Breeder Info Card -->
            <div class="dd-sidebar-card dd-owner-card">
                <div class="dd-owner-card__header">
                    <?php echo get_avatar($owner_id, 72, '', '', ['class' => 'dd-owner-card__avatar']); ?>
                    <div class="dd-owner-card__info">
                        <div class="dd-owner-card__name" style="font-size: 16px; font-weight: 700; color: #0f172a;">
                            <?php echo esc_html($owner ? $owner->display_name : __('Breeder', 'petslist')); ?>
                        </div>
                        <div class="dd-owner-card__since" style="font-size: 12px; color: #64748b; margin-top: 2px;">
                            <?php
                            $since = get_user_meta($owner_id, 'dd_member_since', true) ?: ($owner ? $owner->user_registered : '');
                            if ( $since ) {
                                printf(__('Member since %s', 'petslist'), date('M Y', strtotime($since)));
                            }
                            ?>
                        </div>
                        <?php
                        $owner_dogs = dd_get_user_dog_count($owner_id);
                        printf('<div class="dd-owner-card__dogs" style="font-size: 12px; color: #02c5bd; font-weight: 600; margin-top: 4px;">%s</div>',
                            sprintf(_n('%d stud listed', '%d studs listed', $owner_dogs, 'petslist'), $owner_dogs));
                        ?>
                    </div>
                </div>

                <!-- Breeder Contacts -->
                <div class="dd-owner-card__contact" style="margin-top: 16px; display: flex; flex-direction: column; gap: 8px;">
                    <?php 
                    $c_phone = !empty($meta['contact_phone']) ? $meta['contact_phone'] : get_user_meta($owner_id, 'dd_phone', true);
                    $c_email = !empty($meta['contact_email']) ? $meta['contact_email'] : ($owner ? $owner->user_email : '');
                    $c_web   = !empty($meta['contact_website']) ? $meta['contact_website'] : get_user_meta($owner_id, 'user_url', true);
                    ?>

                    <?php if ( $c_phone ) : ?>
                    <a href="tel:<?php echo esc_attr($c_phone); ?>" class="dd-contact-btn dd-contact-btn--phone" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600;">
                        <i class="fa-solid fa-phone"></i>
                        <span><?php echo esc_html($c_phone); ?></span>
                    </a>
                    <?php endif; ?>

                    <?php if ( $c_email ) : ?>
                    <a href="mailto:<?php echo esc_attr($c_email); ?>?subject=<?php echo esc_attr(rawurlencode('Inquiry regarding stud: ' . get_the_title())); ?>" class="dd-contact-btn dd-contact-btn--email" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600;">
                        <i class="fa-solid fa-envelope"></i>
                        <span><?php _e('Send Email', 'petslist'); ?></span>
                    </a>
                    <?php endif; ?>

                    <?php if ( $c_web ) : ?>
                    <a href="<?php echo esc_url($c_web); ?>" target="_blank" rel="noopener noreferrer" class="dd-contact-btn dd-contact-btn--web" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: #f8fafc; color: #334155; border: 1px solid #cbd5e1; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600;">
                        <i class="fa-solid fa-globe"></i>
                        <span><?php _e('Visit Website', 'petslist'); ?></span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="dd-sidebar-card" style="margin-top: 20px;">
                <h3 class="dd-sidebar-card__title" style="font-size: 16px; font-weight: 700; color: #070c3e; margin-bottom: 14px;">
                    <?php _e('Quick Stats', 'petslist'); ?>
                </h3>
                <ul class="dd-quick-stats" style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; font-size: 13px; color: #475569;">
                    <?php if ( $breed_name ) : ?>
                    <li style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                        <span><i class="fa-solid fa-paw" style="color: #02c5bd; margin-right: 6px;"></i> <?php _e('Breed:', 'petslist'); ?></span>
                        <strong style="color: #0f172a;"><?php echo esc_html($breed_name); ?></strong>
                    </li>
                    <?php endif; ?>

                    <?php if ( ! empty($meta['gender']) ) : ?>
                    <li style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                        <span><i class="fa-solid fa-venus-mars" style="color: #02c5bd; margin-right: 6px;"></i> <?php _e('Gender:', 'petslist'); ?></span>
                        <strong style="color: #0f172a;"><?php echo esc_html($meta['gender']); ?></strong>
                    </li>
                    <?php endif; ?>

                    <?php if ( $age || !empty($meta['age']) ) : ?>
                    <li style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                        <span><i class="fa-solid fa-clock" style="color: #02c5bd; margin-right: 6px;"></i> <?php _e('Age:', 'petslist'); ?></span>
                        <strong style="color: #0f172a;"><?php echo esc_html($meta['age'] ?: $age); ?></strong>
                    </li>
                    <?php endif; ?>

                    <?php if ( ! empty($meta['color']) ) : ?>
                    <li style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                        <span><i class="fa-solid fa-palette" style="color: #02c5bd; margin-right: 6px;"></i> <?php _e('Color:', 'petslist'); ?></span>
                        <strong style="color: #0f172a;"><?php echo esc_html($meta['color']); ?></strong>
                    </li>
                    <?php endif; ?>

                    <?php if ( ! empty($meta['stud_fee']) ) : ?>
                    <li style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                        <span><i class="fa-solid fa-tag" style="color: #059669; margin-right: 6px;"></i> <?php _e('Stud Fee:', 'petslist'); ?></span>
                        <strong style="color: #059669;"><?php echo esc_html($meta['stud_fee']); ?></strong>
                    </li>
                    <?php endif; ?>

                    <?php if ( $loc_display ) : ?>
                    <li style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                        <span><i class="fa-solid fa-location-dot" style="color: #bd8c42; margin-right: 6px;"></i> <?php _e('Location:', 'petslist'); ?></span>
                        <strong style="color: #0f172a; text-align: right; max-width: 60%;"><?php echo esc_html($loc_display); ?></strong>
                    </li>
                    <?php endif; ?>

                    <li style="display: flex; justify-content: space-between; padding-top: 2px;">
                        <span><i class="fa-solid fa-calendar-days" style="color: #64748b; margin-right: 6px;"></i> <?php _e('Listed:', 'petslist'); ?></span>
                        <strong style="color: #0f172a;"><?php echo get_the_date('M j, Y'); ?></strong>
                    </li>
                </ul>
            </div>

            <!-- Back to directory button -->
            <a href="<?php echo esc_url(dd_dog_directory_url()); ?>" class="dd-btn dd-btn--outline dd-btn--full" style="margin-top: 20px; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 16px; border-radius: 8px; font-weight: 600; text-decoration: none; border: 1px solid #cbd5e1; color: #334155; background: #fff;">
                <i class="fa-solid fa-arrow-left"></i> <?php _e('Back to Directory', 'petslist'); ?>
            </a>

        </aside><!-- .dd-single-sidebar -->

    </div><!-- .dd-single-layout -->
</div><!-- .container -->
</div><!-- .dd-single-page -->

<script>
(function($) {
    'use strict';
    $(document).on('click', '.dd-gallery-thumb', function() {
        var src   = $(this).data('src');
        var label = $(this).data('label');
        $('#dd-main-photo').attr('src', src);
        $('#dd-photo-label').text(label || '');
        $('.dd-gallery-thumb').removeClass('dd-gallery-thumb--active');
        $(this).addClass('dd-gallery-thumb--active');
    });
})(jQuery);
</script>

<?php get_footer(); ?>
