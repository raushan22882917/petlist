<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace RadiusTheme\Petslist\Customizer\Settings;

use RadiusTheme\Petslist\Customizer\Controls\Image_Radio;
use RadiusTheme\Petslist\Customizer\Controls\Switcher;
use RadiusTheme\Petslist\Customizer\Controls\Heading;
use RadiusTheme\Petslist\Customizer\Customizer;
use RadiusTheme\Petslist\Helper;

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class Header extends Customizer {

	public function __construct() {
		parent::instance();
		$this->populated_default_data();
		// Add Controls
		add_action( 'customize_register', [ $this, 'register_header_controls' ] );
	}

	public function register_header_controls( $wp_customize ) {
		// Header Style
		$wp_customize->add_setting( 'header_style',
			[
				'default'           => $this->defaults['header_style'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_radio_sanitization',
			]
		);
		$wp_customize->add_control( new Image_Radio( $wp_customize, 'header_style',
			[
				'label'       => esc_html__( 'Header Layout', 'petslist' ),
				'description' => esc_html__( 'Select the header style', 'petslist' ),
				'section'     => 'header_main_section',
				'choices'     => Helper::get_header_list( 'header' ),
			]
		) );

		//Menu Alignment
		$wp_customize->add_setting( 'menu_alignment', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'rttheme_text_sanitization',
			'default'           => $this->defaults['menu_alignment'],
		] );

		$wp_customize->add_control( 'menu_alignment', [
			'type'    => 'select',
			'section' => 'header_main_section', // Add a default or your own section
			'label'   => esc_html__( 'Menu Alignment', 'petslist' ),
			'choices' => [
				'menu-left'   => esc_html__( 'Left Alignment', 'petslist' ),
				'menu-center' => esc_html__( 'Center Alignment', 'petslist' ),
				'menu-right'  => esc_html__( 'Right Alignment', 'petslist' ),
			],
		] );

		//Header width
		$wp_customize->add_setting( 'header_width', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'rttheme_text_sanitization',
			'default'           => $this->defaults['header_width'],
		] );

		$wp_customize->add_control( 'header_width', [
			'type'    => 'select',
			'section' => 'header_main_section', // Add a default or your own section
			'label'   => esc_html__( 'Header Width', 'petslist' ),
			'choices' => [
				'box-width' => esc_html__( 'Box width', 'petslist' ),
				'fullwidth' => esc_html__( 'Fullwidth', 'petslist' ),
			],
		] );
		
		/* = Header Content Control
		=====================================================*/
		// Sticky Header Control
		$wp_customize->add_setting( 'sticky_header',
			[
				'default'           => $this->defaults['sticky_header'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'sticky_header',
			[
				'label'       => esc_html__( 'Sticky Header', 'petslist' ),
				'description' => esc_html__( 'Show header at the top when scrolling down', 'petslist' ),
				'section'     => 'header_switches_section',
			]
		) );

		// Button Control
		$wp_customize->add_setting( 'header_btn',
			[
				'default'           => $this->defaults['header_btn'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'header_btn',
			[
				'label'   => esc_html__( 'Header Link Button', 'petslist' ),
				'section' => 'header_switches_section',
			]
		) );

		// Button Text
		$wp_customize->add_setting( 'header_btn_txt',
			[
				'default'           => $this->defaults['header_btn_txt'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'header_btn_txt',
			[
				'label'           => esc_html__( 'Button Text', 'petslist' ),
				'section'         => 'header_switches_section',
				'type'            => 'text',
				'active_callback' => [ '\RadiusTheme\Petslist\Helper', 'is_header_link_btn_enabled' ],
			]
		);
		// Button URL
		$wp_customize->add_setting( 'header_btn_url',
			[
				'default'           => $this->defaults['header_btn_url'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_url_sanitization',
			]
		);
		$wp_customize->add_control( 'header_btn_url',
			[
				'label'           => esc_html__( 'Button Link', 'petslist' ),
				'section'         => 'header_switches_section',
				'type'            => 'url',
				'active_callback' => [ '\RadiusTheme\Petslist\Helper', 'is_header_link_btn_enabled' ],
			]
		);

		// Header Login Icon Visibility
		$wp_customize->add_setting( 'header_login_icon',
			[
				'default'           => $this->defaults['header_login_icon'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'header_login_icon',
			[
				'label'   => esc_html__( 'Header Login Button', 'petslist' ),
				'section' => 'header_switches_section',
			]
		) );
		// Login Button Text
		$wp_customize->add_setting( 'header_login_text',
			[
				'default'           => $this->defaults['header_login_text'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'header_login_text',
			[
				'label'           => esc_html__( 'Login Button Text', 'petslist' ),
				'section'         => 'header_switches_section',
				'type'            => 'text',
				'active_callback' => [ '\RadiusTheme\Petslist\Helper', 'is_header_login_enabled' ],
			]
		);

		// Header Chat Icon 
		$wp_customize->add_setting( 'header_chat_icon',
			[
				'default'           => $this->defaults['header_chat_icon'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'header_chat_icon',
			[
				'label'   => esc_html__( 'Header Chat Button', 'petslist' ),
				'section' => 'header_switches_section',
			]
		) );
		// Chat Button Text
		$wp_customize->add_setting( 'header_chat_text',
			[
				'default'           => $this->defaults['header_chat_text'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'header_chat_text',
			[
				'label'           => esc_html__( 'Chat Button Text', 'petslist' ),
				'section'         => 'header_switches_section',
				'type'            => 'text',
				'active_callback' => [ '\RadiusTheme\Petslist\Helper', 'is_header_chat_enabled' ],
			]
		);

		// Breadcrumb
		$wp_customize->add_setting( 'breadcrumb',
			[
				'default'           => $this->defaults['breadcrumb'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'breadcrumb',
			[
				'label'   => esc_html__( 'Breadcrumb Visibility', 'petslist' ),
				'section' => 'header_switches_section',
			]
		) );

		/* = Mobile Devices Content Control
		=====================================================*/
		$wp_customize->add_setting('mobile_devices_control', array(
            'default' => '',
            'sanitize_callback' => 'esc_html',
        ));
        $wp_customize->add_control(new Heading($wp_customize, 'mobile_devices_control', array(
            'label' => esc_html__( 'Mobile Devices', 'petslist' ),
            'section' => 'header_switches_section',
        )));

		$wp_customize->add_setting( 'header_link_btn_mobile',
			[
				'default'           => $this->defaults['header_link_btn_mobile'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'header_link_btn_mobile',
			[
				'label'   => esc_html__( 'Link button on mobile', 'petslist' ),
				'section' => 'header_switches_section',
			]
		) );

		$wp_customize->add_setting( 'header_login_btn_mobile',
			[
				'default'           => $this->defaults['header_login_btn_mobile'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'header_login_btn_mobile',
			[
				'label'   => esc_html__( 'Link button on mobile', 'petslist' ),
				'section' => 'header_switches_section',
			]
		) );

		$wp_customize->add_setting( 'header_chat_btn_mobile',
			[
				'default'           => $this->defaults['header_chat_btn_mobile'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'header_chat_btn_mobile',
			[
				'label'   => esc_html__( 'Chat button on mobile', 'petslist' ),
				'section' => 'header_switches_section',
			]
		) );

	}
}