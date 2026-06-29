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

class Rt_Listing_Categories extends Custom_Widget_Base {

	public function __construct( $data = [], $args = null ) {
		$this->rt_name = esc_html__( 'Listing Categories', 'petslist-core' );
		$this->rt_base = 'rt-listing-categories';
		parent::__construct( $data, $args );

	}
    public function get_script_depends() {
		return [
			'swiper',
		];
	}
	protected function register_controls() {

		/* -- Settings Options -- */
		$this->__category_general_settings();
		$this->__category_item_settings();
		$this->__category_icon_settings();
		$this->__category_name_settings();
		$this->__category_count_settings();
    }

	/* General Settings
	-------------------------------*/
	protected function __category_general_settings() {
		$this->start_controls_section(
			'sec_general',
			[
				'label' => esc_html__( 'General', 'petslist-core' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'style',
			[
				'type'    => Controls_Manager::SELECT2,
				'label'   => esc_html__( 'Layout', 'letslist-core' ),
				'options' => array(
					'1' => esc_html__( 'Layout 1', 'letslist-core' ),
					'2' => esc_html__( 'Layout 2', 'letslist-core' ),
					'3' => esc_html__( 'Layout 3', 'letslist-core' ),
					'4' => esc_html__( 'Layout 4', 'letslist-core' ),
					'5' => esc_html__( 'Layout 5', 'letslist-core' ),
				),
				'default' => '1',
			]
		);
		$repeater = new \Elementor\Repeater();
		$repeater->add_control(
			'category_name', [
				'type'    => \Elementor\Controls_Manager::SELECT2,
				'label'   => esc_html__( 'Select Category', 'letslist-core' ),
				'options' => $this->rt_get_categories_by_id('rtcl_category'),
				'multiple' => false,
				'label_block' => true,
				'show_label' => false,
			]
		);
		$repeater->add_control(
			'icon_condition',
			[
				'type'    => Controls_Manager::SELECT2,
				'label'   => esc_html__( 'Icon Type', 'letslist-core' ),
				'options' => array(
					'default_icon' => esc_html__( 'Default Icon', 'letslist-core' ),
					'custom_icon' => esc_html__( 'Custom Icon', 'letslist-core' ),
				),
				'default' => 'default_icon',
			]
		);
		$repeater->add_control(
			'icon_type',
			[
				'type'    => Controls_Manager::SELECT2,
				'label'   => esc_html__( 'Default Icon', 'letslist-core' ),
				'options' => array(
					'icon' => esc_html__( 'Category Icon', 'letslist-core' ),
					'image' => esc_html__( 'Category Image', 'letslist-core' ),
				),
				'default' => 'image',
				'condition'  => array( 'icon_condition' => array( 'default_icon' ) ),
			]
		);
		$repeater->add_control(
			'category_icon',
			[
				'type'        => Controls_Manager::ICONS,
				'label'   => esc_html__( 'Select Custom Icon', 'letslist-core' ),
				'default' => array(
					'value' => 'fas fa-smile-wink',
					'library' => 'fa-solid',
				),
				'condition'   => array( 'icon_condition' => array( 'custom_icon' ) ),
			]
		);
		$repeater->add_control(
			'category_bg_shape',
			[
				'label'   => esc_html__( 'Background Shape', 'petslist-core' ),
				'type'    => Controls_Manager::MEDIA,
				'condition'   => array( 'icon_condition' => array( 'custom_icon' ) ),
			]
		);

		
        $this->add_control(
			'categories',
			[
				'label'     => __( 'Add as many categories as you want', 'petslist-core' ),
				'type'      => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
			]
		);
		$this->add_control(
			'display_icon',
			[
				'type'        => Controls_Manager::SWITCHER,
				'label'       => esc_html__( 'Show Icon', 'letslist-core' ),
				'label_on'    => esc_html__( 'On', 'letslist-core' ),
				'label_off'   => esc_html__( 'Off', 'letslist-core' ),
				'default'     => 'yes',
			]
		);
		$this->add_control(
			'display_count',
			[
				'type'        => Controls_Manager::SWITCHER,
				'label'       => esc_html__( 'Show Counts', 'letslist-core' ),
				'label_on'    => esc_html__( 'On', 'letslist-core' ),
				'label_off'   => esc_html__( 'Off', 'letslist-core' ),
				'default'     => 'yes',
			]
		);
		$this->add_responsive_control(
			'item-to-item-gap',
			[
				'label' => esc_html__( 'Item To Item Gap', 'petslist-core' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range' => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
				),
				'default' => [
					'unit' => 'px',
					'size' => 50,
				],
				'selectors' => [
					'{{WRAPPER}} .category-list' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'icon-to-name-gap',
			[
				'label' => esc_html__( 'Icon To Name Gap', 'petslist-core' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range' => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
				),
				'default' => [
					'unit' => 'px',
					'size' => 13,
				],
				'selectors' => [
					'{{WRAPPER}} .category-list .category-item' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'name-to-count-gap',
			[
				'label' => esc_html__( 'Name To Count Gap', 'petslist-core' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range' => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
				),
				'default' => [
					'unit' => 'px',
					'size' => 5,
				],
				'selectors' => [
					'{{WRAPPER}} .category-list .category-item .content' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'alignment',
			[
				'type'    => Controls_Manager::CHOOSE,
				'label'   => esc_html__( 'Alignment', 'petslist-core' ),
				'options' => [
					'start'   => [
						'title' => esc_html__( 'Start', 'petslist-core' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'petslist-core' ),
						'icon'  => 'eicon-text-align-center',
					],
					'end'  => [
						'title' => esc_html__( 'End', 'petslist-core' ),
						'icon'  => 'eicon-text-align-right',
					],
					'between'  => [
						'title' => esc_html__( 'Justified', 'petslist-core' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'default' => 'left',
			]
		);
        $this->end_controls_section();
	}

	/* Item Settings
	-------------------------------*/
	protected function __category_item_settings() {
		/* - Item Settings
		======================================= */
		$this->start_controls_section(
			'cats_items_options',
			[
				'label'     => esc_html__( 'Item Style', 'petslist-core' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'item_bg_color',
				'label'    => esc_html__( 'Background', 'petslist-core' ),
				'types' => [ 'classic', 'gradient', 'video' ],
				'fields_options' => [
					'background' => [
						'label' => esc_html__('Background', 'petslist-core'),
						'default' => 'classic',
					]
				],
				'selector' => '{{WRAPPER}} .category-list .category-item',
			]
		);
		$this->add_responsive_control(
			'item_padding',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Padding', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .category-list .category-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				),
			]
		);
		$this->add_responsive_control(
			'item_margin',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Margin', 'petslist-core' ),             
				'selectors' => array(
					'{{WRAPPER}} .category-list .category-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				),
			]
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'item_border',
				'label'     => __( 'Border', 'petslist-core' ),
				'selector'  => '{{WRAPPER}} .category-list .category-item',
			]
		);
		$this->add_responsive_control(
			'item_border_radius',
			[
				'type'    => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'label'   => esc_html__( 'Border Radius', 'petslist-core' ),                 
				'selectors' => array(
					'{{WRAPPER}} .category-list .category-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				),
			]
		);
		$this->end_controls_section();
	}

	/* Icon Style
	-------------------------------*/
	protected function __category_icon_settings() {
		$this->start_controls_section(
			'category_icon_style',
			[
				'label' => __( 'Icon Style', 'petslist-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_responsive_control(
			'height',
			[
				'label' => esc_html__( 'Icon Box Size', 'petslist-core' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range' => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
				),
				'default' => [
					'unit' => 'px',
					'size' => 50,
				],
				'selectors' => [
					'{{WRAPPER}} .category-list .category-item .icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'petslist-core' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range' => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
				),
				'default' => [
					'unit' => 'px',
					'size' => 35,
				],
				'selectors' => [
					'{{WRAPPER}} .category-list .category-item .icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .category-list .category-item .icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->add_control(
			'icon_color',
			[
				'label'     => __( 'Color', 'petslist-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .category-list .category-item .icon' => 'color: {{VALUE}} !important',
					'{{WRAPPER}} .category-list .category-item .icon i' => 'color: {{VALUE}} !important',
					'{{WRAPPER}} .category-list .category-item .icon svg path' => 'fill: {{VALUE}} !important',
				],
			]
		);
		$this->add_control(
			'icon_h_color',
			[
				'label'     => __( 'Hover Color', 'petslist-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .category-list .category-item:hover .icon' => 'color: {{VALUE}} !important',
					'{{WRAPPER}} .category-list .category-item:hover .icon i' => 'color: {{VALUE}} !important',
					'{{WRAPPER}} .category-list .category-item:hover .icon svg path' => 'fill: {{VALUE}} !important',
				],
			]
		);
		$this->end_controls_section();
	}

	/* Name Style
	-------------------------------*/
	protected function __category_name_settings() {
		$this->start_controls_section(
			'category_name_style',
			[
				'label' => __( 'Name Style', 'petslist-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control(
			'name_color',
			[
				'label'     => __( 'Color', 'letslist-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .category-list .category-item .content .category-name' => 'color: {{VALUE}} !important',
				],
			]
		);
		$this->add_control(
			'name_h_color',
			[
				'label'     => __( 'Hover Color', 'letslist-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .category-list .category-item .content .category-name:hover' => 'color: {{VALUE}} !important',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'name_typo',
				'label'    => esc_html__( 'Typography', 'petslist-core' ),
				'selector' => '{{WRAPPER}} .category-list .category-item .content .category-name',
			]
		);
		$this->end_controls_section();
	}

	/* Count Style
	-------------------------------*/
	protected function __category_count_settings() {
		$this->start_controls_section(
			'category_count_style',
			[
				'label' => __( 'Count Style', 'letslist-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control(
			'count_color',
			[
				'label'     => __( 'Color', 'letslist-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .category-list .category-item .content .item-number' => 'color: {{VALUE}} !important',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'count_typo',
				'label'    => esc_html__( 'Typography', 'petslist-core' ),
				'selector' => '{{WRAPPER}} .category-list .category-item .content .item-number',
			]
		);
		$this->end_controls_section();
	}
	
	/* Count Query
	-------------------------------*/
    protected function rt_term_post_count( $term_id ) {
		$args = [
			'nopaging'            => true,
			'fields'              => 'ids',
			'post_type'           => 'rtcl_listing',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'suppress_filters'    => false,
			'tax_query'           => [
				[
					'taxonomy' => 'rtcl_category',
					'field'    => 'term_id',
					'terms'    => $term_id,
				],
			],
		];

		$posts = get_posts( $args );
		return count( $posts );
	}

	protected function render() {
		$data = $this->get_settings();
		
		$template = 'view';

		$this->rt_template( $template, $data );
	}

}