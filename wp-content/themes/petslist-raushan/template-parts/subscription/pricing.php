<?php
/**
 * Pricing / Plans Template Part
 * @package Petslist Dog Directory
 */

use RadiusTheme\Petslist\DogDirectory\Subscription;

if ( ! defined( 'ABSPATH' ) ) exit;

$plans      = Subscription::get_plans();
$active_sub = Subscription::get_user_subscription();
$active_plan_slug = $active_sub ? $active_sub->plan_slug : '';
$limit_reached    = Subscription::has_reached_sales_limit();
?>
<div class="dd-pricing-wrap">

    <!-- Header -->
    <div class="dd-pricing-header">
        <div class="dd-pricing-header__badge"><?php _e('Simple Pricing', 'petslist'); ?></div>
        <h1 class="dd-pricing-header__title"><?php _e('Choose Your Plan', 'petslist'); ?></h1>
        <p class="dd-pricing-header__sub">
            <?php _e('Get full access to the Dog Directory. List unlimited dogs, view contact info, pedigrees, health data, and more.', 'petslist'); ?>
        </p>
    </div>

    <!-- Current subscription notice -->
    <?php if ( $active_sub ) : ?>
    <div class="dd-notice dd-notice--success">
        <i class="icon-pl-tick-mark-fill-circle"></i>
        <?php printf(
            __('You have an active <strong>%s</strong> subscription, valid until %s. <a href="%s">Manage in Dashboard</a>', 'petslist'),
            esc_html($active_sub->plan_name),
            date('M j, Y', strtotime($active_sub->expires_at)),
            esc_url(dd_dashboard_url('subscription'))
        ); ?>
    </div>
    <?php endif; ?>

    <!-- Sales cap warning banner -->
    <?php if ( $limit_reached && ! $active_sub ) : ?>
    <div class="dd-notice dd-notice--warning" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 1.25rem;">⚠️</span>
        <div>
            <strong><?php _e('All monthly packages are currently sold out!', 'petslist'); ?></strong>
            <p style="margin: 2px 0 0 0; font-size: 0.9rem;"><?php _e('Only 9 active ad packages can be sold. Please check back later when an advertising space becomes available.', 'petslist'); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Plans Grid -->
    <div class="dd-plans-grid dd-plans-grid--<?php echo count($plans); ?>col">
        <?php
        $popular_slug = 'kennels'; // Mark kennels as popular
        foreach ( $plans as $plan ) :
            $features = json_decode($plan->features, true) ?: [];
            $is_popular  = $plan->slug === $popular_slug;
            $is_active   = $plan->slug === $active_plan_slug;
            $period      = __('/month', 'petslist');
        ?>
        <div class="dd-plan-card <?php echo $is_popular ? 'dd-plan-card--popular' : ''; ?> <?php echo $is_active ? 'dd-plan-card--active' : ''; ?>">
            <?php if ( $is_popular ) : ?>
            <div class="dd-plan-card__badge"><?php _e('Best Value', 'petslist'); ?></div>
            <?php endif; ?>
            <?php if ( $is_active ) : ?>
            <div class="dd-plan-card__badge dd-plan-card__badge--active"><?php _e('Current Plan', 'petslist'); ?></div>
            <?php endif; ?>

            <div class="dd-plan-card__header">
                <div class="dd-plan-card__icon">
                    <?php echo $plan->slug === 'studs' ? '📅' : ($plan->slug === 'kennels' ? '⭐' : '♾️'); ?>
                </div>
                <h2 class="dd-plan-card__name"><?php echo esc_html($plan->name); ?></h2>
                <div class="dd-plan-card__price">
                    <span class="dd-plan-card__currency">$</span>
                    <span class="dd-plan-card__amount"><?php echo number_format($plan->price, 2); ?></span>
                    <span class="dd-plan-card__period"><?php echo $period; ?></span>
                </div>
            </div>

            <ul class="dd-plan-card__features">
                <?php foreach ( $features as $feature ) : ?>
                <li><i class="icon-pl-tick-mark-fill-circle"></i> <?php echo esc_html($feature); ?></li>
                <?php endforeach; ?>
            </ul>

            <div class="dd-plan-card__footer">
                <?php if ( $is_active ) : ?>
                <button class="dd-btn dd-btn--ghost dd-btn--full" disabled><?php _e('Current Plan', 'petslist'); ?></button>
                <?php elseif ( $limit_reached ) : ?>
                <button class="dd-btn dd-btn--ghost dd-btn--full" style="background-color: #e2e8f0; color: #94a3b8; border-color: #cbd5e1;" disabled><?php _e('Sold Out', 'petslist'); ?></button>
                <?php elseif ( is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url(dd_checkout_url($plan->slug)); ?>" class="dd-btn <?php echo $is_popular ? 'dd-btn--primary' : 'dd-btn--outline'; ?> dd-btn--full dd-choose-plan" data-plan="<?php echo esc_attr($plan->slug); ?>">
                    <?php _e('Choose Plan', 'petslist'); ?> <i class="icon-pl-right-arrow"></i>
                </a>
                <?php else : ?>
                <a href="<?php echo esc_url(add_query_arg('redirect_to', urlencode(dd_checkout_url($plan->slug)), dd_register_url())); ?>" class="dd-btn <?php echo $is_popular ? 'dd-btn--primary' : 'dd-btn--outline'; ?> dd-btn--full">
                    <?php _e('Get Started', 'petslist'); ?> <i class="icon-pl-right-arrow"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- FAQs -->
    <div class="dd-pricing-faq">
        <h2 class="dd-pricing-faq__title"><?php _e('Frequently Asked Questions', 'petslist'); ?></h2>
        <div class="dd-faq-grid">
            <?php
            $faqs = [
                [__('Can I cancel anytime?', 'petslist'), __('Yes. Cancel anytime from your dashboard. You\'ll retain access until the end of your billing period.', 'petslist')],
                [__('How many dogs can I list?', 'petslist'), __('Unlimited dogs on all plans. No caps.', 'petslist')],
                [__('Is my payment secure?', 'petslist'), __('All payments are processed securely by Stripe. We never store your card details.', 'petslist')],
                [__('What photos can I upload?', 'petslist'), __('Minimum front and side view photos. Gallery images on Yearly and Lifetime plans.', 'petslist')],
                [__('Can I upgrade my plan?', 'petslist'), __('Yes. Upgrade at any time from your dashboard. The remaining value is applied to the new plan.', 'petslist')],
                [__('Is there a free trial?', 'petslist'), __('New accounts can browse limited directory listings. Subscribe for full access.', 'petslist')],
            ];
            foreach ( $faqs as $faq ) : ?>
            <div class="dd-faq-item">
                <div class="dd-faq-item__question"><?php echo esc_html($faq[0]); ?> <i class="icon-pl-angle-down-fat"></i></div>
                <div class="dd-faq-item__answer"><?php echo esc_html($faq[1]); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Guarantee -->
    <div class="dd-guarantee">
        <div class="dd-guarantee__icon">🛡️</div>
        <div class="dd-guarantee__text">
            <strong><?php _e('7-Day Money-Back Guarantee', 'petslist'); ?></strong>
            <p><?php _e('Not satisfied? Get a full refund within 7 days, no questions asked.', 'petslist'); ?></p>
        </div>
    </div>

</div><!-- .dd-pricing-wrap -->
