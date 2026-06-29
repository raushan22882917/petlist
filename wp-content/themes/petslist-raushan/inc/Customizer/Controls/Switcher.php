<?php

namespace RadiusTheme\Petslist\Customizer\Controls;

use WP_Customize_Control;
/**
 * Toggle Switch Custom Control
 */
if ( class_exists( 'WP_Customize_Control' ) ) {
    class Switcher extends WP_Customize_Control {
        /**
         * The type of control being rendered
         */
        public $type = 'toogle_switch';

        /**
         * Enqueue our scripts and styles
         */
        public function enqueue() {
            wp_enqueue_style('rttheme-custom-controls-css', trailingslashit(get_template_directory_uri()) . 'assets/css/customizer.css', array(), '1.0', 'all');
        }

        /**
         * Render the control in the customizer
         */
        public function render_content() {
            ?>
            <div class="toggle-switch-control">
                <div class="toggle-switch">
                    <input type="checkbox" id="<?php echo esc_attr($this->id); ?>"
                           name="<?php echo esc_attr($this->id); ?>" class="toggle-switch-checkbox"
                           value="<?php echo esc_attr($this->value()); ?>" <?php $this->link();
                    checked($this->value()); ?>>
                    <label class="toggle-switch-label" for="<?php echo esc_attr($this->id); ?>">
                        <span class="toggle-switch-inner"></span>
                        <span class="toggle-switch-switch"></span>
                    </label>
                </div>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
                <?php if (!empty($this->description)) { ?>
                    <span class="customize-control-description"><?php echo esc_html($this->description); ?></span>
                <?php } ?>
            </div>
            <?php
        }
    }
}