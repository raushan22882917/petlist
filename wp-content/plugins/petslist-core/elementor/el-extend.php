<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class RT_Extende_Element_Widget {

	public function __construct() {
		
		// add_action( 'elementor/element/section/section_background/before_section_end', [ $this, 'add_elementor_section_background_controls' ] );
		// add_action( 'elementor/frontend/section/before_render', [ $this, 'render_elementor_section_parallax_background' ] );
		add_action( 'elementor/element/counter/section_counter/after_section_start', [ $this, 'petslist_counter_control' ], 10, 2 );
	}

	public function add_elementor_section_background_controls( \Elementor\Element_Section $section ) {
		$section->add_control(
			'rt_section_parallax',
			[
				'label'        => __( 'Parallax', 'petslist-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_off'    => __( 'Off', 'petslist-core' ),
				'label_on'     => __( 'On', 'petslist-core' ),
				'default'      => 'no',
				'prefix_class' => 'rt-parallax-bg-',
			]
		);

		$section->add_control(
			'rt_parallax_speed',
			[
				'label'     => __( 'Speed', 'petslist-core' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 0.1,
				'max'       => 5,
				'step'      => 0.1,
				'default'   => 0.5,
				'condition' => [
					'rt_section_parallax' => 'yes',
				],
			]
		);

		$section->add_control(
			'rt_parallax_transition',
			[
				'label'        => __( 'Parallax Transition off?', 'petslist-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_off'    => __( 'on', 'petslist-core' ),
				'label_on'     => __( 'Off', 'petslist-core' ),
				'default'      => 'off',
				'return_value' => 'off',
				'prefix_class' => 'rt-parallax-transition-',
				'condition'    => [
					'rt_section_parallax' => 'yes',
				],
			]
		);
	}

	public function add_elementor_section_background_overlay_controls( \Elementor\Element_Section $section ) {
		$section->add_control(
			'rt_section_overlay_parallax',
			[
				'label'        => __( 'Parallax', 'petslist-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_off'    => __( 'Off', 'petslist-core' ),
				'label_on'     => __( 'On', 'petslist-core' ),
				'default'      => 'no',
				'prefix_class' => 'rt-parallax-bg-',
			]
		);

		$section->add_control(
			'rt_parallax_overlay_speed',
			[
				'label'     => __( 'Speed', 'petslist-core' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 0.1,
				'max'       => 5,
				'step'      => 0.1,
				'default'   => 0.5,
				'condition' => [
					'rt_section_overlay_parallax' => 'yes',
				],
			]
		);

		$section->add_control(
			'rt_parallax_overlay_transition',
			[
				'label'        => __( 'Parallax Transition off?', 'petslist-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_off'    => __( 'on', 'petslist-core' ),
				'label_on'     => __( 'Off', 'petslist-core' ),
				'default'      => 'off',
				'return_value' => 'off',
				'prefix_class' => 'rt-parallax-transition-',
				'condition'    => [
					'rt_section_overlay_parallax' => 'yes',
				],
			]
		);
	}

	// Render section background parallax
	public function render_elementor_section_parallax_background( \Elementor\Element_Base $element ) {
		if ( 'section' === $element->get_name() ) {
			if ( 'yes' === $element->get_settings_for_display( 'rt_section_parallax' ) ) {
				$rt_background = $element->get_settings_for_display( 'background_image' );
				if ( ! isset( $rt_background ) ) {
					return;
				}
				$rt_background_URL = $rt_background['url'];
				$data_speed        = $element->get_settings_for_display( 'rt_parallax_speed' );

				$element->add_render_attribute( '_wrapper', [
					'data-speed'    => $data_speed,
					'data-bg-image' => $rt_background_URL,
				] );
			}
		}
	}

	public function petslist_counter_control( $counter, $args ) {
		$counter->add_control( 'counter_style',
			[
				'label'        => __( 'Style', 'petslist-core' ),
				'type'         => \Elementor\Controls_Manager::SELECT,
				'default'      => 'default-style',
				'options'      => [
					'default-style' => __( 'Default', 'petslist-core' ),
					'inline-style'  => __( 'Inline', 'petslist-core' ),
				],
				'prefix_class' => 'elementor-counter-',
			]
		);

		$counter->add_responsive_control( 'width',
			[
				'label' => esc_html__( 'Width', 'petslist-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'description' => esc_html__( 'Number part minimun width (optional)', 'petslist-core' ),
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 105,
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-counter .elementor-counter-number-wrapper' => 'min-width: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$counter->add_responsive_control(
			'alignment',
			[
				'label'     => __( 'Alignment', 'petslist-core' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'flex-start;text-align:left;' => [
						'title' => __( 'Left', 'petslist-core' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center;text-align:center;'   => [
						'title' => __( 'Center', 'petslist-core' ),
						'icon'  => 'eicon-text-align-center',
					],
					'flex-end;text-align:right;'  => [
						'title' => __( 'Right', 'petslist-core' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-counter .elementor-counter-number-prefix, {{WRAPPER}} .elementor-counter .elementor-counter-number-suffix' => 'flex-grow: inherit;',
					'{{WRAPPER}} .elementor-counter .elementor-counter-number-wrapper' => 'justify-content: {{VALUE}};',
					'{{WRAPPER}} .elementor-counter .elementor-counter-title' => 'justify-content: {{VALUE}};',
					'{{WRAPPER}} .elementor-counter-inline-style .elementor-counter' => 'justify-content: {{VALUE}};',
				],
			]
		);
		$counter->add_responsive_control(
			'title_margin',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Title Margin', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .elementor-counter .elementor-counter-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                    
				),
			]
		);
		$counter->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'number_typography',
				'label'        => __( 'Number Typography', 'petslist-core' ),
				'selector' => '{{WRAPPER}} .elementor-counter-number',
			]
		);
	}

}

new RT_Extende_Element_Widget();
