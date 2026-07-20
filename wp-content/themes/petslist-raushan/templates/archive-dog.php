<?php
/**
 * Dog Directory Archive Template
 * @package Petslist Dog Directory
 */

use RadiusTheme\Petslist\DogDirectory\Subscription;

get_header();

$is_subscriber  = Subscription::can_access_directory();
$breeds         = dd_get_breeds();
$per_page       = (int) get_option('dd_dogs_per_page', 12);
$paged          = max(1, get_query_var('paged'));
$breed_filter   = sanitize_text_field($_GET['breed']            ?? '');
$gender_filter  = sanitize_text_field($_GET['gender']           ?? '');
$country_filter = sanitize_text_field($_GET['country']          ?? '');
$health_filter  = sanitize_text_field($_GET['health_clearance'] ?? '');
$keyword        = sanitize_text_field($_GET['s']                ?? '');
$orderby        = sanitize_text_field($_GET['orderby']          ?? 'date');

// Unique countries from metadata
global $wpdb;
$countries = [];
$all_meta  = $wpdb->get_col("SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_dd_dog_meta'");
foreach ( $all_meta as $val ) {
    $data = maybe_unserialize($val);
    if ( is_array($data) && !empty($data['country']) ) {
        $countries[] = trim($data['country']);
    }
}
$countries = array_filter(array_unique($countries));
asort($countries);

// Build query
$args = [
    'post_type'      => 'dd_dog',
    'post_status'    => 'publish',
    'posts_per_page' => $per_page,
    'paged'          => $paged,
    'orderby'        => in_array($orderby, ['date','title','modified']) ? $orderby : 'date',
    'order'          => 'DESC',
];

if ( $breed_filter ) {
    $args['tax_query'] = [['taxonomy' => 'dd_breed', 'field' => 'name', 'terms' => $breed_filter]];
}

$meta_queries = [];
if ( $gender_filter ) {
    $meta_queries[] = ['key' => '_dd_dog_meta', 'value' => '"gender";s:'.strlen($gender_filter).':"'.$gender_filter.'"', 'compare' => 'LIKE'];
}
if ( $country_filter ) {
    $meta_queries[] = ['key' => '_dd_dog_meta', 'value' => '"country";s:'.strlen($country_filter).':"'.$country_filter.'"', 'compare' => 'LIKE'];
}
if ( $health_filter === 'yes' ) {
    $meta_queries[] = ['key' => '_dd_dog_health', 'compare' => 'EXISTS'];
}
if ( count($meta_queries) ) {
    if ( count($meta_queries) > 1 ) $meta_queries['relation'] = 'AND';
    $args['meta_query'] = $meta_queries;
}

if ( $keyword ) $args['s'] = $keyword;

$query = new WP_Query($args);
?>

<div class="dd-archive-page content-area">
    <div class="container">

        <!-- Page Header -->
        <div class="dd-archive-header">
            <div class="dd-archive-header__left">
                <h1 class="dd-archive-header__title">
                    <span class="dd-paw-icon">🐾</span> <?php _e('Dog Directory', 'petslist'); ?>
                </h1>
                <p class="dd-archive-header__subtitle">
                    <?php printf(__('Browse %d registered dogs from verified breeders worldwide.', 'petslist'), $query->found_posts); ?>
                </p>
            </div>
            <div class="dd-archive-header__right">
                <?php if ( $is_subscriber ) : ?>
                <a href="<?php echo esc_url(dd_dashboard_url('add-dog')); ?>" class="dd-btn dd-btn--primary">
                    <i class="icon-pl-plus"></i> <?php _e('Add Your Dog', 'petslist'); ?>
                </a>
                <?php else : ?>
                <a href="<?php echo esc_url(dd_pricing_url()); ?>" class="dd-btn dd-btn--secondary">
                    <?php _e('Subscribe to Add Dogs', 'petslist'); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="dd-filters-bar">
            <form class="dd-search-form" method="get" action="<?php echo esc_url(dd_dog_directory_url()); ?>">
                <div class="dd-search-form__row">
                    <div class="dd-search-form__field dd-search-form__field--grow">
                        <i class="icon-pl-search"></i>
                        <input type="text" name="s" placeholder="<?php esc_attr_e('Search name, owner...', 'petslist'); ?>" value="<?php echo esc_attr($keyword); ?>">
                    </div>
                    <div class="dd-search-form__field">
                        <select name="breed">
                            <?php dd_render_breed_options( $breed_filter, true ); ?>
                        </select>
                    </div>

                    <div class="dd-search-form__field">
                        <select name="gender">
                            <option value=""><?php _e('Any Gender', 'petslist'); ?></option>
                            <option value="Male"   <?php selected($gender_filter, 'Male');   ?>><?php _e('Male ♂', 'petslist'); ?></option>
                            <option value="Female" <?php selected($gender_filter, 'Female'); ?>><?php _e('Female ♀', 'petslist'); ?></option>
                        </select>
                    </div>
                    <div class="dd-search-form__field">
                        <select name="country">
                            <option value=""><?php _e('All Countries', 'petslist'); ?></option>
                            <?php foreach ( $countries as $country ) : if(empty($country)) continue; ?>
                            <option value="<?php echo esc_attr($country); ?>" <?php selected($country_filter, $country); ?>>
                                <?php echo esc_html($country); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="dd-search-form__field">
                        <select name="health_clearance">
                            <option value=""><?php _e('Health: Any', 'petslist'); ?></option>
                            <option value="yes" <?php selected($health_filter, 'yes'); ?>><?php _e('Verified Clearances', 'petslist'); ?></option>
                        </select>
                    </div>
                    <button type="submit" class="dd-btn dd-btn--primary dd-search-form__submit">
                        <i class="icon-pl-search"></i> <?php _e('Search', 'petslist'); ?>
                    </button>
                    <?php if ( $breed_filter || $gender_filter || $keyword || $country_filter || $health_filter ) : ?>
                    <a href="<?php echo esc_url(dd_dog_directory_url()); ?>" class="dd-btn dd-btn--ghost"><?php _e('Clear', 'petslist'); ?></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Non-subscriber banner -->
        <?php if ( ! $is_subscriber ) : ?>
        <div class="dd-subscribe-banner">
            <div class="dd-subscribe-banner__inner">
                <div class="dd-subscribe-banner__icon">🔒</div>
                <div class="dd-subscribe-banner__text">
                    <strong><?php _e('Full directory access requires a subscription.', 'petslist'); ?></strong>
                    <span><?php _e('Subscribe to view contact info, registration numbers, pedigree, health data, and add your own dogs.', 'petslist'); ?></span>
                </div>
                <a href="<?php echo esc_url(dd_pricing_url()); ?>" class="dd-btn dd-btn--accent"><?php _e('View Plans', 'petslist'); ?></a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Results count -->
        <div class="dd-results-bar">
            <span class="dd-results-bar__count">
                <?php printf(_n('%d dog found', '%d dogs found', $query->found_posts, 'petslist'), $query->found_posts); ?>
            </span>
        </div>

        <?php if ( $query->have_posts() ) : ?>

        <!-- Premium Card Grid -->
        <div class="dd-dir-grid">
            <?php while ( $query->have_posts() ) : $query->the_post();
                $pid        = get_the_ID();
                $meta       = dd_get_dog_meta($pid);
                $age        = dd_get_dog_age($meta['dob'] ?? '');
                $thumb      = get_the_post_thumbnail_url($pid, 'large') ?: dd_placeholder_image();
                $breeds_trm = get_the_terms($pid, 'dd_breed');
                $breed_name = $breeds_trm && !is_wp_error($breeds_trm) ? $breeds_trm[0]->name : ($meta['breed'] ?? '');
                $gender     = $meta['gender'] ?? '';
                $color      = $meta['color']  ?? '';
                $city       = $meta['city']   ?? '';
                $country    = $meta['country']?? '';
                $reg_no     = $meta['registration_no'] ?? '';
                $location   = trim(implode(', ', array_filter([$city, $country])));
                $health_data = get_post_meta($pid, '_dd_dog_health', true);
                $has_health  = !empty($health_data) && array_filter((array)$health_data);
                $is_male     = strtolower($gender) === 'male';
            ?>
            <article class="dd-dir-card">

                <!-- Card Image -->
                <div class="dd-dir-card__image">
                    <a href="<?php the_permalink(); ?>" class="dd-dir-card__image-link">
                        <img src="<?php echo esc_url($thumb); ?>"
                             alt="<?php the_title_attribute(); ?>"
                             loading="lazy">
                        <div class="dd-dir-card__image-overlay"></div>
                    </a>

                    <!-- Gender Badge -->
                    <?php if($gender): ?>
                    <span class="dd-dir-card__gender dd-dir-card__gender--<?php echo strtolower($gender); ?>">
                        <?php echo $is_male ? '♂' : '♀'; ?> <?php echo esc_html($gender); ?>
                    </span>
                    <?php endif; ?>

                    <!-- Age pill -->
                    <?php if($age): ?>
                    <span class="dd-dir-card__age-pill"><?php echo esc_html($age); ?></span>
                    <?php endif; ?>

                    <!-- Health verified badge -->
                    <?php if($is_subscriber && $has_health): ?>
                    <span class="dd-dir-card__health-badge">✓ Health Verified</span>
                    <?php endif; ?>
                </div>

                <!-- Card Body -->
                <div class="dd-dir-card__body">

                    <?php if($breed_name): ?>
                    <div class="dd-dir-card__breed"><?php echo esc_html($breed_name); ?></div>
                    <?php endif; ?>

                    <h3 class="dd-dir-card__name">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>

                    <div class="dd-dir-card__meta">
                        <?php if($location): ?>
                        <span class="dd-dir-card__meta-item">
                            <span class="dd-dir-card__meta-icon">📍</span>
                            <?php echo esc_html($location); ?>
                        </span>
                        <?php endif; ?>
                        <?php if($color): ?>
                        <span class="dd-dir-card__meta-item">
                            <span class="dd-dir-card__meta-icon">🎨</span>
                            <?php echo esc_html($color); ?>
                        </span>
                        <?php endif; ?>
                        <?php if($is_subscriber && $reg_no): ?>
                        <span class="dd-dir-card__meta-item">
                            <span class="dd-dir-card__meta-icon">🏷️</span>
                            <?php echo esc_html($reg_no); ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <?php if(!$is_subscriber): ?>
                    <div class="dd-dir-card__locked">
                        🔒 <a href="<?php echo esc_url(dd_pricing_url()); ?>"><?php _e('Subscribe for full details','petslist'); ?></a>
                    </div>
                    <?php endif; ?>

                    <div class="dd-dir-card__footer">
                        <a href="<?php the_permalink(); ?>" class="dd-dir-card__cta">
                            <?php _e('View Profile','petslist'); ?>
                            <span class="dd-dir-card__cta-arrow">→</span>
                        </a>
                    </div>
                </div>
            </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>

        <!-- Pagination -->
        <?php if ( $query->max_num_pages > 1 ) : ?>
        <div class="dd-dir-pagination">
            <?php echo paginate_links([
                'total'     => $query->max_num_pages,
                'current'   => $paged,
                'prev_text' => '← Previous',
                'next_text' => 'Next →',
                'type'      => 'list',
                'mid_size'  => 2,
                'end_size'  => 1,
            ]); ?>
        </div>
        <?php endif; ?>

        <?php else : ?>
        <div class="dd-dir-empty">
            <div class="dd-dir-empty__icon">🐕</div>
            <h3><?php _e('No dogs found', 'petslist'); ?></h3>
            <p><?php _e('Try adjusting your search filters or browse all dogs.', 'petslist'); ?></p>
            <a href="<?php echo esc_url(dd_dog_directory_url()); ?>" class="dd-dir-btn dd-dir-btn--primary"><?php _e('Browse All Dogs', 'petslist'); ?></a>
        </div>
        <?php endif; ?>

    </div><!-- .container -->
</div><!-- .dd-archive-page -->

<?php get_footer(); ?>
