<?php
/**
 * This file can be overridden by copying it to yourtheme/elementor-custom/title/class.php
 * 
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace RadiusTheme\Petslist_Core;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) exit;

class Rt_Listings extends Custom_Widget_Base {

	public function __construct( $data = [], $args = null ){
		$this->rt_name = __( 'Listings', 'petslist-core' );
		$this->rt_base = 'rt-listings';
		parent::__construct( $data, $args );
	}

	protected function register_controls() {
		/* --General Settings Options -- */
		$this->__options_settings_controls();
		$this->__listing_style_settings_controls();
	}

	protected function __options_settings_controls(){
		
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
				'label'   => esc_html__( 'Style', 'petslist-core' ),
				'options' => array(
					'1' => esc_html__( 'layout 1', 'petslist-core' ),
					'2' => esc_html__( 'layout 2', 'petslist-core' ),
				),
				'default' => '1',
			]
		);
		// Layout
		$this->add_control(
			'number',
			[
				'label'   =>esc_html__( 'Total number of post', 'petslist-core' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 6,
				'description' =>esc_html__( 'Write -1 to show all', 'petslist-core' ),
				'label_block' => true,
			]
		);
		$this->add_control(
			'cols',
			[
				'label'   => esc_html__( 'Grid Columns', 'petslist-core' ),
				'type'    => Controls_Manager::SELECT2,
				'options' => $this->rt_grid_options(),
				'default' => '4',
				'label_block' => true,
			]
		);
		
		$this->add_responsive_control(
			'orderby',
			[
				'label'   => esc_html__( 'Order By', 'traveldo' ),
				'type'    => Controls_Manager::SELECT2,
				'options' => $this->rt_post_orderby(),
				'default' => 'date',
				'label_block' => true,
			]
		);
		$this->add_control(
			'query_type',
			[
				'type'    => Controls_Manager::SELECT2,
				'label'   => esc_html__( 'Get Listings', 'petslist-core' ),
				'options' => array(
					'loccat' => esc_html__( 'By Locations & Categories', 'petslist-core' ),
					'titles' => esc_html__( 'By Titles', 'petslist-core' ),
				),
				'label_block' => true,
			]
		);
		$this->add_control(
			'locations',
			[
				'label'   => esc_html__( 'List By Location', 'petslist-core' ),
				'type'    => Controls_Manager::SELECT2,
				'options' => $this->rt_get_categories_by_id('rtcl_location'),
				'multiple' => true,
				'label_block' => true,
				'conditions' => [
					'terms' => [
						['name' => 'query_type', 'operator' => '==', 'value' => 'loccat'],
					],
				],
			]
		);
		$this->add_control(
			'terms',
			[
				'label' => __( 'List By Category', 'petslist-core' ),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => $this->rt_get_categories_by_id('rtcl_category'),             
                'multiple' => true,
                'label_block' => true,
    			'conditions' => [
					'terms' => [
						['name' => 'query_type', 'operator' => '==', 'value' => 'loccat'],
					],
				],
			]
		);
		$this->add_control(
			'postbytitle',
			[
				'label'   => esc_html__( 'List By Title', 'petslist-core' ),
				'type'    => Controls_Manager::SELECT2,
				'options' => $this->rt_posts_title('rtcl_listing'),
				'multiple' => true,
				'label_block' => true,
				'conditions' => [
					'terms' => [
						['name' => 'query_type', 'operator' => '==', 'value' => 'titles'],
					],
				],
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
	}

	protected function __listing_style_settings_controls(){
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
		$this->end_controls_section();
	}

	protected function render() {
		$data = $this->get_settings();

		$template = 'view-1';

		return $this->rt_template( $template, $data );
	}
}