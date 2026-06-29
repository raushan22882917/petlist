<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace RadiusTheme\Petslist_Core;

use RadiusTheme\Petslist\Helper;
use \RT_Postmeta;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'RT_Postmeta' ) ) {
	return;
}

$Postmeta = RT_Postmeta::getInstance();

$prefix = PETSLIST_CORE_THEME_PREFIX;

/*-------------------------------------
#. Layout Settings
---------------------------------------*/
$nav_menus = wp_get_nav_menus( [ 'fields' => 'id=>name' ] );
$nav_menus = [ 'default' => esc_html__( 'Default', 'petslist-core' ) ] + $nav_menus;
// $sidebars  = [ 'default' => esc_html__( 'Default', 'petslist-core' ) ] + Helper::custom_sidebar_fields();

$Postmeta->add_meta_box( "{$prefix}_page_settings", esc_html__( 'Layout Settings', 'petslist-core' ), [
	'page',
	'post'
], '', '', 'high', [
	'fields' => [
		"{$prefix}_layout_settings" => [
			'label' => esc_html__( 'Layouts', 'petslist-core' ),
			'type'  => 'group',
			'value' => [
				'layout'        => [
					'label'   => esc_html__( 'Layout', 'petslist-core' ),
					'type'    => 'select',
					'options' => [
						'default'       => esc_html__( 'Default', 'petslist-core' ),
						'full-width'    => esc_html__( 'Full Width', 'petslist-core' ),
						'left-sidebar'  => esc_html__( 'Left Sidebar', 'petslist-core' ),
						'right-sidebar' => esc_html__( 'Right Sidebar', 'petslist-core' ),
					],
					'default' => 'default',
				],				
				'header_style'  => [
					'label'   => esc_html__( 'Header Layout', 'petslist-core' ),
					'type'    => 'select',
					'options' => [
						'default' => esc_html__( 'Default', 'petslist' ),
						'1'       => esc_html__( 'Layout 1', 'petslist' ),
						'2'       => esc_html__( 'Layout 2', 'petslist' ),
						'3'       => esc_html__( 'Layout 3', 'petslist' ),
						'4'       => esc_html__( 'Layout 4', 'petslist' ),
						'5'       => esc_html__( 'Layout 5', 'petslist' )
					],
					'default' => 'default',
				],
				'header_width' => [
					'label'   => esc_html__( 'Header Width', 'petslist-core' ),
					'type'    => 'select',
					'options' => [
						'default'    => esc_html__( 'Default', 'petslist-core' ),
						'box-width'  => esc_html__( 'Box width', 'petslist-core' ),
						'fullwidth'  => esc_html__( 'Fullwidth', 'petslist-core' ),
					],
					'default' => 'default',
				],
				'menu_alignment'       => [
					'label'   => esc_html__( 'Menu Alignment', 'petslist-core' ),
					'type'    => 'select',
					'options' => [
						'menu-left'   => esc_html__( 'Left Alignment', 'petslist' ),
						'menu-center' => esc_html__( 'Center Alignment', 'petslist' ),
						'menu-right'  => esc_html__( 'Right Alignment', 'petslist' ),
					],
					'default' => 'menu-center',
				],
				'footer_style'  => [
					'label'   => esc_html__( 'Footer Style', 'petslist-core' ),
					'type'    => 'select',
					'options' => [
						'default' => esc_html__( 'Default', 'petslist' ),
						'1'       => esc_html__( 'Layout 1', 'petslist' ),
						'2'       => esc_html__( 'Layout 2', 'petslist' ),
						'3'       => esc_html__( 'Layout 3', 'petslist' ),
					],
					'default' => 'default',
				],
				'banner'        => [
					'label'   => esc_html__( 'Banner', 'petslist-core' ),
					'type'    => 'select',
					'options' => [
						'default' => esc_html__( 'Default', 'petslist-core' ),
						'on'      => esc_html__( 'Enable', 'petslist-core' ),
						'off'     => esc_html__( 'Disable', 'petslist-core' ),
					],
					'default' => 'default',
				],
				'breadcrumb'    => [
					'label'   => esc_html__( 'Breadcrumb', 'petslist-core' ),
					'type'    => 'select',
					'options' => [
						'default' => esc_html__( 'Default', 'petslist-core' ),
						'on'      => esc_html__( 'Enable', 'petslist-core' ),
						'off'     => esc_html__( 'Disable', 'petslist-core' ),
					],
					'default' => 'default',
				],
				'bgimg'         => [
					'label' => esc_html__( 'Banner Search Background Image', 'petslist-core' ),
					'type'  => 'image',
					'desc'  => esc_html__( 'If not selected, default will be used', 'petslist-core' ),
				],
			]
		]
	]
] );