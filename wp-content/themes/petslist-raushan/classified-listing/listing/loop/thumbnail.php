<?php
use Rtcl\Helpers\Functions;
use RadiusTheme\Petslist\Listing_Functions;
use Rtcl\Controllers\Hooks\TemplateHooks;

global $listing;

if (!class_exists('RtclPro')) return;

$listing_type = Listing_Functions::get_listing_type( $listing );
?>
<div class="item-img bg--gradient-50">
    <div class="petslist-listing-actions-buttons">
        <?php if ( ! empty( $listing_type ) ) : ?>
            <span class="listing-type-badge">
                <?php echo sprintf( "%s %s", apply_filters( 'rtcl_type_prefix', __( 'For', 'petslist' ) ), $listing_type['label'] ); ?>
            </span>
        <?php endif; ?>
        <?php TemplateHooks::loop_item_badges(); ?>
    </div>

    <div class="listing-thumb">
        <a href="<?php the_permalink(); ?>" class="rtcl-media grid-view-img"><?php echo wp_kses_post( $listing->get_the_thumbnail( 'petslist-size2' ) ); ?></a>
	</div>

    <ul class="meta-tags">
        <?php if ( Functions::is_enable_favourite() ) { ?>
            <?php if ( is_user_logged_in() ) { ?>
                <li class="meta-item meta-favourite">
                    <?php echo Listing_Functions::get_favourites_link( $listing->get_id() ); ?>
                </li>
            <?php } else { ?>
                <li class="meta-item meta-favourite">
                    <?php echo Listing_Functions::get_favourites_link( $listing->get_id() ); ?>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>
</div>