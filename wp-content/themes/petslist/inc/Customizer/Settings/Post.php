<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

namespace RadiusTheme\Petslist\Customizer\Settings;

use RadiusTheme\Petslist\Customizer\Controls\Switcher;
use RadiusTheme\Petslist\Customizer\Customizer;

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class Post extends Customizer {

	public function __construct() {
		parent::instance();
		$this->populated_default_data();
		// Add Controls
		add_action( 'customize_register', [ $this, 'register_single_post_controls' ] );
	}

	public function register_single_post_controls( $wp_customize ) {
		$wp_customize->add_setting( 'post_date',
			[
				'default'           => $this->defaults['post_date'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'post_date',
			[
				'label'   => esc_html__( 'Display Date', 'petslist' ),
				'section' => 'single_post_section',
			]
		) );

		$wp_customize->add_setting( 'post_author_name',
			[
				'default'           => $this->defaults['post_author_name'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'post_author_name',
			[
				'label'   => esc_html__( 'Display Author Name', 'petslist' ),
				'section' => 'single_post_section',
			]
		) );

		$wp_customize->add_setting( 'post_comment_num',
			[
				'default'           => $this->defaults['post_comment_num'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'post_comment_num',
			[
				'label'   => esc_html__( 'Display Comment Count', 'petslist' ),
				'section' => 'single_post_section',
			]
		) );

		$wp_customize->add_setting( 'post_cats',
			[
				'default'           => $this->defaults['post_cats'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'post_cats',
			[
				'label'   => esc_html__( 'Display Category', 'petslist' ),
				'section' => 'single_post_section',
			]
		) );

		$wp_customize->add_setting( 'post_details_related_section',
			[
				'default'           => $this->defaults['post_details_related_section'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'post_details_related_section',
			[
				'label'   => esc_html__( 'Display Related Posts', 'petslist' ),
				'section' => 'single_post_section',
			]
		) );

		$wp_customize->add_setting( 'post_tag',
			[
				'default'           => $this->defaults['post_tag'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'post_tag',
			[
				'label'   => esc_html__( 'Display Tag', 'petslist' ),
				'section' => 'single_post_section',
			]
		) );

		$wp_customize->add_setting( 'post_social_icon',
			[
				'default'           => $this->defaults['post_social_icon'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'post_social_icon',
			[
				'label'   => esc_html__( 'Display Social Share', 'petslist' ),
				'section' => 'single_post_section',
			]
		) );

	}
}