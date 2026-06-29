<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace RadiusTheme\Petslist_Core;

use \ReflectionClass;
use Elementor\Widget_Base;
use Rtcl\Helpers\Functions;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

abstract class Custom_Widget_Base extends Widget_Base {
	public $rt_name;
	public $rt_base;
	public $rt_category;
	public $rt_icon;
	public $rt_translate;
	public $rt_dir;

	public function __construct( $data = [], $args = null ) {
		$this->rt_category = PETSLIST_CORE_THEME_PREFIX . '-widgets'; // Category /@dev
		$this->rt_icon     = 'rdtheme-el-custom';
		$this->rt_dir      = dirname( ( new ReflectionClass( $this ) )->getFileName() );
		parent::__construct( $data, $args );
	}

	public function get_name() {
		return $this->rt_base;
	}

	public function get_title() {
		return $this->rt_name;
	}

	public function get_icon() {
		return $this->rt_icon;
	}

	public function get_categories() {
		return array( $this->rt_category );
	}

	public function rt_template( $template, $data ) {
		$template_name = '/elementor-custom/' . basename( $this->rt_dir ) . '/' . $template . '.php';
		if ( file_exists( STYLESHEETPATH . $template_name ) ) {
			$file = STYLESHEETPATH . $template_name;
		}
		elseif ( file_exists( TEMPLATEPATH . $template_name ) ) {
			$file = TEMPLATEPATH . $template_name;
		}
		else {
			$file = $this->rt_dir . '/' . $template . '.php';
		}

		ob_start();
		include $file;
		echo ob_get_clean();
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

	public function rt_grid_options(){
		return [
			'1'  => esc_html__( '1 Columns', 'petslist-core' ),
			'2'  => esc_html__( '2 Columns', 'petslist-core' ),
			'3'  => esc_html__( '3 Columns', 'petslist-core' ),
			'4'  => esc_html__( '4 Columns', 'petslist-core' ),
			'5'  => esc_html__( '5 Columns', 'petslist-core' ),
			'6'  => esc_html__( '6 Columns', 'petslist-core' ),
		];
	}

	public function rt_number_options(){
		return [
			'1'  => esc_html__( '1', 'petslist-core' ),
			'2'  => esc_html__( '2', 'petslist-core' ),
			'3'  => esc_html__( '3', 'petslist-core' ),
			'4'  => esc_html__( '4', 'petslist-core' ),				
			'5'  => esc_html__( '5', 'petslist-core' ),
			'6'  => esc_html__( '6', 'petslist-core' ),
			'7'  => esc_html__( '7', 'petslist-core' ),
			'8'  => esc_html__( '8', 'petslist-core' ),
		];
	}

	public function rt_autoplay_speed(){
		return [
			'500'  => esc_html__( '500', 'petslist-core' ),
			'1000' => esc_html__( '1000', 'petslist-core' ),
			'1500' => esc_html__( '1500', 'petslist-core' ),
			'2000' => esc_html__( '2000', 'petslist-core' ),
			'2500' => esc_html__( '2500', 'petslist-core' ),
			'3000' => esc_html__( '3000', 'petslist-core' ),
		];
	}

	public function rt_anim_delay(){
		return [
			'200' => esc_html__( '200', 'petslist-core' ),
			'300' => esc_html__( '300', 'petslist-core' ),
			'400' => esc_html__( '400', 'petslist-core' ),
			'500' => esc_html__( '500', 'petslist-core' ),
			'600' => esc_html__( '600', 'petslist-core' ),
			'700' => esc_html__( '700', 'petslist-core' ),
			'800' => esc_html__( '800', 'petslist-core' ),
			'900' => esc_html__( '900', 'petslist-core' ),
		];
	}

	public function rt_post_orderby(){
		return [
			'ID'  => esc_html__( 'Post Id', 'petslist-core' ),
			'author' => esc_html__( 'Post Author', 'petslist-core' ),
			'title' => esc_html__( 'Title', 'petslist-core' ),
			'date' => esc_html__( 'Date', 'petslist-core' ),
			'modified' => esc_html__( 'Modified', 'petslist-core' ),
			'parent' => esc_html__( 'Parent', 'petslist-core' ),
			'rand' => esc_html__( 'Random', 'petslist-core' ),
			'comment_count' => esc_html__( 'Comment Count', 'petslist-core' ),
			'menu_order' => esc_html__( 'Menu Order', 'petslist-core' ),
		];
	}
	public function rt_blog_categories() {
		$categories = get_categories( array(
			'orderby' => 'name',
			'parent'  => 0
		) );
		if(!empty( $categories )){
			$category_links = array();
			foreach ($categories as $key => $value) {
				$category_links[$value->term_id] = $value->name;  
			}
			return $category_links;
		}
	}

	// post tags lists
	public function rt_blog_tags() {
		$tags     = get_tags( [ 'hide_empty' => false ] );
		$tag_list = [];
		foreach ( $tags as $tag ) {
			$tag_list[ $tag->slug ] = $tag->name;
		}

		return $tag_list;
	}
	public function rt_blog_posts_title() {
		$args = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'taxonomy'       => 'category'
		);
		$post_title = array();
		$grid_query = new \WP_Query( $args );
		if ( $grid_query->have_posts() ) : 
			while ( $grid_query->have_posts() ) : $grid_query->the_post();
			$post_title[get_the_ID()] = get_the_title();
			endwhile; wp_reset_postdata();
		endif;
		return $post_title;
	}
	//Get all thumbnail size
	public function rt_get_all_image_sizes() {
		global $_wp_additional_image_sizes;
		$image_sizes = [ '0' => __( 'Default Image Size', 'petslist-core' ) ];
		foreach ( $_wp_additional_image_sizes as $index => $item ) {
			$image_sizes[ $index ] = __( ucwords( $index . ' - ' . $item['width'] . 'x' . $item['height'] ), 'petslist-core' );
		}
		$image_sizes['full'] = __( "Full Size", 'petslist-core' );

		return $image_sizes;
	}
	public function rt_posts_title($post) {
		$prefix = PETSLIST_CORE_THEME_PREFIX;
		$args = array(
			'post_type'    => $post,
			'post_status'  => 'publish',
			'posts_per_page' => -1,
		);
		$post_title = array();
		$grid_query = new \WP_Query( $args );
		if ( $grid_query->have_posts() ) : 
			while ( $grid_query->have_posts() ) : $grid_query->the_post();
			$post_title[get_the_ID()] = get_the_title();
			endwhile; wp_reset_postdata();
		endif;
		return $post_title;
	}

	//Get Custom post category:
	protected function rt_get_categories_by_id( $cat ) {
		$terms   = get_terms( [
			'taxonomy'   => $cat,
			'hide_empty' => true,
		] );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->term_id ] = $term->name;
			}
			return $options;
		}
	}

	//Get Custom post category:
	protected function rt_get_listing_types() {

		$listing_types = Functions::get_listing_types();
        $listing_types = empty( $listing_types ) ? [] : $listing_types;

		if ( ! empty( $listing_types ) ) {
			foreach ( $listing_types as $key => $listing_type ) {
				$options[ $key ] = $listing_type;
			}
			return $options;
		}
	}

	//Store Agent List
	function rt_get_agency_list() {
		$get_agency = get_posts( [
			'post_type'   => 'store',
			'numberposts' => - 1,
			'post_status' => 'publish',
		] );
		$lists      = [];
		foreach ( $get_agency as $post ) {
			$lists[ $post->ID ] = $post->post_title;
		}

		return $lists;
	}

}