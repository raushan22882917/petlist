<?php
/**
 * Dashboard Tab: Subscription
 * @package Petslist Dog Directory
 */

use RadiusTheme\Petslist\DogDirectory\Subscription;

if ( ! defined( 'ABSPATH' ) ) exit;

$user_id = get_current_user_id();
$sub     = Subscription::get_user_subscription( $user_id );
$plans   = Subscription::get_plans();
?>

<div class="dd-tab-subscription">

    <div class="dd-tab-dogs__header">
        <h2><?php _e( 'My Subscription', 'petslist' ); ?></h2>
        <p class="dd-tab-dogs__subtitle"><?php _e('View and manage your active membership plan and features.', 'petslist'); ?></p>
    </div>

    <div id="dd-sub-message" class="dd-auth-message" style="display:none; margin-bottom: 20px;"></div>

    <div class="dd-dogs-card-panel">

        <?php if ( $sub ) : ?>
        <!-- Active Subscription -->
        <div class="dd-sub-status-card dd-sub-status-card--active">
            <div class="dd-sub-status-card__header">
                <div class="dd-sub-status-card__badge">
                    <i class="fa-solid fa-circle-check" style="color: #22c55e; margin-right: 6px;"></i>
                    <?php _e( 'Active Membership', 'petslist' ); ?>
                </div>
                <div class="dd-sub-status-card__plan"><?php echo esc_html( $sub->plan_name ); ?></div>
            </div>
            
            <div class="dd-sub-status-card__details">
                <div class="dd-sub-detail">
                    <span class="dd-sub-detail__label"><?php _e( 'Status', 'petslist' ); ?></span>
                    <span class="dd-sub-detail__value dd-sub-detail__value--active"><?php _e( 'Active', 'petslist' ); ?></span>
                </div>
                <div class="dd-sub-detail">
                    <span class="dd-sub-detail__label"><?php _e( 'Started', 'petslist' ); ?></span>
                    <span class="dd-sub-detail__value"><?php echo date( 'F j, Y', strtotime( $sub->starts_at ) ); ?></span>
                </div>
                <div class="dd-sub-detail">
                    <span class="dd-sub-detail__label"><?php _e( 'Expires', 'petslist' ); ?></span>
                    <span class="dd-sub-detail__value"><?php echo date( 'F j, Y', strtotime( $sub->expires_at ) ); ?></span>
                </div>
                <div class="dd-sub-detail">
                    <span class="dd-sub-detail__label"><?php _e( 'Days Remaining', 'petslist' ); ?></span>
                    <span class="dd-sub-detail__value">
                        <?php echo max( 0, ceil( ( strtotime($sub->expires_at) - time() ) / 86400 ) ); ?> days
                    </span>
                </div>
            </div>

            <?php
            $features = json_decode( $sub->plan_features, true ) ?: [];
            if ( $features ) :
            ?>
            <div class="dd-sub-features-wrap">
                <h4><?php _e('Plan Features', 'petslist'); ?></h4>
                <ul class="dd-sub-features">
                    <?php foreach ( $features as $f ) : ?>
                    <li><i class="fa-solid fa-check" style="color: #02c5bd; margin-right: 8px;"></i><?php echo esc_html($f); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="dd-sub-actions">
                <a href="<?php echo esc_url( dd_pricing_url() ); ?>" class="dd-btn dd-btn--primary">
                    <i class="fa-solid fa-bolt" style="margin-right: 6px;"></i><?php _e( 'Upgrade Plan', 'petslist' ); ?>
                </a>
                <button class="dd-btn dd-btn--danger dd-btn--outline" id="dd-cancel-sub">
                    <i class="fa-solid fa-xmark" style="margin-right: 6px;"></i><?php _e( 'Cancel Subscription', 'petslist' ); ?>
                </button>
            </div>
        </div>

        <?php else : ?>
        <!-- No Subscription -->
        <div class="dd-sub-status-card dd-sub-status-card--inactive">
            <div class="dd-sub-status-card__icon">🔒</div>
            <h3><?php _e( 'No Active Subscription', 'petslist' ); ?></h3>
            <p><?php _e( 'Subscribe to unlock all features: list dogs, view contact info, access pedigrees, health records, and more.', 'petslist' ); ?></p>
            <a href="<?php echo esc_url( dd_pricing_url() ); ?>" class="dd-btn dd-btn--primary dd-btn--lg">
                <?php _e( 'View Plans & Pricing', 'petslist' ); ?> <i class="fa-solid fa-arrow-right" style="margin-left: 6px;"></i>
            </a>
        </div>

        <!-- Plans Comparison -->
        <div class="dd-sub-plans-mini">
            <?php
            $listing_plans = array_filter( $plans, function( $p ) {
                return $p->slug === 'monthly' || strpos( strtolower( $p->name ), 'listing' ) !== false;
            } );
            $ad_plans = array_filter( $plans, function( $p ) {
                return $p->slug !== 'monthly' && strpos( strtolower( $p->name ), 'listing' ) === false;
            } );
            ?>

            <?php if ( ! empty($listing_plans) ) : ?>
            <h3><?php _e( 'Standard Listing Plan', 'petslist' ); ?></h3>
            <div class="dd-sub-plans-mini__grid" style="grid-template-columns: 1fr; max-width: 360px; margin-bottom: 30px;">
                <?php foreach ( $listing_plans as $plan ) :
                    $period   = $plan->duration <= 31 ? '/mo' : ( $plan->duration <= 366 ? '/yr' : ' once' );
                    $features = json_decode( $plan->features, true ) ?: [];
                ?>
                <div class="dd-sub-plan-mini">
                    <div class="dd-sub-plan-mini__name"><?php echo esc_html($plan->name); ?></div>
                    <div class="dd-sub-plan-mini__price">$<?php echo number_format($plan->price,2); ?><small><?php echo $period; ?></small></div>
                    <ul>
                        <?php foreach ( array_slice($features,0,3) as $f ) : ?>
                        <li><i class="fa-solid fa-check" style="color: #02c5bd; margin-right: 6px; font-size: 11px;"></i><?php echo esc_html($f); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?php echo esc_url(dd_checkout_url($plan->slug)); ?>" class="dd-btn dd-btn--primary dd-btn--sm">
                        <?php _e('Choose Plan', 'petslist'); ?>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ( ! empty($ad_plans) ) : ?>
            <h3><?php _e( 'Ad Packages', 'petslist' ); ?></h3>
            <div class="dd-sub-plans-mini__grid">
                <?php foreach ( $ad_plans as $plan ) :
                    $period   = $plan->duration <= 31 ? '/mo' : ( $plan->duration <= 366 ? '/yr' : ' once' );
                    $features = json_decode( $plan->features, true ) ?: [];
                ?>
                <div class="dd-sub-plan-mini">
                    <div class="dd-sub-plan-mini__name"><?php echo esc_html($plan->name); ?></div>
                    <div class="dd-sub-plan-mini__price">$<?php echo number_format($plan->price,2); ?><small><?php echo $period; ?></small></div>
                    <ul>
                        <?php foreach ( array_slice($features,0,3) as $f ) : ?>
                        <li><i class="fa-solid fa-check" style="color: #02c5bd; margin-right: 6px; font-size: 11px;"></i><?php echo esc_html($f); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?php echo esc_url(dd_checkout_url($plan->slug)); ?>" class="dd-btn dd-btn--outline dd-btn--sm">
                        <?php _e('Choose Plan', 'petslist'); ?>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php endif; ?>

    </div>

</div>
