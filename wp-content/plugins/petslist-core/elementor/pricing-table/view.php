<?php
/**
 * This file can be overridden by copying it to yourtheme/elementor-custom/pricing-table/view-2.php
 * 
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

 use Elementor\Icons_Manager;

extract( $data );

if ( ! empty( $data['plan_btn_link']['url'] ) ) {
    $this->add_link_attributes( 'plan_btn_link', $data['plan_btn_link'] );
}

?>


<div class="rt-pricing-item common-style">
    <div class="pricing-header">
        <h3 class="pricing-title"><?php echo esc_html( $plan_type ); ?></h3>
        <h2 class="pricing-price"><?php echo esc_html( $plan_price ); ?><span class="pricing-plan">&nbsp; <?php echo esc_html( $plan_duration ); ?></span></h2>
        <p class="para-text">   
            <?php echo esc_html( $plan_description ); ?> 
        </p>
    </div>
    <div class="rt-pricing-features">
        <ul class="rt-pricing-features-list feature-list feature-list--style-2">
            <?php foreach ( $data['price_features'] as $value ) { ?>
                <li>
                    <?php Icons_Manager::render_icon( $value['icon'], [ 'aria-hidden' => 'true' ] ); ?>
                    <span><?php echo esc_html( $value['list'] ); ?></span>
                </li>
            <?php } ?>
        </ul>
    </div>

    <div class="rt-pricing-item-btn">
        <a <?php echo $this->get_render_attribute_string( 'plan_btn_link' ); ?> class="pricing-btn text-center">
            <?php echo esc_html( $plan_btn_text ); ?>
        </a>
    </div>
    <?php if ($data['item_shape']['id']) { ?>
        <div class="pricing-shape-img">
            <?php echo wp_get_attachment_image( $data['item_shape']['id'], 'full' ); ?>
        </div>
    <?php } ?>
</div>