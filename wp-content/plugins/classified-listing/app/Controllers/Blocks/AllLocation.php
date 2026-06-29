<?php

/**
 * Main Gutenberg AllLocation Class.
 *
 * The main class that initiates and runs the plugin.
 *
 * @package  Classifid-listing
 *
 * @since    1.0.0
 */

namespace Rtcl\Controllers\Blocks;

use Rtcl\Helpers\Functions;

class AllLocation {
	protected $name = 'rtcl/all-location';

	protected $attributes = [];

	public function get_attributes( $default = false ) {
		$attributes = [
			'blockId'                => [
				'type'    => 'string',
				'default' => '',
			],
			'preview'                => [
				'type'    => 'boolean',
				'default' => false,
			],
			"col_style"              => [
				"type"    => "object",
				"default" => [
					"style"      => "grid",
					"style_list" => "1",
					"style_grid" => "1",
				],
			],
			'colBGColor'             => [
				'type'    => 'string',
				'default' => '',
				'style'   => [ (object) [ 'selector' => '{{RTCL}} .gb-all-locations .location-boxes { background-color:{{colBGColor}}; }' ] ],
			],
			'colBorderColor'         => [
				'type'    => 'string',
				'default' => '',
				'style'   => [ (object) [ 'selector' => '{{RTCL}} .gb-all-locations .location-boxes { border-color:{{colBorderColor}}; }' ] ],
			],
			'colBorderWith'          => [
				'type'    => 'string',
				'default' => '',
				'style'   => [ (object) [ 'selector' => '{{RTCL}} .gb-all-locations .location-boxes {border-width:{{colBorderWith}}; }' ] ],
			],
			'colBorderStyle'         => [
				'type'    => 'string',
				'default' => 'solid',
				'style'   => [ (object) [ 'selector' => '{{RTCL}} .gb-all-locations .location-boxes { border-style:{{colBorderStyle}}; }' ] ],
			],
			'colBorderRadius'        => [
				'type'    => 'string',
				'default' => '',
				'style'   => [ (object) [ 'selector' => '{{RTCL}} .gb-all-locations .location-boxes { border-radius:{{colBorderRadius}}; }' ] ],
			],
			"col_padding"            => [
				"type"    => "object",
				"default" => [
					"unit" => "px",
				],
				'style'   => [
					(object) [
						'selector' => '{{RTCL}} .grid-style-1 .location-boxes,
				{{RTCL}} .list-style-1 .location-boxes {padding:{{col_padding}} !important;}',
					],
				],
			],
			"gutter_padding"         => [
				"type"    => "object",
				"default" => [
					"unit" => "px",
				],
				'style'   => [ (object) [ 'selector' => '{{RTCL}} .location-boxes-wrapper {padding:{{gutter_padding}};}' ] ],
			],
			'headerBGColor'          => [
				'type'    => 'string',
				'default' => '',
				'style'   => [ (object) [ 'selector' => '{{RTCL}} .gb-all-locations.grid-style-2 .location-boxes .location-boxes-header { background-color:{{headerBGColor}}; }' ] ],
			],
			'headerBorderColor'      => [
				'type'    => 'string',
				'default' => '',
				'style'   => [ (object) [ 'selector' => '{{RTCL}} .gb-all-locations.grid-style-2 .location-boxes .location-boxes-header { border-color:{{headerBorderColor}}; }' ] ],
			],
			"header_padding"         => [
				"type"    => "object",
				"default" => [
					"unit" => "px",
				],
				'style'   => [ (object) [ 'selector' => '{{RTCL}} .location-boxes-header {padding:{{header_padding}} !important;}' ] ],
			],
			'childIconColor'         => [
				'type'    => 'string',
				'default' => '',
				'style'   => [
					(object) [
						'selector' => '{{RTCL}} .gb-all-locations .rtcl-gb-sub-location li i {
						color:{{childIconColor}};
					}',
					],
				],
			],
			'childColor'             => [
				'type'  => 'string',
				'style' => [
					(object) [
						'selector' => '{{RTCL}} .gb-all-locations .rtcl-gb-sub-location li a {
						color:{{childColor}} !important;
					}',
					],
				],
			],
			'childHoverColor'        => [
				'type'    => 'string',
				'default' => '',
				'style'   => [
					(object) [
						'selector' => '{{RTCL}} .gb-all-locations .rtcl-gb-sub-location li a:hover {
						color:{{childHoverColor}};
					}',
					],
				],
			],
			'childTypo'              => [
				'type'    => 'object',
				'default' => (object) [
					'openTypography' => 1,
					'size'           => (object) [ 'lg' => '16', 'unit' => 'px' ],
					'spacing'        => (object) [ 'lg' => '0', 'unit' => 'px' ],
					'height'         => (object) [ 'lg' => '26', 'unit' => 'px' ],
					'transform'      => 'none',
					'weight'         => '400',
				],
				'style'   => [ (object) [ 'selector' => '{{RTCL}} .gb-all-locations .rtcl-gb-sub-location li' ] ],
			],
			"child_location_padding" => [
				"type"    => "object",
				"default" => [
					"unit" => "px",
				],
				'style'   => [ (object) [ 'selector' => '{{RTCL}} .location-boxes-body {padding:{{child_location_padding}} !important;}' ] ],
			],
			"titleColor"             => [
				"type"    => "string",
				"default" => "",
				'style'   => [ (object) [ 'selector' => '{{RTCL}} .gb-all-locations .location-boxes .rtcl-title a{color:{{titleColor}} !important;}' ] ],
			],
			"titleHoverColor"        => [
				"type"    => "string",
				"default" => "",
				'style'   => [
					(object) [ 'selector' => '{{RTCL}} .gb-all-locations .location-boxes .rtcl-title a:hover {color:{{titleHoverColor}} !important;}' ],
				],

			],
			'titleTypo'              => [
				'type'    => 'object',
				'default' => (object) [
					'openTypography' => 1,
					'size'           => (object) [ 'lg' => '18', 'unit' => 'px' ],
					'spacing'        => (object) [ 'lg' => '0', 'unit' => 'px' ],
					'height'         => (object) [ 'lg' => '26', 'unit' => 'px' ],
					'transform'      => 'capitalize',
					'weight'         => '700',
				],
				'style'   => [
					(object) [
						'selector' => '{{RTCL}} .gb-all-locations .location-boxes .rtcl-title',
					],
				],
			],
			"counterColor"           => [
				"type"    => "string",
				"default" => "",
				'style'   => [ (object) [ 'selector' => '{{RTCL}} .gb-all-locations .location-boxes .rtcl-counter{color:{{counterColor}}; }' ] ],
			],
			'counterTypo'            => [
				'type'    => 'object',
				'default' => (object) [
					'openTypography' => 1,
					'size'           => (object) [ 'lg' => '15', 'unit' => 'px' ],
					'spacing'        => (object) [ 'lg' => '0', 'unit' => 'px' ],
					'height'         => (object) [ 'lg' => '26', 'unit' => 'px' ],
					'transform'      => 'capitalize',
					'weight'         => '400',
				],
				'style'   => [ (object) [ 'selector' => '{{RTCL}} .gb-all-locations .location-boxes .rtcl-counter' ], ],
			],
			"contentColor"           => [
				"type"    => "string",
				"default" => "",
				'style'   => [ (object) [ 'selector' => '{{RTCL}} .gb-all-locations .location-boxes .rtcl-description{color:{{contentColor}}; }' ] ],
			],
			'contentTypo'            => [
				'type'    => 'object',
				'default' => (object) [
					'openTypography' => 1,
					'size'           => (object) [ 'lg' => '16', 'unit' => 'px' ],
					'spacing'        => (object) [ 'lg' => '0', 'unit' => 'px' ],
					'height'         => (object) [ 'lg' => '26', 'unit' => 'px' ],
					'transform'      => 'none',
					'weight'         => '400',
				],
				'style'   => [
					(object) [
						'selector' => '{{RTCL}} .gb-all-locations .location-boxes .rtcl-description',
					],
				],
			],
			"container_padding"      => [
				"type"    => "object",
				"default" => [
					"unit" => "px",
				],
				'style'   => [
					(object) [
						'selector' => '{{RTCL}}.rtcl-block-editor,
				{{RTCL}}.rtcl-block-frontend {padding:{{container_padding}};}',
					],
				],
			],
			"container_margin"       => [
				"type"    => "object",
				"default" => [
					"unit" => "px",
				],
				'style'   => [
					(object) [
						'selector' => '{{RTCL}}.rtcl-block-editor,
				{{RTCL}}.rtcl-block-frontend {margin:{{container_margin}};}',
					],
				],
			],
			"containerBGColor"       => [
				"type"    => "string",
				"default" => "",
				'style'   => [
					(object) [
						'selector' => '{{RTCL}}.rtcl-block-editor,
				{{RTCL}}.rtcl-block-frontend {background-color:{{containerBGColor}};}',
					],
				],
			],
			"locations"              => [
				"type" => "array",
			],
			"location_type"          => [
				"type"    => "string",
				"default" => "all",
			],
			"show_count"             => [
				"type"    => "boolean",
				"default" => true,
			],
			"count_position"         => [
				"type"    => "string",
				"default" => "inline",
			],
			"count_after_text"       => [
				"type" => "string",
			],
			"orderby"                => [
				"type"    => "string",
				"default" => "name",
			],
			"sortby"                 => [
				"type"    => "string",
				"default" => "asc",
			],
			"hide_empty"             => [
				"type"    => "boolean",
				"default" => false,
			],
			"show_image"             => [
				"type"    => "boolean",
				"default" => false,
			],
			"image_size"             => [
				"type"    => "string",
				"default" => "rtcl-thumbnail",
			],
			"custom_image_width"     => [
				"type"    => "number",
				"default" => 400,
			],
			"custom_image_height"    => [
				"type"    => "number",
				"default" => 280,
			],
			"show_desc"              => [
				"type"    => "boolean",
				"default" => true,
			],
			"show_sub_location"      => [
				"type"    => "boolean",
				"default" => true,
			],
			"enable_link"            => [
				"type"    => "boolean",
				"default" => true,
			],
			"enable_nofollow"        => [
				"type"    => "boolean",
				"default" => false,
			],
			"enable_parent"          => [
				"type"    => "boolean",
				"default" => false,
			],
			"sub_location_limit"     => [
				"type"    => "number",
				"default" => 4,
			],
			"location_limit"         => [
				"type"    => "number",
				"default" => 4,
			],
			"desc_limit"             => [
				"type"    => "number",
				"default" => 20,
			],
			"col_xl"                 => [
				"type"    => "string",
				"default" => "3",
			],
			"col_lg"                 => [
				"type"    => "string",
				"default" => "3",
			],
			"col_md"                 => [
				"type"    => "string",
				"default" => "4",
			],
			"col_sm"                 => [
				"type"    => "string",
				"default" => "6",
			],
			"col_mobile"             => [
				"type"    => "string",
				"default" => "12",
			],
		];

		if ( $default ) {
			$temp = [];
			foreach ( $attributes as $key => $value ) {
				if ( isset( $value['default'] ) ) {
					$temp[ $key ] = $value['default'];
				}
			}

			return $temp;
		} else {
			return $attributes;
		}
	}

	public function __construct() {
		add_action( 'init', [ $this, 'all_location_content' ] );
	}

	public function all_location_content() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}
		register_block_type(
			RTCL_PATH . 'block-metadata/all-location',
			[
				'render_callback' => [ $this, 'render_callback_listings' ],
				'attributes'      => $this->get_attributes(),
			],
		);
	}

	public function render_callback_listings( $attributes ) {
		wp_enqueue_style( 'fontawesome' );
		$settings = $attributes;
		$view     = ! empty( $settings['col_style']['style'] ) && in_array( $settings['col_style']['style'], [ 'grid', 'list' ] )
			? $settings['col_style']['style'] : 'grid';
		$style    = '1';
		if ( 'grid' == $view ) {
			$style = ! empty( $settings['col_style']['style_grid'] ) && in_array( $settings['col_style']['style_grid'], [ '1', '2' ] )
				? $settings['col_style']['style_grid'] : '1';
		}

		$data = [
			'template'              => 'block/all-location/' . $view . '/style-' . $style,
			'view'                  => $view,
			'style'                 => $style,
			'settings'              => $settings,
			'terms'                 => AdminAjaxController::rtcl_gb_all_location_query( $settings ),
			'default_template_path' => null,
		];

		$data = apply_filters( 'rtcl_gb_all_location_box_data', $data );
		ob_start();
		Functions::get_template( $data['template'], $data, '', $data['default_template_path'] );

		return ob_get_clean();
	}
}
