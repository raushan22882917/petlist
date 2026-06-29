<?php
/**
 * Dog Directory Shortcode Output
 * Renders the full archive inside a page via [dd_directory]
 * @package Petslist Dog Directory
 */
use RadiusTheme\Petslist\DogDirectory\Subscription;
if ( ! defined( 'ABSPATH' ) ) exit;

$is_sub  = Subscription::can_access_directory();
$breeds  = dd_get_breeds();
$paged   = max( 1, get_query_var('paged') );
$per_pg  = (int) get_option('dd_dogs_per_page', 12);
$breed_f = sanitize_text_field( $_GET['breed'] ?? '' );
$gender_f= sanitize_text_field( $_GET['gender'] ?? '' );
$kw      = sanitize_text_field( $_GET['s'] ?? '' );
$orderby = sanitize_text_field( $_GET['orderby'] ?? 'date' );

$args = [
    'post_type'      => 'dd_dog',
    'post_status'    => 'publish',
    'posts_per_page' => $per_pg,
    'paged'          => $paged,
    'orderby'        => in_array( $orderby, ['date','title','modified'] ) ? $orderby : 'date',
    'order'          => 'DESC',
];
if ( $breed_f )  $args['tax_query']  = [['taxonomy'=>'dd_breed','field'=>'name','terms'=>$breed_f]];
if ( $gender_f ) $args['meta_query'] = [['key'=>'_dd_dog_meta','value'=>'"gender":"'.$gender_f.'"','compare'=>'LIKE']];
if ( $kw )       $args['s']          = $kw;

$q = new WP_Query( $args );
?>

<div class="dd-directory-wrap">

    <!-- Hero Filter Bar -->
    <div class="dd-dir-filterbar">
        <form class="dd-dir-filterform" method="get">
            <div class="dd-dir-filterform__inner">
                <div class="dd-dir-filterform__search">
                    <span class="dd-dir-filterform__search-icon">🔍</span>
                    <input type="text" name="s" value="<?php echo esc_attr($kw); ?>"
                           placeholder="<?php esc_attr_e('Search by name, breed, location…','petslist'); ?>">
                </div>
                <div class="dd-dir-filterform__selects">
                    <div class="dd-dir-filterform__select-wrap">
                        <select name="breed">
                            <option value=""><?php _e('All Breeds','petslist'); ?></option>
                            <?php foreach($breeds as $b): ?>
                            <option value="<?php echo esc_attr($b->name); ?>" <?php selected($breed_f,$b->name); ?>><?php echo esc_html($b->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="dd-dir-filterform__select-wrap">
                        <select name="gender">
                            <option value=""><?php _e('Any Gender','petslist'); ?></option>
                            <option value="Male"   <?php selected($gender_f,'Male');   ?>><?php _e('♂ Male','petslist'); ?></option>
                            <option value="Female" <?php selected($gender_f,'Female'); ?>><?php _e('♀ Female','petslist'); ?></option>
                        </select>
                    </div>
                    <div class="dd-dir-filterform__select-wrap">
                        <select name="orderby">
                            <option value="date"  <?php selected($orderby,'date');  ?>><?php _e('Newest First','petslist'); ?></option>
                            <option value="title" <?php selected($orderby,'title'); ?>><?php _e('Name A–Z','petslist'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="dd-dir-filterform__actions">
                    <button type="submit" class="dd-dir-btn dd-dir-btn--primary">
                        <?php _e('Search','petslist'); ?>
                    </button>
                    <?php if($breed_f||$gender_f||$kw): ?>
                    <a href="?" class="dd-dir-btn dd-dir-btn--ghost"><?php _e('Clear','petslist'); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <?php if ( ! $is_sub ) : ?>
    <div class="dd-dir-subscribe-banner">
        <div class="dd-dir-subscribe-banner__icon">🔒</div>
        <div class="dd-dir-subscribe-banner__text">
            <strong><?php _e('Unlock Full Profiles','petslist'); ?></strong>
            <span><?php printf(__('Subscribe to see contact details, registration numbers, pedigrees &amp; more. <a href="%s">Get Access →</a>','petslist'), esc_url(dd_pricing_url())); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Results count -->
    <?php if ( $q->have_posts() ) : ?>
    <div class="dd-dir-results-bar">
        <span class="dd-dir-results-bar__count">
            <?php printf( _n('%s dog found','%s dogs found',$q->found_posts,'petslist'), number_format_i18n($q->found_posts) ); ?>
        </span>
    </div>

    <!-- Card Grid -->
    <div class="dd-dir-grid">
        <?php while ( $q->have_posts() ) : $q->the_post();
            $pid    = get_the_ID();
            $meta   = dd_get_dog_meta($pid);
            $thumb  = get_the_post_thumbnail_url($pid,'large') ?: dd_placeholder_image();
            $age    = dd_get_dog_age($meta['dob']??'');
            $bt     = get_the_terms($pid,'dd_breed');
            $breed  = $bt && !is_wp_error($bt) ? $bt[0]->name : ($meta['breed']??'');
            $gender = $meta['gender'] ?? '';
            $color  = $meta['color']  ?? '';
            $city   = $meta['city']   ?? '';
            $country= $meta['country']?? '';
            $reg    = $meta['registration_no'] ?? '';
            $location = trim( implode(', ', array_filter([$city, $country])) );
            $is_male  = strtolower($gender) === 'male';
        ?>
        <article class="dd-dir-card">

            <!-- Image -->
            <div class="dd-dir-card__image">
                <a href="<?php the_permalink(); ?>" class="dd-dir-card__image-link">
                    <img src="<?php echo esc_url($thumb); ?>"
                         alt="<?php the_title_attribute(); ?>"
                         loading="lazy">
                    <div class="dd-dir-card__image-overlay"></div>
                </a>

                <!-- Gender badge -->
                <?php if($gender): ?>
                <span class="dd-dir-card__gender dd-dir-card__gender--<?php echo strtolower($gender); ?>">
                    <?php echo $is_male ? '♂' : '♀'; ?> <?php echo esc_html($gender); ?>
                </span>
                <?php endif; ?>

                <!-- Age pill on image -->
                <?php if($age): ?>
                <span class="dd-dir-card__age-pill"><?php echo esc_html($age); ?></span>
                <?php endif; ?>
            </div>

            <!-- Body -->
            <div class="dd-dir-card__body">

                <?php if($breed): ?>
                <div class="dd-dir-card__breed"><?php echo esc_html($breed); ?></div>
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
                    <?php if($is_sub && $reg): ?>
                    <span class="dd-dir-card__meta-item">
                        <span class="dd-dir-card__meta-icon">🏷️</span>
                        <?php echo esc_html($reg); ?>
                    </span>
                    <?php endif; ?>
                </div>

                <?php if(!$is_sub): ?>
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
    <?php if($q->max_num_pages > 1): ?>
    <div class="dd-dir-pagination">
        <?php echo paginate_links([
            'total'     => $q->max_num_pages,
            'current'   => $paged,
            'prev_text' => '← Previous',
            'next_text' => 'Next →',
            'type'      => 'list',
        ]); ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="dd-dir-empty">
        <div class="dd-dir-empty__icon">🐾</div>
        <h3><?php _e('No dogs found','petslist'); ?></h3>
        <p><?php _e('Try adjusting your search filters or check back later.','petslist'); ?></p>
        <a href="?" class="dd-dir-btn dd-dir-btn--primary"><?php _e('Clear Filters','petslist'); ?></a>
    </div>
    <?php endif; ?>

</div>
