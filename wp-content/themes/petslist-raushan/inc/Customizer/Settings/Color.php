<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

namespace RadiusTheme\Petslist\Customizer\Settings;

use RadiusTheme\Petslist\Customizer\Customizer;
use WP_Customize_Color_Control;

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class Color extends Customizer {

	public function __construct() {
		parent::instance();
		$this->populated_default_data();
		// Add Controls
		add_action( 'customize_register', [ $this, 'register_color_controls' ] );
	}

	public function register_color_controls( $wp_customize ) {

		$this->__site_color_settings_controls($wp_customize);
		$this->__button_color_settings_controls($wp_customize);
	}

	/* Site Color
	==================================================================================================*/
	protected function __site_color_settings_controls($wp_customize) {
		// Primary Color
		$wp_customize->add_setting( 'primary_color',
			[
				'default'           => $this->defaults['primary_color'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_hex_color',
			]
		);
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'primary_color',
			[
				'label'   => esc_html__( 'Primary Color', 'petslist' ),
				'section' => 'site_color_section',
			]
		) );
		// Secondary Color
		$wp_customize->add_setting( 'secondary_color',
			[
				'default'           => $this->defaults['secondary_color'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_hex_color',
			]
		);
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'secondary_color',
			[
				'label'   => esc_html__( 'Secondary Color', 'petslist' ),
				'section' => 'site_color_section',
			]
		) );
		// Body Color
		$wp_customize->add_setting( 'body_color',
			[
				'default'           => $this->defaults['body_color'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_hex_color',
			]
		);
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'body_color',
			[
				'label'   => esc_html__( 'Body Color', 'petslist' ),
				'section' => 'site_color_section',
			]
		) );
		// Heading Color
		$wp_customize->add_setting( 'heading_color',
			[
				'default'           => $this->defaults['heading_color'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_hex_color',
			]
		);
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'heading_color',
			[
				'label'   => esc_html__( 'Heading Color', 'petslist' ),
				'section' => 'site_color_section',
			]
		) );
	}
	/* Button Color
	==================================================================================================*/
	protected function __button_color_settings_controls($wp_customize) {
		// Button Color 1
		$wp_customize->add_setting( 'button_color_1',
			[
				'default'           => $this->defaults['button_color_1'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_hex_color',
			]
		);
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'button_color_1',
			[
				'label'   => esc_html__( 'Button Color 1', 'petslist' ),
				'section' => 'button_color_section',
			]
		) );
		// Button Color 2
		$wp_customize->add_setting( 'button_color_2',
			[
				'default'           => $this->defaults['button_color_2'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_hex_color',
			]
		);
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'button_color_2',
			[
				'label'   => esc_html__( 'Button Color 2', 'petslist' ),
				'section' => 'button_color_section',
			]
		) );
	}
}