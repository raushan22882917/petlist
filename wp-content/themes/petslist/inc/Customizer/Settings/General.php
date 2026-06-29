<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace RadiusTheme\Petslist\Customizer\Settings;

use RadiusTheme\Petslist\Customizer\Controls\Separator;
use RadiusTheme\Petslist\Customizer\Controls\Switcher;
use RadiusTheme\Petslist\Customizer\Controls\Heading;
use RadiusTheme\Petslist\Customizer\Customizer;
use WP_Customize_Media_Control;

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class General extends Customizer {

	public function __construct() {
		parent::instance();
		$this->populated_default_data();
		// Add Controls
		add_action( 'customize_register', [ $this, 'register_general_controls' ] );
	}

	public function register_general_controls( $wp_customize ) {
		// Main Logo
		$wp_customize->add_setting( 'logo',
			[
				'default'           => $this->defaults['logo'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'absint',
			]
		);
		$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'logo',
			[
				'label'         => esc_html__( 'Main Logo', 'petslist' ),
				'description'   => esc_html__( 'Add site main logo', 'petslist' ),
				'section'       => 'general_section',
				'mime_type'     => 'image',
				'button_labels' => [
					'select'       => esc_html__( 'Select Logo', 'petslist' ),
					'change'       => esc_html__( 'Change Logo', 'petslist' ),
					'default'      => esc_html__( 'Default', 'petslist' ),
					'remove'       => esc_html__( 'Remove', 'petslist' ),
					'placeholder'  => esc_html__( 'No file selected', 'petslist' ),
					'frame_title'  => esc_html__( 'Select File', 'petslist' ),
					'frame_button' => esc_html__( 'Choose File', 'petslist' ),
				],
			]
		) );

		$wp_customize->selective_refresh->add_partial( 'logo', [
			'selector'        => '.site-logo',
			'render_callback' => '__return_false',
		] );

		// White logo
		$wp_customize->add_setting( 'logo_dark',
			[
				'default'           => $this->defaults['logo_dark'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'absint',
			]
		);
		$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'logo_dark',
			[
				'label'         => esc_html__( 'Dark Logo', 'petslist' ),
				'description'   => esc_html__( 'Add logo for transparent header', 'petslist' ),
				'section'       => 'general_section',
				'mime_type'     => 'image',
				'button_labels' => [
					'select'       => esc_html__( 'Select Logo', 'petslist' ),
					'change'       => esc_html__( 'Change Logo', 'petslist' ),
					'default'      => esc_html__( 'Default', 'petslist' ),
					'remove'       => esc_html__( 'Remove', 'petslist' ),
					'placeholder'  => esc_html__( 'No file selected', 'petslist' ),
					'frame_title'  => esc_html__( 'Select File', 'petslist' ),
					'frame_button' => esc_html__( 'Choose File', 'petslist' ),
				],
			]
		) );

		// Mobile Logo
		$wp_customize->add_setting( 'mobile_logo',
			[
				'default'           => $this->defaults['mobile_logo'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'absint',
			]
		);
		$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'mobile_logo',
			[
				'label'         => esc_html__( 'Mobile Logo', 'petslist' ),
				'description'   => esc_html__( 'Add logo for mobile header', 'petslist' ),
				'section'       => 'general_section',
				'mime_type'     => 'image',
				'button_labels' => [
					'select'       => esc_html__( 'Select Logo', 'petslist' ),
					'change'       => esc_html__( 'Change Logo', 'petslist' ),
					'default'      => esc_html__( 'Default', 'petslist' ),
					'remove'       => esc_html__( 'Remove', 'petslist' ),
					'placeholder'  => esc_html__( 'No file selected', 'petslist' ),
					'frame_title'  => esc_html__( 'Select File', 'petslist' ),
					'frame_button' => esc_html__( 'Choose File', 'petslist' ),
				],
			]
		) );

		// Logo Width
		$wp_customize->add_setting( 'logo_width',
			[
				'default'           => $this->defaults['logo_width'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'logo_width',
			[
				'label'           => esc_html__( 'Logo max width', 'petslist' ),
				'section'         => 'general_section',
				'type'            => 'text',
				'description'     => esc_html__( 'Enter logo width. Eg: 200px', 'petslist' ),
				'input_attrs'     => [
					'placeholder' => esc_html__( '200px', 'petslist' ),
				],
			]
		);

		// Separator
		$wp_customize->add_setting( 'separator_general1', [
			'default'           => '',
			'sanitize_callback' => 'esc_html',
		] );
		$wp_customize->add_control( new Separator( $wp_customize, 'separator_general1', [
			'settings' => 'separator_general1',
			'section'  => 'general_section',
		] ) );

		// Banner Image
		$wp_customize->add_setting( 'banner_image',
			[
				'default'           => $this->defaults['banner_image'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'absint',
			]
		);
		$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'banner_image',
			[
				'label'         => esc_html__( 'Banner Image', 'petslist' ),
				'description'   => esc_html__( 'Add banner image to change default image', 'petslist' ),
				'section'       => 'general_section',
				'mime_type'     => 'image',
				'button_labels' => [
					'select'       => esc_html__( 'Select Image', 'petslist' ),
					'change'       => esc_html__( 'Change Image', 'petslist' ),
					'default'      => esc_html__( 'Default', 'petslist' ),
					'remove'       => esc_html__( 'Remove', 'petslist' ),
					'placeholder'  => esc_html__( 'No file selected', 'petslist' ),
					'frame_title'  => esc_html__( 'Select File', 'petslist' ),
					'frame_button' => esc_html__( 'Choose File', 'petslist' ),
				],
			]
		) );

		/**
         * Heading
         */
        $wp_customize->add_setting('site_switching', array(
            'default' => '',
            'sanitize_callback' => 'esc_html',
        ));
        $wp_customize->add_control(new Heading($wp_customize, 'site_switching', array(
            'label' => esc_html__( 'Site Switch Control', 'petslist' ),
            'section' => 'general_section',
        )));

		// Preloader
		$wp_customize->add_setting( 'preloader',
			[
				'default'           => $this->defaults['preloader'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'preloader',
			[
				'label'   => esc_html__( 'Preloader', 'petslist' ),
				'section' => 'general_section',
			]
		) );

		// Preloader gif image
		$wp_customize->add_setting( 'preloader_gif',
			[
				'default'           => $this->defaults['preloader_gif'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'absint',
			]
		);
		$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'preloader_gif',
			[
				'label'         => esc_html__( 'Loading Image', 'petslist' ),
				'description'   => esc_html__( 'Page loading gif/animated image', 'petslist' ),
				'section'       => 'general_section',
				'mime_type'     => 'image',
				'button_labels' => [
					'select'       => esc_html__( 'Select Image', 'petslist' ),
					'change'       => esc_html__( 'Change Image', 'petslist' ),
					'default'      => esc_html__( 'Default', 'petslist' ),
					'remove'       => esc_html__( 'Remove', 'petslist' ),
					'placeholder'  => esc_html__( 'No file selected', 'petslist' ),
					'frame_title'  => esc_html__( 'Select File', 'petslist' ),
					'frame_button' => esc_html__( 'Choose File', 'petslist' ),
				],
				'active_callback' => [ '\RadiusTheme\Petslist\Helper', 'is_preloader_enabled' ],
			]
		) );


		// Back to top
		$wp_customize->add_setting( 'back_to_top',
			[
				'default'           => $this->defaults['back_to_top'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'back_to_top',
			[
				'label'   => esc_html__( 'Back to Top', 'petslist' ),
				'section' => 'general_section',
			]
		) );


		// Hide admin bar
		$wp_customize->add_setting( 'remove_admin_bar',
			[
				'default'           => $this->defaults['remove_admin_bar'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'remove_admin_bar',
			[
				'label'       => esc_html__( 'Remove Admin Bar', 'petslist' ),
				'section'     => 'general_section',
				'description' => esc_html__( 'This option not work for administrator users.', 'petslist' ),
			]
		) );

	}

}
