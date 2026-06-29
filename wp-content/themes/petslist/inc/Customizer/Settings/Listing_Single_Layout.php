<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

namespace RadiusTheme\Petslist\Customizer\Settings;

use RadiusTheme\Petslist\Customizer\Customizer;
use RadiusTheme\Petslist\Helper;

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class Listing_Single_Layout extends Customizer {

	public function __construct() {
		parent::instance();
		$this->populated_default_data();
		// Register Page Controls
		add_action( 'customize_register', [ $this, 'register_listing_single_layout_controls' ] );
	}

	public function register_listing_single_layout_controls( $wp_customize ) {

		// Header Layout
		$wp_customize->add_setting( 'listing_single_header_style',
			[
				'default'           => $this->defaults['listing_single_header_style'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'listing_single_header_style', [
			'type'    => 'select',
			'section' => 'listing_single_layout_section',
			'label'   => esc_html__( 'Header Layout', 'petslist' ),
			'choices' => Helper::get_header_list(),
		] );

		// Menu Alignment
		$wp_customize->add_setting( 'listing_single_menu_alignment', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'rttheme_text_sanitization',
			'default'           => $this->defaults['menu_alignment'],
		] );

		$wp_customize->add_control( 'listing_single_menu_alignment', [
			'type'    => 'select',
			'section' => 'listing_single_layout_section', // Add a default or your own section
			'label'   => esc_html__( 'Menu Alignment', 'petslist' ),
			'choices' => [
				'default'     => esc_html__( 'Default', 'petslist' ),
				'menu-left'   => esc_html__( 'Left Alignment', 'petslist' ),
				'menu-center' => esc_html__( 'Center Alignment', 'petslist' ),
				'menu-right'  => esc_html__( 'Right Alignment', 'petslist' ),
			],
		] );

		// Header width
		$wp_customize->add_setting( 'listing_single_header_width', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'rttheme_text_sanitization',
			'default'           => $this->defaults['listing_single_header_width'],
		] );

		$wp_customize->add_control( 'listing_single_header_width', [
			'type'    => 'select',
			'section' => 'listing_single_layout_section', // Add a default or your own section
			'label'   => esc_html__( 'Header Width', 'petslist' ),
			'choices' => [
				'default'   => esc_html__( 'Default', 'petslist' ),
				'box-width' => esc_html__( 'Box width', 'petslist' ),
				'fullwidth' => esc_html__( 'Fullwidth', 'petslist' ),
			],
		] );

		// Breadcrumb
		$wp_customize->add_setting( 'listing_single_breadcrumb',
			[
				'default'           => $this->defaults['listing_single_breadcrumb'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'listing_single_breadcrumb', [
			'type'    => 'select',
			'section' => 'listing_single_layout_section',
			'label'   => esc_html__( 'Breadcrumb', 'petslist' ),
			'choices' => [
				'default' => esc_html__( 'Default', 'petslist' ),
				'on'      => esc_html__( 'Enable', 'petslist' ),
				'off'     => esc_html__( 'Disable', 'petslist' ),
			],
		] );

		// Padding Top
		$wp_customize->add_setting( 'listing_single_padding_top',
			[
				'default'           => $this->defaults['listing_single_padding_top'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'listing_single_padding_top',
			[
				'label'       => esc_html__( 'Content Padding Top', 'petslist' ),
				'description' => esc_html__( 'Listing Single Content Padding Top ', 'petslist' ),
				'section'     => 'listing_single_layout_section',
				'type'        => 'text',
			]
		);

		// Padding Bottom
		$wp_customize->add_setting( 'listing_single_padding_bottom',
			[
				'default'           => $this->defaults['listing_single_padding_bottom'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'listing_single_padding_bottom',
			[
				'label'       => esc_html__( 'Content Padding Bottom', 'petslist' ),
				'description' => esc_html__( 'Listing Single Content Padding Bottom', 'petslist' ),
				'section'     => 'listing_single_layout_section',
				'type'        => 'text',
			]
		);

		// Footer Layout
		$wp_customize->add_setting( 'listing_single_footer_style',
			[
				'default'           => $this->defaults['listing_single_footer_style'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_text_sanitization',
			]
		);
		$wp_customize->add_control( 'listing_single_footer_style', [
			'type'    => 'select',
			'section' => 'listing_single_layout_section',
			'label'   => esc_html__( 'Footer Layout', 'petslist' ),
			'choices' => Helper::get_footer_list(),
		] );
	}
}