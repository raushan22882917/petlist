<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.1
 */

namespace RadiusTheme\Petslist\Customizer;

class Default_Data {

	// Customizer Default Data

	public static function default_values() {
		$customizer_defaults = [

			// General
			'logo'                           => '',
			'logo_dark'                      => '',
			'mobile_logo'                    => '',
			'logo_width'                     => '200px',
			'banner_image'                   => '',
			'preloader'                 	 => '',
			'preloader_gif'                  => '',
			'back_to_top'                    => '',
			'remove_admin_bar'               => '',

			// Header
			'top_bar'                        => 0,
			'sticky_header'                  => 0,
			'header_btn'                     => '',
			'header_btn_txt'                 => '',
			'header_btn_url'                 => '#',
			'breadcrumb'                     => '',
			'header_login_icon'              => '',
			'header_login_text'              => 'Account',
			'header_chat_icon'               => '',
			'header_chat_text'               => '',
			'header_style'                   => '1',
			'header_width'                   => 'box-width',
			'menu_alignment'                 => 'menu-right',
			'tr_header'                      => 0,
			'header_transparent_color'       => 'rgba(255, 255, 255, .5)',
			// Mobile
			'header_link_btn_mobile'         => 1,
			'header_login_btn_mobile'        => 1,
			'header_chat_btn_mobile'         => '',

			// Blog Archive
			'blog_style'                     => 'style1',
			'blog_date'                      => 1,
			'blog_author_name'               => 1,
			'blog_cat_visibility'            => 1,
			'blog_comment_num'               => 1,
			'excerpt_length'                 => 40,
			'blog_button'                    => 1,

			// Single Post
			'post_date'                      => 1,
			'post_author_name'               => 1,
			'post_comment_num'               => 1,
			'post_cats'                      => 1,
			'post_details_related_section'   => 0,
			'post_tag'                       => 1,
			'post_social_icon'               => 0,

			// Error
			'error_image'               	 => '',
			'error_text'                     => 'Error Page',
			'error_subtitle'                 => 'Sorry! This Page is Not Available!',
			'error_buttontext'               => 'Go To Home Page',

			// Footer
			'footer_style'                   => '1',
			'copyright_area'                 => 1,
			'copyright_text'                 => date( 'Y' ) . '© All right reserved by RadiusTheme',
			//Footer 1
            'f1_bg_img'     => '',
            'f1_bg_color'   => '',
            'f1_bg_opacity' => '',
            'f1_cr_bg_color' => '',
            'f1_widgets_area' => '4',
            'f1_area1_column' => '3',
            'f1_area2_column' => '2',
            'f1_area3_column' => '2',
            'f1_area4_column' => '4',

            //Footer 2
            'f2_bg_img'     => '',
            'f2_bg_color'   => '',
            'f2_bg_opacity' => '',
			'f2_cr_bg_color' => '',
            'f2_widgets_area' => '4',
            'f2_area1_column' => '3',
            'f2_area2_column' => '2',
            'f2_area3_column' => '2',
            'f2_area4_column' => '4',

            //Footer 3
            'f3_cr_logo'    => '',
            'f3_bg_img'     => '',
            'f3_bg_color'   => '',
            'f3_bg_opacity' => '',
			'f3_cr_bg_color'  => '',
            'f3_widgets_area' => '4',
            'f3_area1_column' => '2',
            'f3_area2_column' => '3',
            'f3_area3_column' => '3',
            'f3_area4_column' => '4',

			// Listings Settings
			/* Archive */
			'listing_archive_style'       => '1',
			'listing_excerpt'         	  => '18',
			'listing_archive_title'       => 0,
			'listing_archive_title_text'  => '',
			'listing_archive_filter_type' => 'default',
			'listing_archive_search_filter_style'            => 'standard',
			'listing_archive_search_filter_by_keyword'       => 1,
			'listing_archive_search_filter_by_type'    	     => 1,
			'listing_archive_search_filter_by_category'    	 => 1,
			'listing_archive_search_filter_by_location'    	 => 1,
			'listing_archive_search_filter_by_radius_search' => 1,
			'listing_archive_search_filter_by_price'    	 => 1,
			'listing_archive_search_filter_by_custom_field'  => 1,
			/* Single */
			'listing_single_style'        => '1',
			'custom_group_individual'     => '185',
			'listing_related'             => 1,
			/* Search */
			'header_search_type'           => 0,
			'header_search_location'       => 1,
			'header_search_category'       => 0,
			'header_search_radius'         => 0,
			'header_search_keyword'        => 1,
			'header_search_style'          => 'standard',
			// Listing Sidebar
			'single_sidebar_listing_info'  => 'listing_owner_info',

			// Blog Layout
			'blog_layout'                    => 'right-sidebar',
			'blog_top_bar'                   => 'default',
			'blog_header_style'              => 'default',
			'blog_menu_alignment'            => 'default',
			'blog_header_width'              => 'default',
			'blog_tr_header'                 => 'default',
			'blog_breadcrumb'                => 1,
			'blog_padding_top'               => '',
			'blog_padding_bottom'            => '90px',
			'blog_footer_style'              => 'default',

			// Single Post Layout
			'single_post_layout'             => 'right-sidebar',
			'single_post_top_bar'            => 'default',
			'single_post_header_style'       => 'default',
			'single_post_menu_alignment'     => 'default',
			'single_post_header_width'       => 'default',
			'single_post_tr_header'          => 'default',
			'single_post_breadcrumb'         => 'default',
			'single_post_padding_top'        => '',
			'single_post_padding_bottom'     => '',
			'single_post_footer_style'       => 'default',

			// Page Layout
			'page_layout'                    => 'full-width',
			'page_top_bar'                   => 'default',
			'page_header_style'              => 'default',
			'page_menu_alignment'            => 'default',
			'page_header_width'              => 'default',
			'page_tr_header'                 => 'default',
			'page_breadcrumb'                => 'default',
			'page_footer_style'              => 'default',
			'page_padding_top'               => '',
			'page_padding_bottom'            => '',

			// Error Layout
			'error_padding_top'              => '',
			'error_padding_bottom'           => '',
			'error_breadcrumb'               => 'default',
			'error_top_bar'                  => 'default',
			'error_header_style'             => 'default',
			'error_header_width'             => 'default',
			'error_menu_alignment'           => 'default',
			'error_tr_header'                => 'default',
			'error_footer_style'             => 'default',

			// Listing Archive Layout
			'listing_archive_layout'         => 'left-sidebar',
			'listing_archive_columns'        => '3',
			'listing_archive_breadcrumb'     => 'default',
			'listing_archive_top_bar'        => 'default',
			'listing_archive_header_style'   => 'default',
			'listing_archive_header_width'   => 'default',
			'listing_archive_menu_alignment' => 'default',
			'listing_archive_tr_header'      => 'default',
			'listing_archive_footer_style'   => 'default',
			'listing_archive_padding_top'    => '',
			'listing_archive_padding_bottom' => '',

			// Listing Single Layout
			'listing_single_header_style'    => 'default',
			'listing_single_header_width'    => 'default',
			'listing_single_menu_alignment'  => 'default',
			'listing_single_tr_header'       => 'default',
			'listing_single_breadcrumb'      => 'default',
			'listing_single_top_bar'         => 'default',
			'listing_single_footer_style'    => 'default',
			'listing_single_padding_top'     => '',
			'listing_single_padding_bottom'  => '',

			// Listing Main Banner Search
			'listing_price_search_type'   => 'input',
			'listing_widget_min_price'    => '0',
			'listing_widget_max_price'    => '20000',
			'custom_fields_search_items'   => '',

			// Contact Info
			'facebook'                       => '',
			'twitter'                        => '',
			'instagram'                      => '',
			'youtube'                        => '',
			'pinterest'                      => '',
			'linkedin'                       => '',
			'skype'                          => '',

			/* = Body Typo Area
            =======================================================*/
            'typo_body' => json_encode(
                array(
                    'font' => 'Plus Jakarta Sans',
                    'regularweight' => '500',
                )
            ),
            'typo_body_size' => '16px',
            'typo_body_height'=> '1.8',

            /* = Menu Typo Area
            =======================================================*/
            //Menu Typography
            'typo_menu' => json_encode(
                array(
                    'font' => 'Baloo Bhaijaan 2',
                    'regularweight' => '500',
                )
            ),
            'typo_menu_size' => '18px',
            'typo_menu_height'=> '1.6',

            //Sub Menu Typography
            'typo_submenu_size' => '16px',
            'typo_submenu_height'=> '1.6',

            /* = Heading Typo Area
            =======================================================*/
            //Heading Typography
            'typo_heading' => json_encode(
                array(
                    'font' => 'Baloo Bhaijaan 2',
                    'regularweight' => '600',
                )
            ),

            //H1
            'typo_h1' => json_encode(
                array(
                    'font' => '',
                    'regularweight' => '600',
                )
            ),
            'typo_h1_size' => '42px',
            'typo_h1_height' => '1.3',

            //H2
            'typo_h2' => json_encode(
                array(
                    'font' => '',
                    'regularweight' => '600',
                )
            ),
            'typo_h2_size' => '36px',
            'typo_h2_height'=> '1.3',

            //H3
            'typo_h3' => json_encode(
                array(
                    'font' => '',
                    'regularweight' => '600',
                )
            ),
            'typo_h3_size' => '28px',
            'typo_h3_height'=> '1.3',

            //H4
            'typo_h4' => json_encode(
                array(
                    'font' => '',
                    'regularweight' => '600',
                )
            ),
            'typo_h4_size' => '22px',
            'typo_h4_height'=> '1.3',

            //H5
            'typo_h5' => json_encode(
                array(
                    'font' => '',
                    'regularweight' => '600',
                )
            ),
            'typo_h5_size' => '18px',
            'typo_h5_height'=> '1.3',

            //H6
            'typo_h6' => json_encode(
                array(
                    'font' => '',
                    'regularweight' => '600',
                )
            ),
            'typo_h6_size' => '14px',
            'typo_h6_height'=> '1.2',

			// Color
			'primary_color'   				 => '#bd8c42',
			'secondary_color' 				 => '#bd8c42',
			'body_color'      				 => '#515167',
			'heading_color'   				 => '#070C3E',
			'button_color_1'   				 => '#bd8c42',
			'button_color_2'   				 => '#bd8c42',
		];

		return apply_filters( 'rttheme_customizer_defaults', $customizer_defaults );
	}
}