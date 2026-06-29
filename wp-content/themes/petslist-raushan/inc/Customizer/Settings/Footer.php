<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

namespace RadiusTheme\Petslist\Customizer\Settings;

use RadiusTheme\Petslist\Customizer\Controls\Image_Radio;
use RadiusTheme\Petslist\Customizer\Controls\Switcher;
use RadiusTheme\Petslist\Customizer\Controls\Heading;
use RadiusTheme\Petslist\Customizer\Customizer;
use RadiusTheme\Petslist\Helper;
use WP_Customize_Media_Control;

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class Footer extends Customizer {

	public function __construct() {
		parent::instance();
		$this->populated_default_data();
		// Add Controls
		add_action( 'customize_register', [ $this, 'register_footer_controls' ] );
	}

	public function register_footer_controls( $wp_customize ) {

		/* -- Footer General -- */
		$this->__footer_general_settings_controls($wp_customize);
        /* -- Footer 1 -- */
		$this->__footer_1_settings_controls($wp_customize);
        /* -- Footer 2 -- */
		$this->__footer_2_settings_controls($wp_customize);
        /* -- Footer 3 -- */
		$this->__footer_3_settings_controls($wp_customize);
	}
	
	/* Footer General Settiings
	=======================================================================*/
	protected function __footer_general_settings_controls($wp_customize) {
        // Footer Style
		$wp_customize->add_setting( 'footer_style',
			[
				'default'           => $this->defaults['footer_style'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_radio_sanitization',
			]
		);
		$wp_customize->add_control( new Image_Radio( $wp_customize, 'footer_style',
			[
				'label'       => esc_html__( 'Footer Lpetslistut', 'petslist' ),
				'description' => esc_html__( 'Select the header style', 'petslist' ),
				'section'     => 'footer_all_section',
				'choices'     => Helper::get_footer_list( 'footer' ),
			]
		) );

		// Copyright Area Control
		$wp_customize->add_setting( 'copyright_area',
			[
				'default'           => $this->defaults['copyright_area'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control( new Switcher( $wp_customize, 'copyright_area',
			[
				'label'   => esc_html__( 'Display Copyright Area', 'petslist' ),
				'section' => 'footer_all_section',
			]
		) );

		// Copyright Text
		$wp_customize->add_setting( 'copyright_text',
			[
				'default'           => $this->defaults['copyright_text'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_textarea_field',
			]
		);
		$wp_customize->add_control( 'copyright_text',
			[
				'label'           => esc_html__( 'Copyright Text', 'petslist' ),
				'section'         => 'footer_all_section',
				'type'            => 'textarea',
				'active_callback' => [ '\RadiusTheme\Petslist\Helper', 'is_copyright_area_enabled' ],
			]
		);
    }

	/* = Footer 1 Settiings
    =======================================================================*/
	protected function __footer_1_settings_controls($wp_customize) {
        
        $wp_customize->add_setting('f1_widgets_area', array(
            'default' => '',
            'sanitize_callback' => 'esc_html',
        ));
        $wp_customize->add_control(new Heading($wp_customize, 'f1_widgets_area', array(
            'label' => esc_html__( 'Footer 1 Settings', 'petslist' ),
            'section' => 'footer_1',
        )));
        // Background image
        $wp_customize->add_setting( 'f1_bg_img',
            array(
                'default' => $this->defaults['f1_bg_img'],
                'transport' => 'refresh',
                'sanitize_callback' => 'absint',
            )
        );
        $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'f1_bg_img',
            array(
                'label' => __( 'Background Image', 'petslist' ),
                'description' => esc_html__( 'This is the description for the Media Control', 'petslist' ),
                'section' => 'footer_1',
                'mime_type' => 'image',
                'button_labels' => array(
                    'select' => __( 'Select File', 'petslist' ),
                    'change' => __( 'Change File', 'petslist' ),
                    'default' => __( 'Default', 'petslist' ),
                    'remove' => __( 'Remove', 'petslist' ),
                    'placeholder' => __( 'No file selected', 'petslist' ),
                    'frame_title' => __( 'Select File', 'petslist' ),
                    'frame_button' => __( 'Choose File', 'petslist' ),
                ),
            )
        ) );
        // Background color
        $wp_customize->add_setting( 'f1_bg_color',
            array(
                'default' => $this->defaults['f1_bg_color'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_text_sanitization',
            )
        );
        $wp_customize->add_control( 'f1_bg_color',
            array(
                'label' => esc_html__( 'Background Color', 'petslist' ),
                'section' => 'footer_1',
                'type' => 'color',
            )
        );
        // Background color opacity
        $wp_customize->add_setting( 'f1_bg_opacity',
            array(
                'default' => $this->defaults['f1_bg_opacity'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_sanitize_integer',
            )
        );
        $wp_customize->add_control( 'f1_bg_opacity',
            array(
                'label' => esc_html__( 'Background Opacity', 'petslist' ),
                'section' => 'footer_1',
                'type' => 'number',
            )
        );
        // Copyright Background color
        $wp_customize->add_setting( 'f1_cr_bg_color',
            array(
                'default' => $this->defaults['f1_cr_bg_color'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_text_sanitization',
            )
        );
        $wp_customize->add_control( 'f1_cr_bg_color',
            array(
                'label' => esc_html__( 'Copyright Background Color', 'petslist' ),
                'section' => 'footer_1',
                'type' => 'color',
            )
        );
        // Widgets Area
        $wp_customize->add_setting( 'f1_widgets_area',
            array(
                'default' => $this->defaults['f1_widgets_area'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_radio_sanitization',
            )
        );
        $wp_customize->add_control( 'f1_widgets_area',
            array(
                'label'   => esc_html__( 'Widget Area', 'petslist' ),
                'section' => 'footer_1',
                'type'    => 'select',
                'choices' => Helper::rt_number_options(),
            )
        );
        $wp_customize->add_setting( 'f1_area1_column',
            array(
                'default' => $this->defaults['f1_area1_column'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_radio_sanitization',
            )
        );
        $wp_customize->add_control( 'f1_area1_column',
            array(
                'label' => esc_html__( 'Area 1 Columns', 'petslist' ),
                'section' => 'footer_1',
                'type' => 'select',
                'choices' => Helper::rt_grid_options(),
                'description' => esc_html__( 'Total Columns 12', 'petslist' ),
            )
        );
        $wp_customize->add_setting( 'f1_area2_column',
            array(
                'default' => $this->defaults['f1_area2_column'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_radio_sanitization',
            )
        );
        $wp_customize->add_control( 'f1_area2_column',
            array(
                'label' => esc_html__( 'Area 2 Columns', 'petslist' ),
                'section' => 'footer_1',
                'type' => 'select',
                'choices' => Helper::rt_grid_options(),
                'description' => esc_html__( 'Total Columns 12', 'petslist' ),
            )
        );
        $wp_customize->add_setting( 'f1_area3_column',
            array(
                'default' => $this->defaults['f1_area3_column'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_radio_sanitization',
            )
        );
        $wp_customize->add_control( 'f1_area3_column',
            array(
                'label' => esc_html__( 'Area 3 Columns', 'petslist' ),
                'section' => 'footer_1',
                'type' => 'select',
                'choices' => Helper::rt_grid_options(),
                'description' => esc_html__( 'Total Columns 12', 'petslist' ),
            )
        );
        $wp_customize->add_setting( 'f1_area4_column',
            array(
                'default' => $this->defaults['f1_area4_column'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_radio_sanitization',
            )
        );
        $wp_customize->add_control( 'f1_area4_column',
            array(
                'label' => esc_html__( 'Area 4 Columns', 'petslist' ),
                'section' => 'footer_1',
                'type' => 'select',
                'choices' => Helper::rt_grid_options(),
                'description' => esc_html__( 'Total Columns 12', 'petslist' ),
            )
        );
    }

    /* = Footer 2 Settiings
    =======================================================================*/
    protected function __footer_2_settings_controls($wp_customize) {

        $wp_customize->add_setting('f2_widgets_area', array(
            'default' => '',
            'sanitize_callback' => 'esc_html',
        ));
        $wp_customize->add_control(new Heading($wp_customize, 'f2_widgets_area', array(
            'label' => esc_html__( 'Footer 2 Settings', 'petslist' ),
            'section' => 'footer_2',
        )));

        // Background image
        $wp_customize->add_setting( 'f2_bg_img',
            array(
                'default' => $this->defaults['f2_bg_img'],
                'transport' => 'refresh',
                'sanitize_callback' => 'absint',
            )
        );
        $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'f2_bg_img',
            array(
                'label' => __( 'Background Image', 'petslist' ),
                'description' => esc_html__( 'This is the description for the Media Control', 'petslist' ),
                'section' => 'footer_2',
                'mime_type' => 'image',
                'button_labels' => array(
                    'select' => __( 'Select File', 'petslist' ),
                    'change' => __( 'Change File', 'petslist' ),
                    'default' => __( 'Default', 'petslist' ),
                    'remove' => __( 'Remove', 'petslist' ),
                    'placeholder' => __( 'No file selected', 'petslist' ),
                    'frame_title' => __( 'Select File', 'petslist' ),
                    'frame_button' => __( 'Choose File', 'petslist' ),
                ),
            )
        ) );
        
        // Background color
        $wp_customize->add_setting( 'f2_bg_color',
            array(
                'default' => $this->defaults['f2_bg_color'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_text_sanitization',
            )
        );
        $wp_customize->add_control( 'f2_bg_color',
            array(
                'label' => esc_html__( 'Background Color', 'petslist' ),
                'section' => 'footer_2',
                'type' => 'color',
            )
        );
        // Background color opacity
        $wp_customize->add_setting( 'f2_bg_opacity',
            array(
                'default' => $this->defaults['f2_bg_opacity'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_sanitize_integer',
            )
        );
        $wp_customize->add_control( 'f2_bg_opacity',
            array(
                'label' => esc_html__( 'Background Opacity', 'petslist' ),
                'section' => 'footer_2',
                'type' => 'number',
            )
        );
        // Copyright Background color
        $wp_customize->add_setting( 'f2_cr_bg_color',
            array(
                'default' => $this->defaults['f2_cr_bg_color'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_text_sanitization',
            )
        );
        $wp_customize->add_control( 'f2_cr_bg_color',
            array(
                'label' => esc_html__( 'Copyright Background Color', 'petslist' ),
                'section' => 'footer_2',
                'type' => 'color',
            )
        );
        // Widget Area
        $wp_customize->add_setting( 'f2_widgets_area',
            array(
                'default' => $this->defaults['f2_widgets_area'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_radio_sanitization',
            )
        );
        $wp_customize->add_control( 'f2_widgets_area',
            array(
                'label'   => esc_html__( 'Widget Area', 'petslist' ),
                'section' => 'footer_2',
                'type'    => 'select',
                'choices' => Helper::rt_number_options(),
            )
        );
        $wp_customize->add_setting( 'f2_area1_column',
            array(
                'default' => $this->defaults['f2_area1_column'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_radio_sanitization',
            )
        );
        $wp_customize->add_control( 'f2_area1_column',
            array(
                'label' => esc_html__( 'Area 1 Columns', 'petslist' ),
                'section' => 'footer_2',
                'type' => 'select',
                'choices' => Helper::rt_grid_options(),
                'description' => esc_html__( 'Total Columns 12', 'petslist' ),
            )
        );
        $wp_customize->add_setting( 'f2_area2_column',
            array(
                'default' => $this->defaults['f2_area2_column'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_radio_sanitization',
            )
        );
        $wp_customize->add_control( 'f2_area2_column',
            array(
                'label' => esc_html__( 'Area 2 Columns', 'petslist' ),
                'section' => 'footer_2',
                'type' => 'select',
                'choices' => Helper::rt_grid_options(),
                'description' => esc_html__( 'Total Columns 12', 'petslist' ),
            )
        );
        $wp_customize->add_setting( 'f2_area3_column',
            array(
                'default' => $this->defaults['f2_area3_column'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_radio_sanitization',
            )
        );
        $wp_customize->add_control( 'f2_area3_column',
            array(
                'label' => esc_html__( 'Area 3 Columns', 'petslist' ),
                'section' => 'footer_2',
                'type' => 'select',
                'choices' => Helper::rt_grid_options(),
                'description' => esc_html__( 'Total Columns 12', 'petslist' ),
            )
        );
        $wp_customize->add_setting( 'f2_area4_column',
            array(
                'default' => $this->defaults['f2_area4_column'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_radio_sanitization',
            )
        );
        $wp_customize->add_control( 'f2_area4_column',
            array(
                'label' => esc_html__( 'Area 4 Columns', 'petslist' ),
                'section' => 'footer_2',
                'type' => 'select',
                'choices' => Helper::rt_grid_options(),
                'description' => esc_html__( 'Total Columns 12', 'petslist' ),
            )
        );
    }

    /* = Footer 3 Settiings
    =======================================================================*/
    protected function __footer_3_settings_controls($wp_customize) {

        $wp_customize->add_setting('f3_widgets_area', array(
            'default' => '',
            'sanitize_callback' => 'esc_html',
        ));
        $wp_customize->add_control(new Heading($wp_customize, 'f3_widgets_area', array(
            'label' => esc_html__( 'Footer 3 Settings', 'petslist' ),
            'section' => 'footer_3',
        )));
        // Copyright area logo
        $wp_customize->add_setting( 'f3_cr_logo',
            array(
                'default' => $this->defaults['f3_cr_logo'],
                'transport' => 'refresh',
                'sanitize_callback' => 'absint',
            )
        );
        $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'f3_cr_logo',
            array(
                'label' => __( 'Copyright Logo', 'petslist' ),
                'description' => esc_html__( 'This is the description for the Media Control', 'petslist' ),
                'section' => 'footer_3',
                'mime_type' => 'image',
                'button_labels' => array(
                    'select' => __( 'Select File', 'petslist' ),
                    'change' => __( 'Change File', 'petslist' ),
                    'default' => __( 'Default', 'petslist' ),
                    'remove' => __( 'Remove', 'petslist' ),
                    'placeholder' => __( 'No file selected', 'petslist' ),
                    'frame_title' => __( 'Select File', 'petslist' ),
                    'frame_button' => __( 'Choose File', 'petslist' ),
                ),
            )
        ) );
        
        // Background image
        $wp_customize->add_setting( 'f3_bg_img',
            array(
                'default' => $this->defaults['f3_bg_img'],
                'transport' => 'refresh',
                'sanitize_callback' => 'absint',
            )
        );
        $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'f3_bg_img',
            array(
                'label' => __( 'Background Image', 'petslist' ),
                'description' => esc_html__( 'This is the description for the Media Control', 'petslist' ),
                'section' => 'footer_3',
                'mime_type' => 'image',
                'button_labels' => array(
                    'select' => __( 'Select File', 'petslist' ),
                    'change' => __( 'Change File', 'petslist' ),
                    'default' => __( 'Default', 'petslist' ),
                    'remove' => __( 'Remove', 'petslist' ),
                    'placeholder' => __( 'No file selected', 'petslist' ),
                    'frame_title' => __( 'Select File', 'petslist' ),
                    'frame_button' => __( 'Choose File', 'petslist' ),
                ),
            )
        ) );
        // Background color
        $wp_customize->add_setting( 'f3_bg_color',
            array(
                'default' => $this->defaults['f3_bg_color'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_text_sanitization',
            )
        );
        $wp_customize->add_control( 'f3_bg_color',
            array(
                'label' => esc_html__( 'Background Color', 'petslist' ),
                'section' => 'footer_3',
                'type' => 'color',
            )
        );
        // Background color opacity
        $wp_customize->add_setting( 'f3_bg_opacity',
            array(
                'default' => $this->defaults['f3_bg_opacity'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_sanitize_integer',
            )
        );
        $wp_customize->add_control( 'f3_bg_opacity',
            array(
                'label' => esc_html__( 'Background Opacity', 'petslist' ),
                'section' => 'footer_3',
                'type' => 'number',
            )
        );
        // Copyright Background color
        $wp_customize->add_setting( 'f3_cr_bg_color',
            array(
                'default' => $this->defaults['f3_cr_bg_color'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_text_sanitization',
            )
        );
        $wp_customize->add_control( 'f3_cr_bg_color',
            array(
                'label' => esc_html__( 'Copyright Background Color', 'petslist' ),
                'section' => 'footer_3',
                'type' => 'color',
            )
        );
        // Widget Area
        $wp_customize->add_setting( 'f3_widgets_area',
            array(
                'default' => $this->defaults['f3_widgets_area'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_radio_sanitization',
            )
        );
        $wp_customize->add_control( 'f3_widgets_area',
            array(
                'label'   => esc_html__( 'Widget Area', 'petslist' ),
                'section' => 'footer_3',
                'type'    => 'select',
                'choices' => Helper::rt_number_options(),
            )
        );
        $wp_customize->add_setting( 'f3_area1_column',
            array(
                'default' => $this->defaults['f3_area1_column'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_radio_sanitization',
            )
        );
        $wp_customize->add_control( 'f3_area1_column',
            array(
                'label' => esc_html__( 'Area 1 Columns', 'petslist' ),
                'section' => 'footer_3',
                'type' => 'select',
                'choices' => Helper::rt_grid_options(),
                'description' => esc_html__( 'Total Columns 12', 'petslist' ),
            )
        );
        $wp_customize->add_setting( 'f3_area2_column',
            array(
                'default' => $this->defaults['f3_area2_column'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_radio_sanitization',
            )
        );
        $wp_customize->add_control( 'f3_area2_column',
            array(
                'label' => esc_html__( 'Area 2 Columns', 'petslist' ),
                'section' => 'footer_3',
                'type' => 'select',
                'choices' => Helper::rt_grid_options(),
                'description' => esc_html__( 'Total Columns 12', 'petslist' ),
            )
        );
        $wp_customize->add_setting( 'f3_area3_column',
            array(
                'default' => $this->defaults['f3_area3_column'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_radio_sanitization',
            )
        );
        $wp_customize->add_control( 'f3_area3_column',
            array(
                'label' => esc_html__( 'Area 3 Columns', 'petslist' ),
                'section' => 'footer_3',
                'type' => 'select',
                'choices' => Helper::rt_grid_options(),
                'description' => esc_html__( 'Total Columns 12', 'petslist' ),
            )
        );
        $wp_customize->add_setting( 'f3_area4_column',
            array(
                'default' => $this->defaults['f3_area4_column'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_radio_sanitization',
            )
        );
        $wp_customize->add_control( 'f3_area4_column',
            array(
                'label' => esc_html__( 'Area 4 Columns', 'petslist' ),
                'section' => 'footer_3',
                'type' => 'select',
                'choices' => Helper::rt_grid_options(),
                'description' => esc_html__( 'Total Columns 12', 'petslist' ),
            )
        );
    }

}