<?php
/**
 * Dog Directory - Single Dog Profile Template
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

// Gallery
$gallery_ids   = get_post_meta($post_id, '_dd_gallery', true) ?: [];
?>

<div class="dd-single-page content-area">
<div class="container">

    <!-- Breadcrumb -->
    <nav class="dd-breadcrumb">
        <a href="<?php echo home_url('/'); ?>"><?php _e('Home', 'petslist'); ?></a>
        <i class="icon-pl-angle-down-fat"></i>
        <a href="<?php echo esc_url(dd_dog_directory_url()); ?>"><?php _e('Dog Directory', 'petslist'); ?></a>
        <i class="icon-pl-angle-down-fat"></i>
        <span><?php the_title(); ?></span>
    </nav>

    <div class="dd-single-layout">

        <!-- LEFT: Main Content -->
        <main class="dd-single-main">

            <!-- Hero Gallery -->
            <div class="dd-single-gallery">
                <div class="dd-gallery-main" id="dd-gallery-main">
                    <img src="<?php echo esc_url($front_url); ?>" alt="<?php printf(__('%s - Front View', 'petslist'), get_the_title()); ?>" id="dd-main-photo" class="dd-gallery-main__img">
                    <?php if ( $is_subscriber || $is_owner || $is_admin ) : ?>
                    <div class="dd-gallery-view-label" id="dd-photo-label"><?php _e('Front View', 'petslist'); ?></div>
                    <?php endif; ?>
                </div>
                <?php if ( $is_subscriber || $is_owner || $is_admin ) : ?>
                <div class="dd-gallery-thumbs">
                    <div class="dd-gallery-thumb dd-gallery-thumb--active" data-src="<?php echo esc_url($front_url); ?>" data-label="<?php esc_attr_e('Front View', 'petslist'); ?>">
                        <img src="<?php echo esc_url($front_url); ?>" alt="Front">
                        <span><?php _e('Front', 'petslist'); ?></span>
                    </div>
                    <?php if ( $side_url !== dd_placeholder_image() ) : ?>
                    <div class="dd-gallery-thumb" data-src="<?php echo esc_url($side_url); ?>" data-label="<?php esc_attr_e('Side View', 'petslist'); ?>">
                        <img src="<?php echo esc_url($side_url); ?>" alt="Side">
                        <span><?php _e('Side', 'petslist'); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php foreach ( $gallery_ids as $gid ) : $gurl = wp_get_attachment_image_url($gid, 'medium'); if ( ! $gurl ) continue; ?>
                    <div class="dd-gallery-thumb" data-src="<?php echo esc_url($gurl); ?>" data-label="<?php esc_attr_e('Gallery', 'petslist'); ?>">
                        <img src="<?php echo esc_url($gurl); ?>" alt="Gallery">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else : ?>
                <div class="dd-gallery-locked">
                    <div class="dd-gallery-locked__inner">
                        <i class="fa-solid fa-lock"></i>
                        <p><?php _e('Subscribe to view all photos including front, side, and gallery images.', 'petslist'); ?></p>
                        <a href="<?php echo esc_url(dd_pricing_url()); ?>" class="dd-btn dd-btn--primary"><?php _e('Get Access', 'petslist'); ?></a>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Dog Title & Quick Info -->
            <div class="dd-single-head">
                <div class="dd-single-head__left">
                    <h1 class="dd-single-head__name">
                        <?php the_title(); ?>
                        <?php if ( $meta['gender'] ) : ?>
                        <span class="dd-gender-badge dd-gender-badge--<?php echo strtolower($meta['gender']); ?>">
                            <?php echo $meta['gender'] === 'Male' ? '♂ Male' : '♀ Female'; ?>
                        </span>
                        <?php endif; ?>
                    </h1>
                    <?php if ( $breed_name ) : ?>
                    <div class="dd-single-head__breed">
                        <i class="icon-pl-tag"></i>
                        <a href="<?php echo esc_url(dd_dog_directory_url() . '?breed=' . urlencode($breed_name)); ?>"><?php echo esc_html($breed_name); ?></a>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="dd-single-head__right">
                    <?php if ( $is_owner || $is_admin ) : ?>
                    <a href="<?php echo esc_url(dd_dashboard_url('dogs') . '&edit=' . $post_id); ?>" class="dd-btn dd-btn--ghost">
                        <i class="icon-pl-edit"></i> <?php _e('Edit', 'petslist'); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Profile Details Table -->
            <div class="dd-single-card dd-profile-details">
                <h2 class="dd-single-card__title"><?php _e('Dog Profile', 'petslist'); ?></h2>
                <div class="dd-profile-grid">
                    <?php
                    $visible_fields = [
                        'breed'  => __('Breed', 'petslist'),
                        'gender' => __('Gender', 'petslist'),
                        'dob'    => __('Date of Birth', 'petslist'),
                        'color'  => __('Color', 'petslist'),
                        'weight' => __('Weight', 'petslist'),
                    ];
                    $subscriber_fields = [
                        'registration_no' => __('Registration No.', 'petslist'),
                        'country'         => __('Country', 'petslist'),
                        'city'            => __('City', 'petslist'),
                    ];
                    foreach ( $visible_fields as $key => $label ) :
                        $value = $key === 'breed' ? $breed_name : ($meta[$key] ?? '');
                        if ( $key === 'dob' && $value ) $value .= $age ? " ({$age})" : '';
                        if ( $key === 'weight' && $value ) $value .= ' kg';
                        if ( empty($value) ) continue;
                    ?>
                    <div class="dd-profile-item">
                        <span class="dd-profile-item__label"><?php echo esc_html($label); ?></span>
                        <span class="dd-profile-item__value"><?php echo esc_html($value); ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php if ( $is_subscriber || $is_owner || $is_admin ) :
                        foreach ( $subscriber_fields as $key => $label ) :
                            $value = $meta[$key] ?? '';
                            if ( empty($value) ) continue;
                    ?>
                    <div class="dd-profile-item">
                        <span class="dd-profile-item__label"><?php echo esc_html($label); ?></span>
                        <span class="dd-profile-item__value"><?php echo esc_html($value); ?></span>
                    </div>
                    <?php endforeach;
                    else : ?>
                    <div class="dd-profile-item dd-profile-item--locked dd-profile-item--span">
                        <i class="fa-solid fa-lock"></i>
                        <span><?php printf(__('<a href="%s">Subscribe</a> to see registration number, location, and contact details.', 'petslist'), esc_url(dd_pricing_url())); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Description -->
            <?php if ( get_the_content() ) : ?>
            <div class="dd-single-card">
                <h2 class="dd-single-card__title"><?php _e('About This Dog', 'petslist'); ?></h2>
                <div class="dd-single-card__content">
                    <?php the_content(); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Health & Pedigree (subscribers only) -->
            <?php if ( $is_subscriber || $is_owner || $is_admin ) : ?>
            <?php if ( array_filter($health) ) : ?>
            <div class="dd-single-card">
                <h2 class="dd-single-card__title"><?php _e('Health & Pedigree', 'petslist'); ?></h2>
                <div class="dd-health-grid">
                    <?php
                    $health_labels = [
                        'health_clearances' => ['icon' => '🩺', 'label' => __('Health Clearances', 'petslist')],
                        'vaccinations'      => ['icon' => '💉', 'label' => __('Vaccinations', 'petslist')],
                        'pedigree'          => ['icon' => '📜', 'label' => __('Pedigree', 'petslist')],
                        'awards'            => ['icon' => '🏆', 'label' => __('Awards & Titles', 'petslist')],
                        'microchip'         => ['icon' => '📡', 'label' => __('Microchip', 'petslist')],
                    ];
                    foreach ( $health_labels as $key => $info ) :
                        $value = $health[$key] ?? '';
                        if ( empty($value) ) continue;
                    ?>
                    <div class="dd-health-item">
                        <div class="dd-health-item__icon"><?php echo $info['icon']; ?></div>
                        <div class="dd-health-item__content">
                            <strong><?php echo esc_html($info['label']); ?></strong>
                            <p><?php echo nl2br(esc_html($value)); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php else : ?>
            <div class="dd-single-card dd-single-card--locked">
                <div class="dd-locked-content">
                    <div class="dd-locked-content__icon">🔒</div>
                    <h3><?php _e('Health & Pedigree Information', 'petslist'); ?></h3>
                    <p><?php _e('Subscribe to view detailed health clearances, vaccination records, pedigree, and awards.', 'petslist'); ?></p>
                    <a href="<?php echo esc_url(dd_pricing_url()); ?>" class="dd-btn dd-btn--primary"><?php _e('Get Full Access', 'petslist'); ?></a>
                </div>
            </div>
            <?php endif; ?>

        </main><!-- .dd-single-main -->

        <!-- RIGHT: Sidebar -->
        <aside class="dd-single-sidebar">

            <!-- Owner Info -->
            <div class="dd-sidebar-card dd-owner-card">
                <div class="dd-owner-card__header">
                    <?php echo get_avatar($owner_id, 72, '', '', ['class' => 'dd-owner-card__avatar']); ?>
                    <div class="dd-owner-card__info">
                        <div class="dd-owner-card__name"><?php echo esc_html($owner->display_name); ?></div>
                        <div class="dd-owner-card__since">
                            <?php
                            $since = get_user_meta($owner_id, 'dd_member_since', true) ?: $owner->user_registered;
                            printf(__('Member since %s', 'petslist'), date('M Y', strtotime($since)));
                            ?>
                        </div>
                        <?php
                        $owner_dogs = dd_get_user_dog_count($owner_id);
                        printf('<div class="dd-owner-card__dogs">%s</div>',
                            sprintf(_n('%d dog listed', '%d dogs listed', $owner_dogs, 'petslist'), $owner_dogs));
                        ?>
                    </div>
                </div>
                <?php if ( $is_subscriber || $is_owner || $is_admin ) : ?>
                <div class="dd-owner-card__contact">
                    <?php if ( $meta['contact_phone'] ) : ?>
                    <a href="tel:<?php echo esc_attr($meta['contact_phone']); ?>" class="dd-contact-btn dd-contact-btn--phone">
                        <i class="icon-pl-iocn-fill"></i>
                        <span><?php echo esc_html($meta['contact_phone']); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if ( $meta['contact_email'] ) : ?>
                    <a href="mailto:<?php echo esc_attr($meta['contact_email']); ?>" class="dd-contact-btn dd-contact-btn--email">
                        <i class="icon-pl-message-box"></i>
                        <span><?php _e('Send Email', 'petslist'); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if ( $meta['contact_website'] ) : ?>
                    <a href="<?php echo esc_url($meta['contact_website']); ?>" target="_blank" rel="noopener noreferrer" class="dd-contact-btn dd-contact-btn--web">
                        <i class="icon-pl-earth"></i>
                        <span><?php _e('Visit Website', 'petslist'); ?></span>
                    </a>
                    <?php endif; ?>
                </div>
                <?php else : ?>
                <div class="dd-owner-card__locked">
                    <a href="<?php echo esc_url(dd_pricing_url()); ?>" class="dd-btn dd-btn--primary dd-btn--full">
                        <i class="fa-solid fa-lock"></i> <?php _e('Subscribe to Contact', 'petslist'); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Quick Stats -->
            <div class="dd-sidebar-card">
                <h3 class="dd-sidebar-card__title"><?php _e('Quick Stats', 'petslist'); ?></h3>
                <ul class="dd-quick-stats">
                    <?php if ( $breed_name ) : ?>
                    <li><i class="icon-pl-tag"></i> <strong><?php _e('Breed:', 'petslist'); ?></strong> <?php echo esc_html($breed_name); ?></li>
                    <?php endif; ?>
                    <?php if ( $meta['gender'] ) : ?>
                    <li><i class="icon-pl-account"></i> <strong><?php _e('Gender:', 'petslist'); ?></strong> <?php echo esc_html($meta['gender']); ?></li>
                    <?php endif; ?>
                    <?php if ( $age ) : ?>
                    <li><i class="icon-pl-clock"></i> <strong><?php _e('Age:', 'petslist'); ?></strong> <?php echo esc_html($age); ?></li>
                    <?php endif; ?>
                    <?php if ( $meta['color'] ) : ?>
                    <li><i class="icon-pl-flash"></i> <strong><?php _e('Color:', 'petslist'); ?></strong> <?php echo esc_html($meta['color']); ?></li>
                    <?php endif; ?>
                    <?php if ( $meta['weight'] ) : ?>
                    <li><i class="icon-pl-iocn-fill"></i> <strong><?php _e('Weight:', 'petslist'); ?></strong> <?php echo esc_html($meta['weight']); ?> kg</li>
                    <?php endif; ?>
                    <li><i class="icon-pl-calendar"></i> <strong><?php _e('Listed:', 'petslist'); ?></strong> <?php echo get_the_date(); ?></li>
                </ul>
            </div>

            <!-- Back to directory -->
            <a href="<?php echo esc_url(dd_dog_directory_url()); ?>" class="dd-btn dd-btn--outline dd-btn--full">
                <i class="fa-solid fa-arrow-left"></i> <?php _e('Back to Directory', 'petslist'); ?>
            </a>

        </aside><!-- .dd-single-sidebar -->

    </div><!-- .dd-single-layout -->
</div><!-- .container -->
</div><!-- .dd-single-page -->

<script>
(function($) {
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
