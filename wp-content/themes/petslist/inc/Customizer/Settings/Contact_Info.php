<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

namespace RadiusTheme\Petslist\Customizer\Settings;

use RadiusTheme\Petslist\Customizer\Controls\Separator;
use RadiusTheme\Petslist\Customizer\Customizer;

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class Contact_Info extends Customizer {

	public function __construct() {
		parent::instance();
		$this->populated_default_data();
		// Add Controls
		add_action( 'customize_register', [ $this, 'register_contact_controls' ] );
	}

	public function register_contact_controls( $wp_customize ) {
		/**
		 * Separator
		 */
		$wp_customize->add_setting( 'social_separator',
			[
				'default'           => '',
				'sanitize_callback' => 'esc_html',
			] );
		$wp_customize->add_control( new Separator( $wp_customize, 'social_separator', [
			'settings' => 'social_separator',
			'section'  => 'contact_info_section',
		] ) );
		// Facebook
		$wp_customize->add_setting( 'facebook',
			[
				'default'           => $this->defaults['facebook'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'esc_url',
			]
		);
		$wp_customize->add_control( 'facebook',
			[
				'label'   => __( 'Facebook', 'petslist' ),
				'section' => 'contact_info_section',
				'type'    => 'url',
			]
		);
		// Twitter
		$wp_customize->add_setting( 'twitter',
			[
				'default'           => $this->defaults['twitter'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'esc_url',
			]
		);
		$wp_customize->add_control( 'twitter',
			[
				'label'   => __( 'Twitter', 'petslist' ),
				'section' => 'contact_info_section',
				'type'    => 'url',
			]
		);
		// Instagram
		$wp_customize->add_setting( 'instagram',
			[
				'default'           => $this->defaults['instagram'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'esc_url',
			]
		);
		$wp_customize->add_control( 'instagram',
			[
				'label'   => __( 'Instagram', 'petslist' ),
				'section' => 'contact_info_section',
				'type'    => 'url',
			]
		);
		// Linkedin
		$wp_customize->add_setting( 'linkedin',
			[
				'default'           => $this->defaults['linkedin'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'esc_url',
			]
		);
		$wp_customize->add_control( 'linkedin',
			[
				'label'   => __( 'Linkedin', 'petslist' ),
				'section' => 'contact_info_section',
				'type'    => 'url',
			]
		);
		// Youtube
		$wp_customize->add_setting( 'youtube',
			[
				'default'           => $this->defaults['youtube'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'esc_url',
			]
		);
		$wp_customize->add_control( 'youtube',
			[
				'label'   => __( 'Youtube', 'petslist' ),
				'section' => 'contact_info_section',
				'type'    => 'url',
			]
		);
		// Pinterest
		$wp_customize->add_setting( 'pinterest',
			[
				'default'           => $this->defaults['pinterest'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'esc_url',
			]
		);
		$wp_customize->add_control( 'pinterest',
			[
				'label'   => __( 'Pinterest', 'petslist' ),
				'section' => 'contact_info_section',
				'type'    => 'url',
			]
		);
		// Skype
		$wp_customize->add_setting( 'skype',
			[
				'default'           => $this->defaults['skype'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'esc_url',
			]
		);
		$wp_customize->add_control( 'skype',
			[
				'label'   => __( 'Skype', 'petslist' ),
				'section' => 'contact_info_section',
				'type'    => 'url',
			]
		);
	}
}