<?php
/**
 *
 * This file can be overridden by copying it to yourtheme/elementor-custom/button/view-2.php
 *
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace RadiusTheme\Ayo_Core;
use Elementor\Icons_Manager;

extract( $data );

if ( ! empty( $data['btnlink']['url'] ) ) {
    $this->add_link_attributes( 'btnlink', $data['btnlink'] );
}
?>

<div class="button-arapper">
    <a <?php echo $this->get_render_attribute_string( 'btnlink' ); ?> class="app-btn">
        <?php 
        $icon_value = isset($data['icon']['value']) ? $data['icon']['value'] : '';
        if ($icon_value) {
            echo '<i class="' . esc_attr($icon_value) . '" aria-hidden="true"></i>';
        } else {
            Icons_Manager::render_icon( $data['icon'], [ 'aria-hidden' => 'true' ] );
        }
        ?>
        <span>
            <?php if (!empty($btntext2)) { ?>
                <?php echo esc_html( $btntext2 ); ?>
            <?php } ?> 
            <b>
                <?php echo esc_html( $btntext1 ); ?>
            </b>
        </span>
    </a>
</div>

