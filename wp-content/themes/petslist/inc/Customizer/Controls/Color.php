<?php

namespace RadiusTheme\Petslist\Customizer\Controls;

use WP_Customize_Control;

/**
 * Toggle Switch Custom Control
 */
if ( class_exists( 'WP_Customize_Control' ) ) {
	class Color extends WP_Customize_Control {

		/**
		 * The type of control being rendered
		 */
		public $type = 'alpha-color';
		/**
		 * Add support for palettes to be passed in.
		 *
		 * Supported palette values are true, false, or an array of RGBa and Hex colors.
		 */
		public $palette;
		/**
		 * Add support for showing the opacity value on the slider handle.
		 */
		public $show_opacity;

		/**
		 * Enqueue our scripts and styles
		 */
		public function enqueue() {
			wp_enqueue_style( 'rttheme-custom-controls-css', trailingslashit( get_template_directory_uri() ) . 'assets/css/customizer.css', [ 'wp-color-picker' ], '1.0', 'all' );
			wp_enqueue_script( 'rttheme-custom-controls-js', trailingslashit( get_template_directory_uri() ) . 'assets/js/customizer.js', [ 'jquery', 'wp-color-picker' ], '1.0',
				true );
		}

		/**
		 * Render the control in the customizer
		 */
		public function render_content_old() {
			?>
            <div class="toggle-switch-control">
                <div class="toggle-switch">
                    <input type="checkbox" id="<?php echo esc_attr( $this->id ); ?>"
                           name="<?php echo esc_attr( $this->id ); ?>" class="toggle-switch-checkbox"
                           value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link();
					checked( $this->value() ); ?>>
                    <label class="toggle-switch-label" for="<?php echo esc_attr( $this->id ); ?>">
                        <span class="toggle-switch-inner"></span>
                        <span class="toggle-switch-switch"></span>
                    </label>
                </div>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php if ( ! empty( $this->description ) ) { ?>
                    <span class="customize-control-description"><?php echo esc_html( $this->description ); ?></span>
				<?php } ?>
            </div>
			<?php
		}

		public function render_content() {
			// Process the palette
			if ( is_array( $this->palette ) ) {
				$palette = implode( '|', $this->palette );
			} else {
				// Default to true.
				$palette = ( false === $this->palette || 'false' === $this->palette ) ? 'false' : 'true';
			}

			// Support passing show_opacity as string or boolean. Default to true.
			$show_opacity = ( false === $this->show_opacity || 'false' === $this->show_opacity ) ? 'false' : 'true';

			?>
            <label>
				<?php // Output the label and description if they were passed in.
				if ( isset( $this->label ) && '' !== $this->label ) {
					echo '<span class="customize-control-title">' . sanitize_text_field( $this->label ) . '</span>';
				}
				if ( isset( $this->description ) && '' !== $this->description ) {
					echo '<span class="description customize-control-description">' . sanitize_text_field( $this->description ) . '</span>';
				} ?>
            </label>
            <input class="alpha-color-control" type="text" data-show-opacity="<?php echo esc_attr( $show_opacity ); ?>" data-palette="<?php echo esc_attr( $palette ); ?>"
                 data-default-color="<?php echo esc_attr( $this->settings['default']->default ); ?>" <?php $this->link(); ?> />
			<?php
		}
	}
}