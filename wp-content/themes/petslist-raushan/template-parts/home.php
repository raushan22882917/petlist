<?php
/**
 * Dog Directory - Custom Homepage Template Part
 * @package Petslist Dog Directory
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

// Base URL for media uploads (works on both local and live environments)
$dd_upload_base = wp_upload_dir()['baseurl'];

// Fetch breeds for filter dropdown
$breeds = dd_get_breeds();

// Fetch plans for subscription pricing section
$plans = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dd_plans WHERE is_active = 1 ORDER BY price ASC");

// Fetch 4 recent published dogs
$recent_dogs = new WP_Query([
    'post_type'      => 'dd_dog',
    'post_status'    => 'publish',
    'posts_per_page' => 4,
    'orderby'        => 'date',
    'order'          => 'DESC'
]);
?>

<div class="dd-home-wrap">
    <!-- HERO SEARCH SECTION -->
    <section class="dd-home-hero">
        <img class="dd-home-hero__bg-art dd-home-hero__bg-art--left" src="<?php echo esc_url( $dd_upload_base . '/2023/08/banner-img-1.png' ); ?>" alt="Dog Left">
        <img class="dd-home-hero__bg-art dd-home-hero__bg-art--right" src="<?php echo esc_url( $dd_upload_base . '/2023/08/banner-img-1.png' ); ?>" alt="Dog Right">
        <div class="dd-home-hero__container">
            <h1 class="dd-home-hero__title"><?php _e('Find Your Perfect Pedigree Companion', 'petslist'); ?></h1>
            <p class="dd-home-hero__subtitle"><?php _e('Browse verified pedigree dogs, query health clearances, and connect directly with certified owners.', 'petslist'); ?></p>
            
            <form class="dd-home-search" method="get" action="<?php echo esc_url(dd_dog_directory_url()); ?>">
                <div class="dd-home-search__field dd-home-search__field--keyword">
                    <span class="dd-home-search__icon">🔍</span>
                    <input type="text" name="s" placeholder="<?php esc_attr_e('Search by name or keywords...', 'petslist'); ?>">
                </div>
                <div class="dd-home-search__field">
                    <select name="breed">
                        <option value=""><?php _e('All Breeds', 'petslist'); ?></option>
                        <?php foreach ( $breeds as $breed ) : ?>
                        <option value="<?php echo esc_attr($breed->name); ?>">
                            <?php echo esc_html($breed->name); ?> (<?php echo $breed->count; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="dd-home-search__field">
                    <select name="gender">
                        <option value=""><?php _e('Any Gender', 'petslist'); ?></option>
                        <option value="Male"><?php _e('Male ♂', 'petslist'); ?></option>
                        <option value="Female"><?php _e('Female ♀', 'petslist'); ?></option>
                    </select>
                </div>
                <button type="submit" class="dd-home-search__btn"><?php _e('Search Listings', 'petslist'); ?></button>
            </form>
        </div>
    </section>

    <!-- FEATURED DOGS SECTION -->
    <section class="dd-home-section">
        <div class="dd-home-section__header">
            <h2><?php _e('Latest Verified Submissions', 'petslist'); ?></h2>
            <p><?php _e('Meet the newest additions to our pedigree dog directory.', 'petslist'); ?></p>
        </div>

        <?php if ( $recent_dogs->have_posts() ) : ?>
        <div class="dd-home-dogs-grid">
            <?php while ( $recent_dogs->have_posts() ) : $recent_dogs->the_post();
                $dog_id     = get_the_ID();
                $meta       = dd_get_dog_meta($dog_id);
                $front_url  = dd_get_front_photo_url($dog_id, 'medium');
                $breed_terms = get_the_terms($dog_id, 'dd_breed');
                $breed_name  = $breed_terms && ! is_wp_error($breed_terms) ? $breed_terms[0]->name : ($meta['breed'] ?? '—');
                $age         = dd_get_dog_age($meta['dob'] ?? '');
            ?>
            <div class="dd-home-dog-card">
                <div class="dd-home-dog-card__image">
                    <img src="<?php echo esc_url($front_url); ?>" alt="<?php the_title_attribute(); ?>">
                    <?php if ( $meta['gender'] ) : ?>
                    <span class="dd-home-dog-card__gender dd-home-dog-card__gender--<?php echo strtolower($meta['gender']); ?>">
                        <?php echo $meta['gender'] === 'Male' ? '♂' : '♀'; ?>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="dd-home-dog-card__content">
                    <span class="dd-home-dog-card__breed"><?php echo esc_html($breed_name); ?></span>
                    <h3 class="dd-home-dog-card__title"><?php the_title(); ?></h3>
                    <div class="dd-home-dog-card__meta">
                        <?php if ( $age ) : ?>
                        <span>🎂 <?php echo esc_html($age); ?></span>
                        <?php endif; ?>
                        <?php if ( !empty($meta['city']) || !empty($meta['country']) ) : ?>
                        <span>📍 <?php echo esc_html(trim(($meta['city'] ?? '') . ', ' . ($meta['country'] ?? ''), ', ')); ?></span>
                        <?php endif; ?>
                    </div>
                    <a href="<?php the_permalink(); ?>" class="dd-home-dog-card__btn"><?php _e('View Profile', 'petslist'); ?></a>
                </div>
            </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <div style="text-align: center; margin-top: 30px;">
            <a href="<?php echo esc_url(dd_dog_directory_url()); ?>" class="dd-home-view-all"><?php _e('Browse Full Directory →', 'petslist'); ?></a>
        </div>
        <?php else : ?>
        <div class="dd-home-empty-state">
            <p>🐾 <?php _e('No dogs listed in the directory yet. Check back soon!', 'petslist'); ?></p>
        </div>
        <?php endif; ?>
    </section>

    <!-- HOW IT WORKS SECTION -->
    <section class="dd-home-how">
        <div class="dd-home-section__header">
            <h2><?php _e('Simple and Secure Directory Access', 'petslist'); ?></h2>
            <p><?php _e('Follow these three steps to list or browse certified pedigree profiles.', 'petslist'); ?></p>
        </div>
        <div class="dd-home-how-grid">
            <div class="dd-home-how-card">
                <div class="dd-home-how-card__icon">💳</div>
                <h3><?php _e('Choose a Plan', 'petslist'); ?></h3>
                <p><?php _e('Select a Monthly, Yearly, or Lifetime subscription tier tailored to your directory needs.', 'petslist'); ?></p>
            </div>
            <div class="dd-home-how-card">
                <div class="dd-home-how-card__icon">👤</div>
                <h3><?php _e('Register Account', 'petslist'); ?></h3>
                <p><?php _e('Create a user profile dashboard to manage your directory settings and list your dogs.', 'petslist'); ?></p>
            </div>
            <div class="dd-home-how-card">
                <div class="dd-home-how-card__icon">🐶</div>
                <h3><?php _e('Add Your Dogs', 'petslist'); ?></h3>
                <p><?php _e('Publish profiles featuring front and side photos, pedigree charts, microchips, and health records.', 'petslist'); ?></p>
            </div>
        </div>
    </section>

    <!-- SUBSCRIPTION PLANS SECTION -->
    <section class="dd-home-section dd-home-plans-section">
        <div class="dd-home-section__header">
            <h2><?php _e('Choose Your Subscription Plan', 'petslist'); ?></h2>
            <p><?php _e('Unlock premium search tools, health records, contact information, and unlimited pedigree listings.', 'petslist'); ?></p>
        </div>
        
        <div class="dd-home-plans-grid">
            <?php foreach ( $plans as $plan ) :
                $features = json_decode($plan->features, true) ?: [];
                $is_popular = ($plan->slug === 'yearly');
            ?>
            <div class="dd-home-plan-card <?php echo $is_popular ? 'dd-home-plan-card--popular' : ''; ?>">
                <?php if ( $is_popular ) : ?>
                <div class="dd-home-plan-card__badge"><?php _e('Most Popular', 'petslist'); ?></div>
                <?php endif; ?>
                <h3 class="dd-home-plan-card__name"><?php echo esc_html($plan->name); ?></h3>
                <div class="dd-home-plan-card__price">
                    <span class="currency">$</span>
                    <span class="value"><?php echo number_format($plan->price, 2); ?></span>
                    <span class="period">/ <?php echo $plan->slug === 'lifetime' ? __('lifetime', 'petslist') : ($plan->slug === 'yearly' ? __('year', 'petslist') : __('month', 'petslist')); ?></span>
                </div>
                <ul class="dd-home-plan-card__features">
                    <?php foreach ( $features as $feat ) : ?>
                    <li>✔️ <?php echo esc_html($feat); ?></li>
                    <?php endforeach; ?>
                </ul>
                <a href="<?php echo esc_url(dd_checkout_url() . '?plan=' . $plan->id); ?>" class="dd-home-plan-card__btn"><?php _e('Get Started', 'petslist'); ?></a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<style>
/* ── Custom Homepage Layout Styles ──────────────────────────────── */
.dd-home-wrap {
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: #515167;
    background: #fdfdfd;
}
.dd-home-hero {
    background-color: #070c3e;
    background-image: url('<?php echo esc_url( $dd_upload_base . '/2023/08/banner-bg.png' ); ?>');
    background-repeat: no-repeat;
    background-position: center center;
    background-size: cover;
    padding: 120px 24px;
    text-align: center;
    color: #fff;
    position: relative;
    overflow: hidden;
    min-height: 480px;
    display: flex;
    align-items: center;
}
.dd-home-hero__bg-art {
    position: absolute;
    bottom: 0;
    z-index: 1;
    max-height: 90%;
    pointer-events: none;
    transition: all 0.3s ease;
}
.dd-home-hero__bg-art--left {
    left: 20px;
    max-width: 280px;
}
.dd-home-hero__bg-art--right {
    right: 20px;
    max-width: 280px;
    transform: scaleX(-1);
}
@media (max-width: 1024px) {
    .dd-home-hero__bg-art {
        display: none;
    }
}
.dd-home-hero__container {
    max-width: 900px;
    margin: 0 auto;
    position: relative;
    z-index: 10;
}
.dd-home-hero__title {
    font-size: 42px;
    font-weight: 800;
    color: #ffffff;
    line-height: 1.2;
    margin-bottom: 16px;
    letter-spacing: -0.5px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.dd-home-hero__subtitle {
    font-size: 16px;
    color: #cbd5e1;
    margin-bottom: 40px;
    max-width: 650px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}
.dd-home-search {
    background: #ffffff;
    padding: 8px;
    border-radius: 16px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    max-width: 800px;
    margin: 0 auto;
}
.dd-home-search__field {
    flex: 1;
    min-width: 150px;
    position: relative;
}
.dd-home-search__field--keyword {
    flex: 1.5;
    min-width: 200px;
}
.dd-home-search__icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 16px;
}
.dd-home-search input,
.dd-home-search select {
    width: 100%;
    height: 52px;
    border: none;
    background: transparent;
    padding: 0 16px;
    color: #070c3e;
    font-size: 14px;
    font-weight: 500;
    outline: none;
}
.dd-home-search__field--keyword input {
    padding-left: 44px;
}
.dd-home-search select {
    border-left: 1px solid #f1f5f9;
    cursor: pointer;
}
.dd-home-search__btn {
    background: #02c5bd;
    color: #ffffff;
    border: none;
    border-radius: 12px;
    padding: 0 24px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    height: 52px;
}
.dd-home-search__btn:hover {
    background: #02a39d;
    transform: translateY(-1px);
}
@media (max-width: 768px) {
    .dd-home-search {
        flex-direction: column;
        padding: 16px;
        border-radius: 20px;
    }
    .dd-home-search select {
        border-left: none;
        border-top: 1px solid #f1f5f9;
    }
    .dd-home-search__btn {
        width: 100%;
    }
}
.dd-home-section {
    max-width: 1200px;
    margin: 0 auto;
    padding: 80px 24px;
}
.dd-home-section__header {
    text-align: center;
    margin-bottom: 50px;
}
.dd-home-section__header h2 {
    font-size: 32px;
    font-weight: 800;
    color: #070c3e;
    margin-bottom: 12px;
}
.dd-home-section__header p {
    font-size: 15px;
    color: #64748b;
}
.dd-home-dogs-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
}
@media (max-width: 992px) {
    .dd-home-dogs-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 576px) {
    .dd-home-dogs-grid {
        grid-template-columns: 1fr;
    }
}
.dd-home-dog-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
}
.dd-home-dog-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    border-color: #02c5bd;
}
.dd-home-dog-card__image {
    position: relative;
    padding-top: 100%; /* 1:1 Aspect Ratio */
    background: #f1f5f9;
}
.dd-home-dog-card__image img {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    object-fit: cover;
}
.dd-home-dog-card__gender {
    position: absolute;
    top: 12px; right: 12px;
    width: 28px; height: 28px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700;
    font-size: 14px;
    color: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.dd-home-dog-card__gender--male { background: #3b82f6; }
.dd-home-dog-card__gender--female { background: #ec4899; }
.dd-home-dog-card__content {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.dd-home-dog-card__breed {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #02c5bd;
    letter-spacing: 1px;
    display: block;
    margin-bottom: 6px;
}
.dd-home-dog-card__title {
    font-size: 18px;
    font-weight: 700;
    color: #070c3e;
    margin-bottom: 12px;
    line-height: 1.3;
}
.dd-home-dog-card__meta {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 12px;
    color: #64748b;
    margin-bottom: 20px;
}
.dd-home-dog-card__btn {
    text-align: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #070c3e;
    border-radius: 10px;
    padding: 10px;
    font-weight: 600;
    font-size: 13px;
    text-decoration: none;
    transition: all 0.2s;
    margin-top: auto;
}
.dd-home-dog-card__btn:hover {
    background: rgba(2, 197, 189, 0.05);
    color: #02c5bd;
    border-color: #02c5bd;
}
.dd-home-view-all {
    display: inline-block;
    color: #02c5bd;
    font-weight: 700;
    font-size: 15px;
    text-decoration: none;
    transition: all 0.2s;
}
.dd-home-view-all:hover {
    color: #02a39d;
    transform: translateX(2px);
}
.dd-home-how {
    background: #f8fafc;
    padding: 80px 24px;
}
.dd-home-how-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
    max-width: 1200px;
    margin: 0 auto;
}
@media (max-width: 768px) {
    .dd-home-how-grid {
        grid-template-columns: 1fr;
    }
}
.dd-home-how-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 32px;
    text-align: center;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.01);
}
.dd-home-how-card__icon {
    font-size: 40px;
    margin-bottom: 20px;
}
.dd-home-how-card h3 {
    font-size: 18px;
    font-weight: 700;
    color: #070c3e;
    margin-bottom: 12px;
}
.dd-home-how-card p {
    font-size: 13px;
    line-height: 1.6;
    color: #64748b;
}
.dd-home-plans-section {
    background: #ffffff;
}
.dd-home-plans-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
    max-width: 1100px;
    margin: 0 auto;
    align-items: stretch;
}
@media (max-width: 900px) {
    .dd-home-plans-grid {
        grid-template-columns: 1fr;
        max-width: 450px;
    }
}
.dd-home-plan-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 24px;
    padding: 40px;
    text-align: center;
    position: relative;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
    display: flex;
    flex-direction: column;
    transition: all 0.3s;
}
.dd-home-plan-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
}
.dd-home-plan-card--popular {
    border-color: #02c5bd;
    box-shadow: 0 10px 15px -3px rgba(2, 197, 189, 0.1);
}
.dd-home-plan-card__badge {
    position: absolute;
    top: 20px; left: 50%; transform: translateX(-50%);
    background: #02c5bd;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 4px 14px;
    border-radius: 12px;
}
.dd-home-plan-card__name {
    font-size: 18px;
    font-weight: 700;
    color: #070c3e;
    margin-bottom: 24px;
}
.dd-home-plan-card__price {
    margin-bottom: 30px;
    color: #070c3e;
}
.dd-home-plan-card__price .currency {
    font-size: 20px;
    font-weight: 600;
    vertical-align: super;
}
.dd-home-plan-card__price .value {
    font-size: 48px;
    font-weight: 800;
}
.dd-home-plan-card__price .period {
    font-size: 13px;
    color: #64748b;
}
.dd-home-plan-card__features {
    list-style: none;
    padding: 0; margin: 0 0 30px 0;
    text-align: left;
    display: flex;
    flex-direction: column;
    gap: 12px;
    font-size: 13px;
    color: #515167;
}
.dd-home-plan-card__btn {
    display: block;
    width: 100%;
    padding: 14px;
    border-radius: 14px;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.2s;
    margin-top: auto;
}
.dd-home-plan-card:not(.dd-home-plan-card--popular) .dd-home-plan-card__btn {
    background: #f1f5f9;
    color: #070c3e;
}
.dd-home-plan-card:not(.dd-home-plan-card--popular) .dd-home-plan-card__btn:hover {
    background: #e2e8f0;
}
.dd-home-plan-card--popular .dd-home-plan-card__btn {
    background: #02c5bd;
    color: #ffffff;
}
.dd-home-plan-card--popular .dd-home-plan-card__btn:hover {
    background: #02a39d;
    box-shadow: 0 4px 12px rgba(2, 197, 189, 0.25);
}
.dd-home-empty-state {
    text-align: center;
    background: #ffffff;
    border: 1px dashed #cbd5e1;
    border-radius: 16px;
    padding: 40px;
    font-size: 14px;
    color: #94a3b8;
}
</style>
