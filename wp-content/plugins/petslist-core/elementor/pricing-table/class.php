<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace RadiusTheme\Petslist_Core;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rt_Pricing_Table extends Custom_Widget_Base {

	public function __construct( $data = [], $args = null ) {
		$this->rt_name = esc_html__( 'Pricing Table', 'petslist-core' );
		$this->rt_base = 'rt-pricing-table';
		parent::__construct( $data, $args );

	}
    public function get_script_depends() {
		return [
			'swiper',
		];
	}
	protected function register_controls() {

		/* -- Settings Options -- */
		$this->__pricing_fileds_settings();
		$this->__pricing_feature_lists_settings();
		$this->__pricing_table_style_settings();
		$this->__pricing_button_style_settings();
    }
	// Fields
    protected function __pricing_fileds_settings(){
		$this->start_controls_section(
			'sec_fields',
			[
				'label' => esc_html__( 'General', 'petslist-core' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'plan_type',
			[
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Plan Type', 'petslist-core' ),
				'default' => 'Basic',
				'label_block' => true,
			]
		);
		$this->add_control(
			'plan_price',
			[
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Plan Price', 'petslist-core' ),
				'default' => '$12',
				'label_block' => true,
			]
		);
		$this->add_control(
			'plan_duration',
			[
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Plan Duration', 'petslist-core' ),
				'default' => '/Month',
				'label_block' => true,
			]
		);
		$this->add_control(
			'plan_description',
			[
				'type'    => Controls_Manager::TEXTAREA,
				'label'   => esc_html__( 'Plan Description', 'petslist-core' ),
				'label_block' => true,
			]
		);
		$this->add_control(
			'plan_btn_text',
			[
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Plan Button Text', 'petslist-core' ),
				'default' => 'Purchase Now',
				'label_block' => true,
			]
		);

		$this->add_control(
			'plan_btn_link',
			[
				'label' => esc_html__( 'Link', 'petslist-core' ),
				'type' => Controls_Manager::URL,
				'options' => [ 'url', 'is_external', 'nofollow' ],
				'default' => [
					'url' => '',
					'is_external' => true,
					'nofollow' => true,
				],
				'label_block' => true,
			]
		);
		$this->add_control(
			'item_shape',
			[
				'type'    => Controls_Manager::MEDIA,
				'label'   => esc_html__( 'Shape image', 'petslist-core' ),
			]
		);
		$this->end_controls_section();
	}
	//Features List
    protected function __pricing_feature_lists_settings(){
		$this->start_controls_section(
			'sec_feature_list',
			[
				'label' => esc_html__( 'Feature List', 'petslist-core' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);
		$repeater = new \Elementor\Repeater();
		$repeater->add_control(
			'icon', [
				'label' => __( 'Icon', 'petslist-core' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-check',
					'library' => 'fa-solid',
				],
				'label_block' => true,
			]
		);
		$repeater->add_control(
			'feature_name', [
				'label' => __( 'List', 'petslist-core' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
			]
		);
        
		$this->add_control(
			'price_features',
			[
				'label'     => __( 'Price Features', 'petslist-core' ),
				'type'      => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => array(
					array( 
						'icon' => 'fas fa-check-circle',
						'list' => '3 Regular Ads',
					),
					array( 
						'icon' => 'fas fa-check-circle',
						'list' => 'No Featured Ads'
					),
					array( 
						'icon' => 'fas fa-check-circle',
						'list' => 'No Top Ads'
					),
					array( 
						'icon' => 'fas fa-check-circle',
						'list' => 'No Ads Will Be Bumped Up'
					),
					array( 
						'icon' => 'fas fa-check-circle',
						'list' => 'Limited Support'
					),
				),
			]
		);
        $this->end_controls_section();
	}
	//Style
	protected function __pricing_table_style_settings(){
		$this->start_controls_section(
			'pricing_table_style',
			[
				'label' => esc_html__( 'Style', 'petslist-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		//Full Box
		$this->add_control(
			'full_box',
			[
				'type' => Controls_Manager::HEADING,
				'label'   => esc_html__( 'Full Box', 'petslist-core' ),
				'separator' => 'before',
			]
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'full_box_bg_color',
				'label'    => esc_html__( 'Background', 'petslist-core' ),
				'types' => [ 'classic', 'gradient', 'video' ],
				'fields_options' => [
					'background' => [
						'label' => esc_html__(' Background', 'petslist-core'),
						'default' => 'classic',
					]
				],
				'selector' => '{{WRAPPER}} .rt-pricing-item',
			]
		);
		$this->add_responsive_control(
			'full_box_padding',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Padding', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .rt-pricing-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                    
				),
			]
		);
		$this->add_responsive_control(
			'full_box_border_radius',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Border Radius', 'petslist-core' ),               
				'selectors' => array(
					'{{WRAPPER}} .rt-pricing-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                    
				),
			]
		);
		//Plan Type
		$this->add_control(
			'plan_type_heading',
			[
				'type' => Controls_Manager::HEADING,
				'label'   => esc_html__( 'Plan Type Style', 'petslist-core' ),
				'separator' => 'before',
			]
		);
		$this->add_control(
			'plan_type_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .pricing-title' => 'color: {{VALUE}}',
				),
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'plan_type_typo',
				'label'    => esc_html__( 'Typography', 'petslist-core' ),
				'selector' => '{{WRAPPER}} .pricing-title',
			]
		);
		//Plan Price
		$this->add_control(
			'plan_pricing_heading',
			[
				'type' => Controls_Manager::HEADING,
				'label'   => esc_html__( 'Plan Pricing Style', 'petslist-core' ),
				'separator' => 'before',
			]
		);
		$this->add_control(
			'plan_pricing_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .pricing-price' => 'color: {{VALUE}}',
				),
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'plan_pricing_typo',
				'label'    => esc_html__( 'Typography', 'petslist-core' ),
				'selector' => '{{WRAPPER}} .pricing-price',
			]
		);
		//Plan Duration
		$this->add_control(
			'plan_duration_heading',
			[
				'type' => Controls_Manager::HEADING,
				'label'   => esc_html__( 'Plan Duration', 'petslist-core' ),
				'separator' => 'before',
			]
		);
		$this->add_control(
			'plan_duration_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .pricing-plan' => 'color: {{VALUE}}',
				),
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'plan_duration_typo',
				'label'    => esc_html__( 'Typography', 'petslist-core' ),
				'selector' => '{{WRAPPER}} .pricing-plan',
			]
		);
		//Plan Description
		$this->add_control(
			'plan_description_heading',
			[
				'type' => Controls_Manager::HEADING,
				'label'   => esc_html__( 'Plan Description', 'petslist-core' ),
				'separator' => 'before',
			]
		);
		$this->add_control(
			'plan_description_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .para-text' => 'color: {{VALUE}}',
				),
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'plan_description_typo',
				'label'    => esc_html__( 'Typography', 'petslist-core' ),
				'selector' => '{{WRAPPER}} .para-text',
			]
		);
		//Plan Features
		$this->add_control(
			'plan_features_list_heading',
			[
				'type' => Controls_Manager::HEADING,
				'label'   => esc_html__( 'Plan Features', 'petslist-core' ),
				'separator' => 'before',
			]
		);
		$this->add_control(
			'plan_features_list_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'List Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .rt-pricing-features-list li span' => 'color: {{VALUE}}',
				),
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'plan_features_list_typo',
				'label'    => esc_html__( 'List Typography', 'petslist-core' ),
				'selector' => '{{WRAPPER}} .rt-pricing-features-list li span',
			]
		);
		$this->add_control(
			'plan_features_list_icon_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .rt-pricing-features-list li i' => 'color: {{VALUE}}',
				),
			]
		);
		$this->add_responsive_control(
			'plan_features_list_icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'petslist-core' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rt-pricing-features-list li i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .rt-pricing-features-list li svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->end_controls_section();
	}
	// Button Style
	protected function __pricing_button_style_settings() {
		$this->start_controls_section(
			'pricing_button_style',
			[
				'label' => esc_html__( 'Style', 'petslist-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'pricing_btn_heading',
			[
				'type' => Controls_Manager::HEADING,
				'label'   => esc_html__( 'Button Style', 'petslist-core' ),
				'separator' => 'before',
			]
		);

		$this->start_controls_tabs( 'btn_tab_style' );
		// Normal tab.
		$this->start_controls_tab(
			'pricing_btn_style_normal',
			[
				'label' => __( 'Normal', 'petslist-core' ),
			]
		);

		$this->add_control(
			'pricing_btn_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .rt-pricing-item-btn .pricing-btn' => 'color: {{VALUE}}',
				),
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'pricing_btn_typo',
				'label'    => esc_html__( 'Typography', 'petslist-core' ),
				'selector' => '{{WRAPPER}} .rt-pricing-item-btn .pricing-btn',
			]
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'pricing_btn_bg_color',
				'label'    => esc_html__( 'Background', 'petslist-core' ),
				'types' => [ 'classic', 'gradient', 'video' ],
				'fields_options' => [
					'background' => [
						'label' => esc_html__(' Background', 'petslist-core'),
						'default' => 'classic',
					]
				],
				'selector' => '{{WRAPPER}} .rt-pricing-item-btn .pricing-btn',
			]
		);
		$this->add_responsive_control(
			'pricing_btn_padding',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Padding', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .rt-pricing-item-btn .pricing-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                    
				),
			]
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'pricing_btn_border',
				'label'     => __( 'Border', 'petslist-core' ),
				'selector'  => '{{WRAPPER}} .rt-pricing-item-btn .pricing-btn',
			]
		);
		$this->add_responsive_control(
			'pricing_btn_border_radius',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Radius', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .rt-pricing-item-btn .pricing-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                    
				),
			]
		);
		$this->end_controls_tab();

		// Hover tab.
		$this->start_controls_tab(
			'pricing_btn_style_hover',
			[
				'label' => __( 'Hover', 'petslist-core' ),
			]
		);
		$this->add_control(
			'pricing_btn_h_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .rt-pricing-item-btn .pricing-btn:hover' => 'color: {{VALUE}}',
				),
			]
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'pricing_btn_hbg_color',
				'label'     => __( 'Background', 'petslist-core' ),
				'selector'  => '{{WRAPPER}} .rt-pricing-item-btn .pricing-btn:hover',
			]
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'pricing_btn_hover_border',
				'label'     => __( 'Border', 'petslist-core' ),
				'selector'  => '{{WRAPPER}} .rt-pricing-item-btn .pricing-btn:hover',
			]
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();
	}

	protected function render() {
		$data = $this->get_settings();
		
		$template = 'view';

		$this->rt_template( $template, $data );
	}

}