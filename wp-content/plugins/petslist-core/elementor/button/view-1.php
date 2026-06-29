<?php
/**
 *
 * This file can be overridden by copying it to yourtheme/elementor-custom/button/view-1.php
 *
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

use Elementor\Icons_Manager;
extract( $data );

if ( ! empty( $data['btnlink']['url'] ) ) {
    $this->add_link_attributes( 'btnlink', $data['btnlink'] );
}
?>

<div class="button-arapper icon-position-<?php echo esc_attr( $icon_position ); ?>">
    <a <?php echo $this->get_render_attribute_string( 'btnlink' ); ?> class="button-style-<?php echo esc_attr($style); ?>">
        <?php 
            if ($icon_position == 'before') {
                Icons_Manager::render_icon( $data['icon'], [ 'aria-hidden' => 'true' ] );
            }
            echo esc_html( $btntext ); 
            if ($icon_position == 'after') {
                Icons_Manager::render_icon( $data['icon'], [ 'aria-hidden' => 'true' ] );
            }
        ?>
    </a>
</div>

