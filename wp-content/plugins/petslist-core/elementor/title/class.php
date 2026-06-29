<?php
/**
 * This file can be overridden by copying it to yourtheme/elementor-custom/title/class.php
 * 
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace RadiusTheme\Petslist_Core;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) exit;

class Rt_Title extends Custom_Widget_Base {

	public function __construct( $data = [], $args = null ){
		$this->rt_name = __( 'Section Title', 'petslist-core' );
		$this->rt_base = 'rt-title';
		parent::__construct( $data, $args );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'sec_general',
			[
				'label' => esc_html__( 'General', 'petslist-core' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'title',
			[
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Title', 'petslist-core' ),
				'default' => 'Section Title',
				'label_block' => true,
			]
		);
		$this->add_control(
			'subtitle',
			[
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Sub Title', 'petslist-core' ),
				'default' => 'Section Sub Title',
				'label_block' => true,
			]
		);
		$this->add_responsive_control(
			'heading_tag',
			[
				'type'    => Controls_Manager::SELECT,
				'label'   => esc_html__( 'HTML Tag', 'petslist-core' ),
				'options' => [
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
				],
				'default' => 'h2',
			]
		);
		$this->add_control(
			'desc',
			[
				'type'    => Controls_Manager::WYSIWYG,
				'label'   => esc_html__( 'Description', 'petslist-core' ),
				'label_block' => true,
			]
		);
		$this->add_control(
			'btntext',
			[
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Text', 'petslist-core' ),
				'label_block' => true,
			]
		);
		$this->add_control(
			'url',
			[
				'type'  => Controls_Manager::URL,
				'label' => esc_html__( 'Link', 'petslist-core' ),
				'placeholder' => 'https://your-link.com',
				'label_block' => true,
			]
		);
		$this->add_responsive_control(
			'align',
			[
				'type'    => Controls_Manager::CHOOSE,
				'label'   => esc_html__( 'Title Alignment', 'petslist-core' ),
				'options' => $this->rt_alignment_options(),
				'prefix_class' => 'elementor-align-',
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .section-heading' => 'text-align: {{VALUE}};',
				],
			]
		);
		$this->end_controls_section();

		// Title Style
		$this->start_controls_section(
			'title_settings',
			[
				'label'     => esc_html__( 'Title', 'petslist-core' ),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control(
			'title_heading',
			[
				'type' => Controls_Manager::HEADING,
				'label'   => esc_html__( 'Title Style', 'petslist-core' ),
				'separator' => 'before',
			]
		);
		$this->add_control(
			'title_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .section-heading .heading-title' => 'color: {{VALUE}}'
				),
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typo',
				'label'    => esc_html__( 'Typography', 'petslist-core' ),
				'selector' => '{{WRAPPER}} .section-heading .heading-title',
			]
		);
		$this->add_responsive_control(
			'title_margin',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Margin', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .section-heading .heading-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                    
				),
			]
		);
		$this->add_control(
			'title_line',
			[
				'type' => Controls_Manager::HEADING,
				'label'   => esc_html__( 'Title Line', 'petslist-core' ),
				'separator' => 'before',
			]
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'title_line_bg',
				'label'     => __( 'Background', 'petslist-core' ),
				'selector'  => '{{WRAPPER}} .section-heading.tf .heading-title:after',
			]
		);
		$this->add_control(
			'title_line_switcher',
			[
				'type'        => Controls_Manager::SWITCHER,
				'label'       => esc_html__( 'Show Title Line', 'petslist-core' ),
				'label_on'    => esc_html__( 'On', 'petslist-core' ),
				'label_off'   => esc_html__( 'Off', 'petslist-core' ),
				'default'     => '',
			]
		);
		$this->end_controls_section();


		// Sub Title Style
		$this->start_controls_section(
			'title_sub_settings',
			[
				'label'     => esc_html__( 'Sub Title', 'petslist-core' ),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control(
			'subtitle_heading',
			[
				'type' => Controls_Manager::HEADING,
				'label'   => esc_html__( 'Sub Title Style', 'petslist-core' ),
				'separator' => 'before',
			]
		);
		$this->add_control(
			'subtitle_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .section-heading .heading-subtitle' => 'color: {{VALUE}}'
				),
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'subtitle_typo',
				'label'    => esc_html__( 'Typography', 'petslist-core' ),
				'selector' => '{{WRAPPER}} .section-heading .heading-subtitle',
			]
		);
		$this->add_responsive_control(
			'subtitle_margin',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Margin', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .section-heading .heading-subtitle' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                    
				),
			]
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'subtitle_border',
				'label'     => __( 'Border', 'petslist-core' ),
				'selector'  => '{{WRAPPER}} .section-heading .heading-subtitle',
			]
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'subtitle_bg',
				'label'     => __( 'Background', 'petslist-core' ),
				'selector'  => '{{WRAPPER}} .section-heading .heading-subtitle',
			]
		);
		$this->end_controls_section();


		// Description Style
		$this->start_controls_section(
			'desc_settings',
			[
				'label'     => esc_html__( 'Description', 'petslist-core' ),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control(
			'desc_heading',
			[
				'type' => Controls_Manager::HEADING,
				'label'   => esc_html__( 'Description Style', 'petslist-core' ),
				'separator' => 'before',
			]
		);
		$this->add_control(
			'desc_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .section-heading p' => 'color: {{VALUE}}'
				),
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'desc_typo',
				'label'    => esc_html__( 'Typography', 'petslist-core' ),
				'selector' => '{{WRAPPER}} .section-heading p',
			]
		);
		$this->add_responsive_control(
			'desc_margin',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Margin', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .section-heading p' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                    
				),
			]
		);
		$this->end_controls_section();


		// Button Style
		$this->start_controls_section(
			'btn_style',
			[
				'label'     => esc_html__( 'Button', 'petslist-core' ),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control(
			'btn_heading',
			[
				'type' => Controls_Manager::HEADING,
				'label'   => esc_html__( 'Button Style', 'petslist-core' ),
				'separator' => 'before',
			]
		);

		$this->start_controls_tabs( 'btn_tab_style' );
		// Normal tab.
		$this->start_controls_tab(
			'btn_style_normal',
			[
				'label' => __( 'Normal', 'petslist-core' ),
			]
		);

		$this->add_control(
			'btn_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .item-btn' => 'color: {{VALUE}}',
				),
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'btn_typo',
				'label'    => esc_html__( 'Typography', 'petslist-core' ),
				'selector' => '{{WRAPPER}} .item-btn',
			]
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'btn_bg_color',
				'label'     => __( 'Background', 'petslist-core' ),
				'selector'  => '{{WRAPPER}} .item-btn',
			]
		);
		$this->add_responsive_control(
			'btn_padding',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Padding', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .item-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                    
				),
			]
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'btn_border',
				'label'     => __( 'Border', 'petslist-core' ),
				'selector'  => '{{WRAPPER}} .item-btn',
			]
		);
		$this->end_controls_tab();

		// Hover tab.
		$this->start_controls_tab(
			'btn_style_hover',
			[
				'label' => __( 'Hover', 'petslist-core' ),
			]
		);
		$this->add_control(
			'btn_h_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .item-btn:hover' => 'color: {{VALUE}}',
				),
			]
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'btn_hbg_color',
				'label'     => __( 'Background', 'petslist-core' ),
				'selector'  => '{{WRAPPER}} .item-btn:hover',
			]
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'btn_hover_border',
				'label'     => __( 'Border', 'petslist-core' ),
				'selector'  => '{{WRAPPER}} .item-btn:hover',
			]
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();

	}

	protected function render() {
		$data = $this->get_settings();

		$template = 'view';

		return $this->rt_template( $template, $data );
	}
}