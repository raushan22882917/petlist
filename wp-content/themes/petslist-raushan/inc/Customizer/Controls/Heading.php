<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0.5
 */

namespace RadiusTheme\Petslist\Customizer\Controls;

use WP_Customize_Control;
/**
 * Separator Custom Control
 */
if ( class_exists( 'WP_Customize_Control' ) ) {
    class Heading extends WP_Customize_Control {
        public $type = 'heading';

        /**
         * Enqueue our scripts and styles
         */
        public function enqueue() {
            wp_enqueue_style('rttheme-custom-controls-css', trailingslashit(get_template_directory_uri()) . 'assets/css/customizer.css', array(), '1.0', 'all');
        }

        public function render_content() {
            ?>
            <h3><?php echo esc_html($this->label); ?></h3>
            <?php
        }
    }
}