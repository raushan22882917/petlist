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

class Rt_Team_Member extends Custom_Widget_Base {

	public function __construct( $data = [], $args = null ) {
		$this->rt_name = esc_html__( 'Team Member', 'petslist-core' );
		$this->rt_base = 'rt-team-member';
		parent::__construct( $data, $args );

	}
    public function get_script_depends() {
		return [
			'swiper',
		];
	}
	protected function register_controls() {

		/* -- Settings Options -- */
		$this->__team_member_fileds_settings();
		$this->__social_lists_settings();
		$this->__full_box_style_settings();
		$this->__picture_style_settings();
		$this->__name_style_settings();
		$this->__designation_style_settings();
    }
	// Fields
    protected function __team_member_fileds_settings(){
		$this->start_controls_section(
			'sec_fields',
			[
				'label' => esc_html__( 'General', 'petslist-core' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'picture',
			[
				'type'    => Controls_Manager::MEDIA,
				'label'   => esc_html__( 'Picture', 'petslist-core' ),
			]
		);
		$this->add_control(
			'name',
			[
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Name', 'petslist-core' ),
				'default' => 'Marvin McKinney',
				'label_block' => true,
			]
		);
		$this->add_control(
			'designation',
			[
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Designation', 'petslist-core' ),
				'default' => 'Accountance',
				'label_block' => true,
			]
		);
		$this->add_control(
			'link',
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
		$this->end_controls_section();
	}
	//Social List
    protected function __social_lists_settings(){
		$this->start_controls_section(
			'social_list',
			[
				'label' => esc_html__( 'Social List', 'petslist-core' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);
		$repeater = new \Elementor\Repeater();
		$repeater->add_control(
			'social_icon', [
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
			'social_link', [
				'label' => __( 'List', 'petslist-core' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
			]
		);
        
		$this->add_control(
			'social_lists',
			[
				'label'     => __( 'Lists', 'petslist-core' ),
				'type'      => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => array(
					array( 
						'social_icon' => 'fa-brands fa-facebook-f',
						'social_link' => '#',
					),
					array( 
						'social_icon' => 'fa-brands fa-twitter',
						'social_link' => '#',
					),
				),
			]
		);
        $this->end_controls_section();
	}
	//Full Box Style
	protected function __full_box_style_settings(){
		$this->start_controls_section(
			'full_box_style',
			[
				'label' => esc_html__( 'Full Box', 'petslist-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
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
				'selector' => '{{WRAPPER}} .team-card',
			]
		);
		$this->add_responsive_control(
			'full_box_padding',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Padding', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .team-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                    
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
					'{{WRAPPER}} .team-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                    
				),
			]
		);
		$this->end_controls_section();
	}

	//Picture Style
	protected function __picture_style_settings() {
		$this->start_controls_section(
			'picture_style',
			[
				'label' => esc_html__( 'Picture', 'petslist-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'picture_bg_color',
				'label'    => esc_html__( 'Background', 'petslist-core' ),
				'types' => [ 'classic', 'gradient', 'video' ],
				'fields_options' => [
					'background' => [
						'label' => esc_html__(' Background', 'petslist-core'),
						'default' => 'classic',
					]
				],
				'selector' => '{{WRAPPER}} .team-card .team-img-wrapper',
			]
		);
		
		$this->add_responsive_control(
			'picture_padding',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Padding', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .team-card .team-img-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                    
				),
			]
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'picture_border',
				'label'     => __( 'Border', 'petslist-core' ),
				'selector'  => '{{WRAPPER}} .team-card .team-img-wrapper',
			]
		);
		$this->add_responsive_control(
			'picture_border_radius',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Radius', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .team-card .team-img-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                   
				),
			]
		);
		$this->end_controls_section();
	}

	//Name Style
	protected function __name_style_settings() {
		$this->start_controls_section(
			'name_style',
			[
				'label' => esc_html__( 'Name', 'petslist-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control(
			'name_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .team-card .team-content .title' => 'color: {{VALUE}}',
					'{{WRAPPER}} .team-card .team-content .title a' => 'color: {{VALUE}}',
				),
			]
		);
		$this->add_control(
			'name_hover_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Hover Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .team-card .team-content .title a:hover' => 'color: {{VALUE}}',
				),
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'name_typo',
				'label'    => esc_html__( 'Typography', 'petslist-core' ),
				'selector' => '{{WRAPPER}} .team-card .team-content .title, {{WRAPPER}} .team-card .team-content .title a',
			]
		);
		$this->add_responsive_control(
			'name_margin',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Margin', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .team-card .team-content .title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                    
				),
			]
		);
		$this->end_controls_section();
	}

	//Designation Style
	protected function __designation_style_settings() {
		$this->start_controls_section(
			'designation_style',
			[
				'label' => esc_html__( 'Designation', 'petslist-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control(
			'designation_color',
			[
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Color', 'petslist-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .team-card .team-content .designation' => 'color: {{VALUE}}',
				),
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'designation_typo',
				'label'    => esc_html__( 'Typography', 'petslist-core' ),
				'selector' => '{{WRAPPER}} .team-card .team-content .designation',
			]
		);
		$this->add_responsive_control(
			'designation_margin',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Margin', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .team-card .team-content .designation' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',                    
				),
			]
		);
		$this->end_controls_section();
	}

	protected function render() {
		$data = $this->get_settings();
		
		$template = 'view';

		$this->rt_template( $template, $data );
	}

}