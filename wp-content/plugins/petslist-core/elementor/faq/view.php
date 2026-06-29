<?php
/**
 * This file can be overridden by copying it to yourtheme/elementor-custom/accordion/view.php
 * 
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

 use Elementor\Icons_Manager;

global $faq_unique_id;
$faq_unique_id = empty($faq_unique_id) ? 1 : $faq_unique_id + 1;
$faq_id = 'rtaccordion-'.$faq_unique_id;
if ( $data['icon_display']  == 'yes' ) {
    $icon = $data['icon_position'];
} else {
    $icon = '';
}

?>

<div class="faq-box">
    <div class="panel-group" id="<?php echo esc_attr( $faq_id ) ?>">
        <?php $i = 1;
            foreach ( $data['faq_items'] as $faq_item ) {
            $show =  $i == 1 ? 'show' : '';
            $collapsed =  $i == 1 ? '' : 'collapsed';
            $t = $faq_item['faq_title'];
            $uid = strtolower(str_replace(array(':', '\\', '/', '*', '?', '&', '.', ';', ' '), '', $t));           
        ?>
        <div class="panel panel-default">
            <div class="panel-heading" id="heading<?php echo esc_attr($uid); ?>">
                <button class="accordion-button <?php echo esc_attr( $icon.' '.$collapsed ); ?>" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapse<?php echo esc_attr($uid); ?>" aria-expanded="true" aria-controls="collapse<?php echo esc_attr($uid); ?>">
                    <?php if ($faq_item['faq_icon']['value']) { ?>
                    <span class="btn-icon">
                        <?php Icons_Manager::render_icon( $faq_item['faq_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                    </span>
                    <?php } echo wp_kses_post( $faq_item['faq_title'] ); ?>

                    <?php if ( $data['icon_display']  == 'yes' ) { ?>
                        <span class="rtin-accordion-icon">
                            <?php if( !empty($data['icon']) ) { ?>
                                <span class="rtin-icon rt-icon-closed">
                                    <?php Icons_Manager::render_icon( $data['icon'], [ 'aria-hidden' => 'true' ] ); ?>
                                </span>
                            <?php } if( !empty($data['icon_open']) ) { ?>
                                <span class="rtin-icon rt-icon-opened">
                                    <?php Icons_Manager::render_icon( $data['icon_open'], [ 'aria-hidden' => 'true' ] ); ?>
                                </span>
                            <?php } ?>
                        </span>
                    <?php } ?>
                </button>
            </div>
            <div id="collapse<?php echo esc_attr($uid); ?>" class="accordion-collapse collapse <?php echo esc_attr( $show ); ?>" aria-labelledby="heading<?php echo esc_attr($uid); ?>"
                data-bs-parent="#<?php echo esc_attr( $faq_id ) ?>">
                <div class="panel-body">
                    <?php echo wp_kses_post( $faq_item['faq_content'] ) ?>
                </div>
            </div>
        </div>
        <?php $i++; } ?>
    </div>
</div>