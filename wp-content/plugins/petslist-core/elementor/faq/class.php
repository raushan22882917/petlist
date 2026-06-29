<?php
/**
 * This file can be overridden by copying it to yourtheme/elementor-custom/faq/class.php
 * 
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace RadiusTheme\Petslist_Core;

if (!class_exists( 'RtclPro' )) return;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) exit;

class Rt_Faq extends Custom_Widget_Base {

	public function __construct( $data = [], $args = null ){
		$this->rt_name = __( 'Faq', 'petslist-core' );
		$this->rt_base = 'rt-faq';
		parent::__construct( $data, $args );
	}

	protected function register_controls() {
		/* -- Settings Options -- */
		$this->__faq_items_options_settings_controls();

		/* -- Styles Options -- */
		$this->__faq_item_title_style_controls();
		$this->__faq_item_content_style_controls();
		$this->__faq_item_icon_style_controls();
	}

	/* -- Options -- */
	protected function __faq_items_options_settings_controls() {
		$this->start_controls_section(
			'section_faq',
			[
				'label' => esc_html__( 'Faq List', 'petslist-core' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'faq_items',
			[
				'label' => esc_html__( 'Faq List', 'petslist-core' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => [
					[
						'name' => 'faq_icon',
						'label' => esc_html__( 'Icon', 'petslist-core' ),
						'type' => Controls_Manager::ICONS,
						'default' => [
							'value' => 'far fa-check-circle',
							'library' => 'fa-solid',
						],
					],
					[
						'name' => 'faq_title',
						'label' => esc_html__( 'Title', 'petslist-core' ),
						'type' => Controls_Manager::TEXT,
						'default' => esc_html__( 'Put here faq title' , 'petslist-core' ),
						'label_block' => true,
					],
					[
						'name' => 'faq_content',
						'label' => esc_html__( 'Content', 'petslist-core' ),
						'type' => Controls_Manager::WYSIWYG,
						'default' => esc_html__( 'Item content. Click the edit button to change this text.' , 'petslist-core' ),
						'show_label' => false,
					],
				],
				'default' => [
					[
						'faq_title' => esc_html__( 'Faq Title 1', 'petslist-core' ),
						'faq_content' => esc_html__( 'Item content. Click the edit button to change this text.', 'petslist-core' ),
					],
					[
						'faq_title' => esc_html__( 'Faq Title 2', 'petslist-core' ),
						'faq_content' => esc_html__( 'Item content. Click the edit button to change this text.', 'petslist-core' ),
					],
				],
				'title_field' => '{{{ faq_title }}}',
			]
		);

		$this->add_control(
			'icon_display',
			[
				'label' => esc_html__( 'Icon', 'petslist-core' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'petslist-core' ),
				'label_off' => esc_html__( 'Hide', 'petslist-core' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => esc_html__( 'Enable or disable icon. Default: Off', 'petslist-core' ),
			]
		);

		$this->add_control(
			'icon',
			[
				'label' => esc_html__( 'Icon', 'petslist-core' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-angle-down',
					'library' => 'fa-solid',
				],
				'recommended' => [
					'fa-solid' => [
						'fa-angle-down',
					],
				],
				'condition'   => [ 'icon_display!' => ''],
			]
		);
		$this->add_control(
			'icon_open',
			[
				'label' => esc_html__( 'Icon Opened', 'petslist-core' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-angle-up',
					'library' => 'fa-solid',
				],
				'recommended' => [
					'fa-solid' => [
						'fa-angle-up',
					],
				],
				'condition'   => [ 'icon_display!' => ''],
			]
		);
		$this->add_control(
			'icon_position',
			[
				'label' => esc_html__( 'Icon Position', 'petslist-core' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'petslist-core' ),
						'icon' => 'eicon-text-align-left',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'petslist-core' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'right',
				'toggle' => true,
				'condition'   => [ 'icon_display!' => ''],
			]
		);
		$this->add_control(
			'css_class',
			[
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Css class', 'petslist-core' ),
				'description' => esc_html__( 'Extra class for css, if need', 'petslist-core' ),
			]
		);
		$this->end_controls_section();
	}

	/* -- Item Title Style -- */
	protected function __faq_item_title_style_controls() {
		$this->start_controls_section(
			'faq_title_style',
			[
				'label' => __( 'Title Style', 'petslist-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Title Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .accordion-button' => 'color: {{VALUE}}',
				),
			]
		);
		$this->add_control(
			'title-active_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Title Active Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .accordion-button:not(.collapsed)' => 'color: {{VALUE}}',
				),
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typo',
				'label'    => esc_html__( 'Title Typography', 'petslist-core' ),
				'selector' => '{{WRAPPER}} .accordion-button',
			]
		);
		$this->add_responsive_control(
			'title_padding',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Padding', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .accordion-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                    
				),
			]
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'title_border',
				'label'     => __( 'Border', 'petslist-core' ),
				'selector'  => '{{WRAPPER}} .accordion-button',
			]
		);

		$this->end_controls_section();
	}

	/* -- Item Content Style -- */
	protected function __faq_item_content_style_controls() {
		$this->start_controls_section(
			'faq_body_style',
			[
				'label' => __( 'Text Style', 'petslist-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'body_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .panel-body' => 'color: {{VALUE}}',
				),
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'body_typo',
				'label'    => esc_html__( 'Typography', 'petslist-core' ),
				'selector' => '{{WRAPPER}} .panel-body',
			]
		);
		$this->add_responsive_control(
			'body_padding',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Padding', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .panel-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                    
				),
			]
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'body_border',
				'label'     => __( 'Border', 'petslist-core' ),
				'selector'  => '{{WRAPPER}} .panel-body',
			]
		);
		$this->end_controls_section();
	}

	/* -- Item Icon Style -- */
	protected function __faq_item_icon_style_controls() {
		$this->start_controls_section(
			'faq_icon_style',
			[
				'label' => __( 'Icon Style', 'petslist-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control(
			'icon_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Icon Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .rtin-accordion-icon .rtin-icon' => 'color: {{VALUE}}',
				),
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'icon_typo',
				'label'    => esc_html__( 'Typography', 'petslist-core' ),
				'selector' => '{{WRAPPER}} .rtin-accordion-icon .rtin-icon',
			]
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'icon_bg',
				'label'    => esc_html__( 'Background', 'petslist-core' ),
				'types' => [ 'classic', 'gradient', 'video' ],
				'fields_options' => [
					'background' => [
						'label' => esc_html__('Icon Background Color', 'petslist-core'),
						'default' => 'classic',
					]
				],
				'selector' => '{{WRAPPER}} .rtin-accordion-icon .rtin-icon',
			]
		);
		$this->add_responsive_control(
			'faq_icon_bg_size',
			[
				'label' => esc_html__( 'Width', 'petslist-core' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
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
				'selectors' => [
					'{{WRAPPER}} .rtin-accordion-icon .rtin-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'faq_icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'petslist-core' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 500,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rtin-accordion-icon .rtin-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .rtin-accordion-icon .rtin-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'icon_margin',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Margin', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .rtin-accordion-icon .rtin-icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                    
				),
			]
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'icon_border',
				'label'     => __( 'Border', 'petslist-core' ),
				'selector'  => '{{WRAPPER}} .rtin-accordion-icon .rtin-icon',
			]
		);
		$this->add_responsive_control(
			'icon_border_radius',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Border Radius', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .rtin-accordion-icon .rtin-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                    
				),
			]
		);
		$this->add_control(
			'open_faq_icon_heading',
			[
				'type' => Controls_Manager::HEADING,
				'label'   => esc_html__( 'Open Item Style', 'petslist-core' ),
				'separator' => 'before',
			]
		);
		$this->add_control(
			'open_faq_icon_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Icon Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .rtin-accordion-icon .rtin-icon.rt-icon-opened' => 'color: {{VALUE}}',
				),
			]
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'open_faq_icon_bg',
				'label'    => esc_html__( 'Background', 'petslist-core' ),
				'types' => [ 'classic', 'gradient', 'video' ],
				'fields_options' => [
					'background' => [
						'label' => esc_html__('Icon Background Color', 'petslist-core'),
						'default' => 'classic',
					]
				],
				'selector' => '{{WRAPPER}} .rtin-accordion-icon .rtin-icon.rt-icon-opened',
			]
		);
		$this->end_controls_section();
	}


	protected function render() {
		$data = $this->get_settings();

		$template = 'view';

		return $this->rt_template( $template, $data );
	}
}