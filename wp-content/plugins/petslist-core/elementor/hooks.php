<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

// namespace RadiusTheme\Petslist_Core;

use Elementor\Controls_Manager;
use Rtcl\Helpers\Functions;

class Plugins_Hooks {

	protected static $instance = null;

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

    public function __construct() {
		add_filter( 'rtcl_el_category_box_style', [ $this, 'petslist_listing_category_box_style' ], 10 );
        add_filter( 'rtcl_el_listing_category_widget_general_field', [ $this, 'category_settings_new_fields'], 10, 2 );
        add_filter( 'rtcl_el_listing_category_widget_general_field', [ $this, 'category_settings_cols_modity'], 10, 2 );
        add_filter( 'rtcl_el_listing_category_widget_style_field', [ $this, 'category_settings_style_modity'], 10, 2 );
        add_filter( 'rtcl_el_listing_category_widget_style_field', [ $this, 'category_settings_add_new_style'], 10, 2 );
		add_filter( 'rtcl_el_listings_grid_style', [ __CLASS__, 'el_listings_style_grid' ], 10 );
		add_filter( 'rtcl_el_listings_list_style', [ __CLASS__, 'el_listings_style' ], 10 );
    }

	
	/**
	 * Category function
	 *
	 * @param [array] $data array for list style.
	 *
	 * @return array
	 */
	public static function petslist_listing_category_box_style( $data ) {
		$data['style-3'] = esc_html__( 'Style 3', 'petslist-core' );
		return $data;
	}

	public function rt_alignment_options(){
		return array(
			'left'    => array(
				'title' => __( 'Left', 'petslist-core' ),
				'icon' => 'eicon-text-align-left',
			),
			'center' => array(
				'title' => __( 'Center', 'petslist-core' ),
				'icon' => 'eicon-text-align-center',
			),
			'right' => array(
				'title' => __( 'Right', 'petslist-core' ),
				'icon' => 'eicon-text-align-right',
			),
			'justify' => array(
				'title' => __( 'Justified', 'petslist-core' ),
				'icon' => 'eicon-text-align-justify',
			),
		);
	}

	public function category_settings_new_fields( $fields, $obj ){

		if( 'rtcl-listing-cat-box' == $obj->rtcl_base ){

			$cat_label = array(
				array(
					'id'      => 'cat_label',
					'type'    => Controls_Manager::TEXT,
					'label'   => esc_html__( ' Category Label', 'petslist-core-core' ),
					'condition'   => [ 'rtcl_cats_style' => 'style-3'],		
					'description' => esc_html__( 'Keyword field position', 'petslist-core-core' ),
				),
			);
			$fields = $obj->insert_new_controls( 'rtcl_cats', $cat_label, $fields );
		}

		return $fields;
	}

	public function category_settings_cols_modity( $fields, $obj ){
		$modify_array = array(
			array(
				'id'        => 'rtcl_sec_responsive',
				'condition'   => [ 'rtcl_cats_style' => ['style-1', 'style-2']],	
			),			
			array(
				'id'        => 'rtcl_col_xl',
				'condition'   => [ 'rtcl_cats_style' => ['style-1', 'style-2']],	
			),			
		);
		$fields = $obj->modify_controls( $modify_array, $fields );

		return $fields;
	}

	public function category_settings_style_modity( $fields, $obj ){
		$modify_array = array(
			array(
				'id'        => 'rtcl_background_body',
				'condition'   => [ 'rtcl_cats_style' => ['style-2', 'style-3']],	
			),			
		);
		$fields = $obj->modify_controls( $modify_array, $fields );

		return $fields;
	}

	public function category_settings_add_new_style( $fields, $obj ){
		if( 'rtcl-listing-cat-box' == $obj->rtcl_base ){
			$label_color = array(
				array(
					'id'      => 'label_color',
					'mode'    => 'responsive',
					'type'    => Controls_Manager::COLOR,
					'label'   => esc_html__( 'label Color', 'petslist-core' ),
					'selectors' => array( 
						'{{WRAPPER}} .cat-items-label' => 'color: {{VALUE}}'
					),
				),
			);
			$fields = $obj->insert_new_controls( 'rtcl_title_color', $label_color, $fields );
		}
		return $fields;
	}


	/**
	 * Undocumented function
	 *
	 * @param [array] $data array for list style.
	 *
	 * @return array
	 */
	public static function el_listings_style_grid( $data = [] ) {
		$data['style-6'] = [
			'title' => esc_html__( 'Style 6', 'petslist-core' ),
			'url'   => PETSLIST_CORE_BASE_URL ."assets/img/grid-style-06.png",
		];
		$data['style-7'] = [
			'title' => esc_html__( 'Style 7', 'petslist-core' ),
			'url'   => PETSLIST_CORE_BASE_URL ."assets/img/grid-style-06.png",
		];
		return $data;
	}

	/**
	 * Undocumented function
	 *
	 * @param [array] $data array for list style.
	 *
	 * @return array
	 */
	public static function el_listings_style( $data = [] ) {
		$data['style-6'] = [
			'title' => esc_html__( 'Style 6', 'petslist-core' ),
			'url'   =>  PETSLIST_CORE_BASE_URL ."assets/img/list-style-06.png",
		];
		return $data;
	}

}

Plugins_Hooks::instance();