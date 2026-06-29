<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace RadiusTheme\Petslist\Customizer\Typography;

use RadiusTheme\Petslist\Customizer\Controls\Separator;
use RadiusTheme\Petslist\Customizer\Default_Data;

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class Typography {

	// Get our default values
	private $defaults;

	public function __construct() {
		// Get our Customizer defaults
		$this->defaults = Default_Data::default_values();
		// Register Section
		add_action( 'customize_register', [ $this, 'register_typography_sections' ] );
		// Register Controls
		add_action( 'customize_register', [ $this, 'register_typography_controls' ] );
	}

	/**
	 * Register the Typography sections
	 */
	public function register_typography_sections( $wp_customize ) {
		// Typography Panel
		$wp_customize->add_panel( 'rt_typo_panel',
			[
				'title'       => esc_html__( 'Typography', 'petslist' ),
				'description' => esc_html__( 'Change site typography.', 'petslist' ),
				'priority'    => 4,
			]
		);
		// Body
		$wp_customize->add_section( 'typography_body_section',
			[
				'title'    => esc_html__( 'Body', 'petslist' ),
				'priority' => 10,
				'panel'    => 'rt_typo_panel',
			]
		);
		// Heading
		$wp_customize->add_section( 'typography_heading_section',
			[
				'title'    => esc_html__( 'Heading', 'petslist' ),
				'priority' => 12,
				'panel'    => 'rt_typo_panel',
			]
		);
		// Menu
		$wp_customize->add_section( 'typography_menu_section',
			[
				'title'    => esc_html__( 'Menu', 'petslist' ),
				'priority' => 14,
				'panel'    => 'rt_typo_panel',
			]
		);
	}

	/**
	 * Register our -- general controls
	 */
	public function register_typography_controls( $wp_customize ) {
		// Test of Google Font Select Control
		$wp_customize->add_setting( 'typo_body',
			[
				'default'           => $this->defaults['typo_body'],
				'sanitize_callback' => 'rttheme_google_font_sanitization',
			]
		);
		$wp_customize->add_control( new Control( $wp_customize, 'typo_body',
			[
				'label'       => __( 'Body', 'petslist' ),
				'section'     => 'typography_body_section',
				'input_attrs' => [
					'font_count' => 'all',
					'orderby'    => 'popular',
				],
			]
		) );
		$wp_customize->add_setting( 'typo_body_size',
			[
				'default'           => $this->defaults['typo_body_size'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'typo_body_size',
			[
				'label'       => __( 'Font Size', 'petslist' ),
				'description' => esc_html__( 'Font Size (px)', 'petslist' ),
				'section'     => 'typography_body_section',
				'type'        => 'text',
				'input_attrs' => [
					'class' => 'rtt-txt-box',

				],
			]
		);
		$wp_customize->add_setting( 'typo_body_height',
			[
				'default'           => $this->defaults['typo_body_height'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'typo_body_height',
			[
				'label'       => __( 'Line Height', 'petslist' ),
				'description' => esc_html__( 'Line Height (px)', 'petslist' ),
				'section'     => 'typography_body_section',
				'type'        => 'text',
				'input_attrs' => [
					'class' => 'rtt-txt-box',

				],
			]
		);
		/*-----------------------
		    Heading Typography
		-----------------------*/

		// All Heading Typography
		$wp_customize->add_setting( 'typo_heading',
			[
				'default'           => $this->defaults['typo_heading'],
				'sanitize_callback' => 'rttheme_google_font_sanitization',
			]
		);
		$wp_customize->add_control( new Control( $wp_customize, 'typo_heading',
			[
				'label'       => esc_html__( 'All Heading Typography (H1-H6)', 'petslist' ),
				'section'     => 'typography_heading_section',
				'input_attrs' => [
					'font_count' => 'all',
					'orderby'    => 'popular',
				],
			]
		) );

		// Separator
		$wp_customize->add_setting( 'typo_separator_general1', [
			'default'           => '',
			'sanitize_callback' => 'esc_html',
		] );
		$wp_customize->add_control( new Separator( $wp_customize, 'typo_separator_general1', [
			'settings' => 'typo_separator_general1',
			'section'  => 'typography_heading_section',
		] ) );

		// H1 Google Font Select Control
		$wp_customize->add_setting( 'typo_h1',
			[
				'default'           => $this->defaults['typo_h1'],
				'sanitize_callback' => 'rttheme_google_font_sanitization',
			]
		);
		$wp_customize->add_control( new Control( $wp_customize, 'typo_h1',
			[
				'label'       => __( 'Header h1 ', 'petslist' ),
				'section'     => 'typography_heading_section',
				'input_attrs' => [
					'font_count' => 'all',
					'orderby'    => 'popular',
				],
			]
		) );
		$wp_customize->add_setting( 'typo_h1_size',
			[
				'default'           => $this->defaults['typo_h1_size'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'typo_h1_size',
			[
				'label'       => __( 'Font Size', 'petslist' ),
				'description' => esc_html__( 'Font Size (px)', 'petslist' ),
				'section'     => 'typography_heading_section',
				'type'        => 'text',
				'input_attrs' => [
					'class' => 'rtt-txt-box',

				],
			]
		);
		$wp_customize->add_setting( 'typo_h1_height',
			[
				'default'           => $this->defaults['typo_h1_height'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'typo_h1_height',
			[
				'label'       => __( 'Line Height', 'petslist' ),
				'description' => esc_html__( 'Line Height (px)', 'petslist' ),
				'section'     => 'typography_heading_section',
				'type'        => 'text',
				'input_attrs' => [
					'class' => 'rtt-txt-box',

				],
			]
		);

		// Separator
		$wp_customize->add_setting( 'typo_separator_general2', [
			'default'           => '',
			'sanitize_callback' => 'esc_html',
		] );
		$wp_customize->add_control( new Separator( $wp_customize, 'typo_separator_general2', [
			'settings' => 'typo_separator_general2',
			'section'  => 'typography_heading_section',
		] ) );

		// H2 Google Font Select Control
		$wp_customize->add_setting( 'typo_h2',
			[
				'default'           => $this->defaults['typo_h2'],
				'sanitize_callback' => 'rttheme_google_font_sanitization',
			]
		);
		$wp_customize->add_control( new Control( $wp_customize, 'typo_h2',
			[
				'label'       => __( 'Header h2 ', 'petslist' ),
				'section'     => 'typography_heading_section',
				'input_attrs' => [
					'font_count' => 'all',
					'orderby'    => 'popular',
				],
			]
		) );
		$wp_customize->add_setting( 'typo_h2_size',
			[
				'default'           => $this->defaults['typo_h2_size'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'typo_h2_size',
			[
				'label'       => __( 'Font Size', 'petslist' ),
				'description' => esc_html__( 'Font Size (px)', 'petslist' ),
				'section'     => 'typography_heading_section',
				'type'        => 'text',
				'input_attrs' => [
					'class' => 'rtt-txt-box',

				],
			]
		);
		$wp_customize->add_setting( 'typo_h2_height',
			[
				'default'           => $this->defaults['typo_h2_height'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'typo_h2_height',
			[
				'label'       => __( 'Line Height', 'petslist' ),
				'description' => esc_html__( 'Line Height (px)', 'petslist' ),
				'section'     => 'typography_heading_section',
				'type'        => 'text',
				'input_attrs' => [
					'class' => 'rtt-txt-box',

				],
			]
		);

		// Separator
		$wp_customize->add_setting( 'typo_separator_general3', [
			'default'           => '',
			'sanitize_callback' => 'esc_html',
		] );
		$wp_customize->add_control( new Separator( $wp_customize, 'typo_separator_general3', [
			'settings' => 'typo_separator_general3',
			'section'  => 'typography_heading_section',
		] ) );

		// H3 Google Font Select Control
		$wp_customize->add_setting( 'typo_h3',
			[
				'default'           => $this->defaults['typo_h3'],
				'sanitize_callback' => 'rttheme_google_font_sanitization',
			]
		);
		$wp_customize->add_control( new Control( $wp_customize, 'typo_h3',
			[
				'label'       => __( 'Header h3 ', 'petslist' ),
				'section'     => 'typography_heading_section',
				'input_attrs' => [
					'font_count' => 'all',
					'orderby'    => 'popular',
				],
			]
		) );
		$wp_customize->add_setting( 'typo_h3_size',
			[
				'default'           => $this->defaults['typo_h3_size'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'typo_h3_size',
			[
				'label'       => esc_html__( 'Font Size', 'petslist' ),
				'description' => esc_html__( 'Font Size (px)', 'petslist' ),
				'section'     => 'typography_heading_section',
				'type'        => 'text',
				'input_attrs' => [
					'class' => 'rtt-txt-box',

				],
			]
		);
		$wp_customize->add_setting( 'typo_h3_height',
			[
				'default'           => $this->defaults['typo_h3_height'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'typo_h3_height',
			[
				'label'       => esc_html__( 'Line Height', 'petslist' ),
				'description' => esc_html__( 'Line Height (px)', 'petslist' ),
				'section'     => 'typography_heading_section',
				'type'        => 'text',
				'input_attrs' => [
					'class' => 'rtt-txt-box',

				],
			]
		);

		// Separator
		$wp_customize->add_setting( 'typo_separator_general4', [
			'default'           => '',
			'sanitize_callback' => 'esc_html',
		] );
		$wp_customize->add_control( new Separator( $wp_customize, 'typo_separator_general4', [
			'settings' => 'typo_separator_general4',
			'section'  => 'typography_heading_section',
		] ) );

		// H4 Google Font Select Control
		$wp_customize->add_setting( 'typo_h4',
			[
				'default'           => $this->defaults['typo_h4'],
				'sanitize_callback' => 'rttheme_google_font_sanitization',
			]
		);
		$wp_customize->add_control( new Control( $wp_customize, 'typo_h4',
			[
				'label'       => esc_html__( 'Header h4 ', 'petslist' ),
				'section'     => 'typography_heading_section',
				'input_attrs' => [
					'font_count' => 'all',
					'orderby'    => 'popular',
				],
			]
		) );
		$wp_customize->add_setting( 'typo_h4_size',
			[
				'default'           => $this->defaults['typo_h4_size'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'typo_h4_size',
			[
				'label'       => esc_html__( 'Font Size', 'petslist' ),
				'description' => esc_html__( 'Font Size (px)', 'petslist' ),
				'section'     => 'typography_heading_section',
				'type'        => 'text',
				'input_attrs' => [
					'class' => 'rtt-txt-box',

				],
			]
		);
		$wp_customize->add_setting( 'typo_h4_height',
			[
				'default'           => $this->defaults['typo_h4_height'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'typo_h4_height',
			[
				'label'       => esc_html__( 'Line Height', 'petslist' ),
				'description' => esc_html__( 'Line Height (px)', 'petslist' ),
				'section'     => 'typography_heading_section',
				'type'        => 'text',
				'input_attrs' => [
					'class' => 'rtt-txt-box',

				],
			]
		);

		// Separator
		$wp_customize->add_setting( 'typo_separator_general5', [
			'default'           => '',
			'sanitize_callback' => 'esc_html',
		] );
		$wp_customize->add_control( new Separator( $wp_customize, 'typo_separator_general5', [
			'settings' => 'typo_separator_general5',
			'section'  => 'typography_heading_section',
		] ) );

		// H5 Google Font Select Control
		$wp_customize->add_setting( 'typo_h5',
			[
				'default'           => $this->defaults['typo_h5'],
				'sanitize_callback' => 'rttheme_google_font_sanitization',
			]
		);
		$wp_customize->add_control( new Control( $wp_customize, 'typo_h5',
			[
				'label'       => esc_html__( 'Header h5 ', 'petslist' ),
				'section'     => 'typography_heading_section',
				'input_attrs' => [
					'font_count' => 'all',
					'orderby'    => 'popular',
				],
			]
		) );
		$wp_customize->add_setting( 'typo_h5_size',
			[
				'default'           => $this->defaults['typo_h5_size'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'typo_h5_size',
			[
				'label'       => esc_html__( 'Font Size', 'petslist' ),
				'description' => esc_html__( 'Font Size (px)', 'petslist' ),
				'section'     => 'typography_heading_section',
				'type'        => 'text',
				'input_attrs' => [
					'class' => 'rtt-txt-box',

				],
			]
		);
		$wp_customize->add_setting( 'typo_h5_height',
			[
				'default'           => $this->defaults['typo_h5_height'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'typo_h5_height',
			[
				'label'       => esc_html__( 'Line Height', 'petslist' ),
				'description' => esc_html__( 'Line Height (px)', 'petslist' ),
				'section'     => 'typography_heading_section',
				'type'        => 'text',
				'input_attrs' => [
					'class' => 'rtt-txt-box',

				],
			]
		);

		// Separator
		$wp_customize->add_setting( 'typo_separator_general6', [
			'default'           => '',
			'sanitize_callback' => 'esc_html',
		] );
		$wp_customize->add_control( new Separator( $wp_customize, 'typo_separator_general6', [
			'settings' => 'typo_separator_general6',
			'section'  => 'typography_heading_section',
		] ) );

		// H6 Google Font Select Control
		$wp_customize->add_setting( 'typo_h6',
			[
				'default'           => $this->defaults['typo_h6'],
				'sanitize_callback' => 'rttheme_google_font_sanitization',
			]
		);
		$wp_customize->add_control( new Control( $wp_customize, 'typo_h6',
			[
				'label'       => esc_html__( 'Header h6 ', 'petslist' ),
				'section'     => 'typography_heading_section',
				'input_attrs' => [
					'font_count' => 'all',
					'orderby'    => 'popular',
				],
			]
		) );
		$wp_customize->add_setting( 'typo_h6_size',
			[
				'default'           => $this->defaults['typo_h6_size'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'typo_h6_size',
			[
				'label'       => esc_html__( 'Font Size', 'petslist' ),
				'description' => esc_html__( 'Font Size (px)', 'petslist' ),
				'section'     => 'typography_heading_section',
				'type'        => 'text',
				'input_attrs' => [
					'class' => 'rtt-txt-box',

				],
			]
		);
		$wp_customize->add_setting( 'typo_h6_height',
			[
				'default'           => $this->defaults['typo_h6_height'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'typo_h6_height',
			[
				'label'       => esc_html__( 'Line Height', 'petslist' ),
				'description' => esc_html__( 'Line Height (px)', 'petslist' ),
				'section'     => 'typography_heading_section',
				'type'        => 'text',
				'input_attrs' => [
					'class' => 'rtt-txt-box',

				],
			]
		);

		// Separator
		$wp_customize->add_setting( 'typo_separator_general7', [
			'default'           => '',
			'sanitize_callback' => 'esc_html',
		] );
		$wp_customize->add_control( new Separator( $wp_customize, 'typo_separator_general7', [
			'settings' => 'typo_separator_general7',
			'section'  => 'typography_heading_section',
		] ) );

		/*------------------------
			Menu Typography
		------------------------*/
		// Font Family
		$wp_customize->add_setting( 'typo_menu',
			[
				'default'           => $this->defaults['typo_menu'],
				'sanitize_callback' => 'rttheme_google_font_sanitization',
			]
		);
		$wp_customize->add_control( new Control( $wp_customize, 'typo_menu',
			[
				'label'       => esc_html__( 'Menu', 'petslist' ),
				'section'     => 'typography_menu_section',
				'input_attrs' => [
					'font_count' => 'all',
					'orderby'    => 'popular',
				],
			]
		) );
		// Font Size
		$wp_customize->add_setting( 'typo_menu_size',
			[
				'default'           => $this->defaults['typo_menu_size'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'typo_menu_size',
			[
				'label'       => esc_html__( 'Font Size', 'petslist' ),
				'description' => esc_html__( 'Font Size (px)', 'petslist' ),
				'section'     => 'typography_menu_section',
				'type'        => 'text',
				'input_attrs' => [
					'class' => 'rtt-txt-box',
				],
			]
		);
		// Line Height
		$wp_customize->add_setting( 'typo_menu_height',
			[
				'default'           => $this->defaults['typo_menu_height'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'typo_menu_height',
			[
				'label'       => esc_html__( 'Line Height', 'petslist' ),
				'description' => esc_html__( 'Line Height (px)', 'petslist' ),
				'section'     => 'typography_menu_section',
				'type'        => 'text',
				'input_attrs' => [
					'class' => 'rtt-txt-box',
				],
			]
		);

		/**
		 * Sub Menu Typography
		 */
		$wp_customize->add_setting( 'typo_submenu_separator', [
			'default'           => '',
			'sanitize_callback' => 'esc_html',
		] );
		$wp_customize->add_control( new Separator( $wp_customize, 'typo_submenu_separator', [
			'settings' => 'typo_submenu_separator',
			'section'  => 'typography_menu_section',
		] ) );
		// Font Size
		$wp_customize->add_setting( 'typo_submenu_size',
			[
				'default'           => $this->defaults['typo_submenu_size'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'typo_submenu_size',
			[
				'label'       => __( 'Sub Menu Font Size', 'petslist' ),
				'description' => esc_html__( 'Font Size (px)', 'petslist' ),
				'section'     => 'typography_menu_section',
				'type'        => 'text',
				'input_attrs' => [
					'class' => 'rtt-txt-box',
				],
			]
		);
		// Line Height
		$wp_customize->add_setting( 'typo_submenu_height',
			[
				'default'           => $this->defaults['typo_submenu_height'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'typo_submenu_height',
			[
				'label'       => __( 'Sub Menu Line Height', 'petslist' ),
				'description' => esc_html__( 'Line Height (px)', 'petslist' ),
				'section'     => 'typography_menu_section',
				'type'        => 'text',
				'input_attrs' => [
					'class' => 'rtt-txt-box',
				],
			]
		);
	}
}