<?php
/**
 * Admin Dashboard — Plans Tab
 */
if ( ! defined('ABSPATH') ) exit;
global $wpdb;
$plans = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dd_plans ORDER BY price ASC");
?>

<div class="dda-plans">
    <div class="ddu-panel">
        <div class="ddu-panel__head">
            <h3 class="ddu-panel__title"><?php _e('Subscription Plans','petslist'); ?></h3>
            <small style="color:#9ca3af"><?php _e('Edit prices, durations, and features. Changes take effect for new subscriptions immediately.','petslist'); ?></small>
        </div>

        <div id="dd-plan-message" class="dd-auth-message" style="display:none;margin-bottom:16px"></div>

        <div class="dda-plans-edit-grid">
            <?php foreach ($plans as $plan) :
                $features = json_decode($plan->features, true) ?: [];
            ?>
            <div class="dda-plan-edit-card" data-plan-id="<?php echo $plan->id; ?>">
                <div class="dda-plan-edit-card__header">
                    <span class="dda-plan-edit-card__icon">
                        <?php echo $plan->slug==='monthly'?'🐾':($plan->slug==='studs'?'🐕':($plan->slug==='kennels'?'⭐':'🏢')); ?>
                    </span>
                    <strong><?php echo esc_html($plan->name); ?></strong>
                    <span class="ddu-pill ddu-pill--<?php echo $plan->is_active?'active':'draft'; ?>">
                        <?php echo $plan->is_active ? __('Active','petslist') : __('Inactive','petslist'); ?>
                    </span>
                </div>
                <div class="dda-plan-edit-card__body">
                    <div class="dd-form-group">
                        <label><?php _e('Name','petslist'); ?></label>
                        <input type="text" class="plan-field" name="name" value="<?php echo esc_attr($plan->name); ?>">
                    </div>
                    <div class="dda-plan-edit-card__row">
                        <div class="dd-form-group">
                            <label><?php _e('Price ($)','petslist'); ?></label>
                            <input type="number" class="plan-field" name="price" value="<?php echo esc_attr($plan->price); ?>" step="0.01" min="0">
                        </div>
                        <div class="dd-form-group">
                            <label><?php _e('Duration (days)','petslist'); ?></label>
                            <input type="number" class="plan-field" name="duration" value="<?php echo esc_attr($plan->duration); ?>" min="1">
                        </div>
                    </div>
                    <div class="dd-form-group">
                        <label><?php _e('Features (one per line)','petslist'); ?></label>
                        <textarea class="plan-field" name="features" rows="5"><?php echo esc_textarea(implode("\n", $features)); ?></textarea>
                    </div>
                    <div class="dd-form-group">
                        <label><?php _e('Status','petslist'); ?></label>
                        <select class="plan-field" name="is_active">
                            <option value="1" <?php selected($plan->is_active,1); ?>><?php _e('Active','petslist'); ?></option>
                            <option value="0" <?php selected($plan->is_active,0); ?>><?php _e('Inactive','petslist'); ?></option>
                        </select>
                    </div>
                </div>
                <button class="ddu-btn-primary dda-save-plan" data-id="<?php echo $plan->id; ?>" style="width:100%"><?php _e('Save Changes','petslist'); ?></button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
(function($){
    $(document).on('click', '.dda-save-plan', function(){
        var $card = $(this).closest('.dda-plan-edit-card');
        var pid   = $(this).data('id');
        var feats = $card.find('[name=features]').val().split('\n').filter(Boolean);
        $.post(ddVars.ajaxUrl, {
            action:    'dd_admin_update_plan',
            nonce:     ddVars.nonces.dog,
            plan_id:   pid,
            name:      $card.find('[name=name]').val(),
            price:     $card.find('[name=price]').val(),
            duration:  $card.find('[name=duration]').val(),
            is_active: $card.find('[name=is_active]').val(),
            features:  feats,
        }, function(res){
            var $msg = $('#dd-plan-message');
            $msg.removeClass('success error').addClass(res.success?'success':'error')
                .html(res.data.message).show();
            setTimeout(function(){ $msg.fadeOut(); }, 3000);
        });
    });
})(jQuery);
</script>
