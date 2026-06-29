<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace RadiusTheme\Petslist\Customizer\Settings;

use RadiusTheme\Petslist\Customizer\Controls\Multiple_Checkbox;
use RadiusTheme\Petslist\Customizer\Controls\Image_Radio;
use RadiusTheme\Petslist\Customizer\Controls\Heading;
use RadiusTheme\Petslist\Customizer\Controls\Switcher;
use RadiusTheme\Petslist\Customizer\Customizer;
use RadiusTheme\Petslist\Helper;
use Rtcl\Helpers\Functions;

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class Listings extends Customizer {

	public function __construct() {
		parent::instance();
		$this->populated_default_data();
		// Register Page Controls
		add_action( 'customize_register', [ $this, 'register_listings_controls' ] );
	}

	public function register_listings_controls( $wp_customize ) {

		/* -- Listing -- */
		$this->__listing_settings_controls($wp_customize);
		/* -- Listing Archive -- */
		$this->__listing_archive_settings_controls($wp_customize);
		/* -- Listing Single -- */
		$this->__listing_single_settings_controls($wp_customize);
		/* -- Listings Search -- */
		$this->__listings_search_settings_controls($wp_customize);
		
	}

	/* Listing Settiings
	==================================================================================================*/
	protected function __listing_settings_controls($wp_customize) {
		// Listing Box Style
		$wp_customize->add_setting( 'listing_archive_style',
			[
				'default'           => $this->defaults['listing_archive_style'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_radio_sanitization',
			]
		);
		$wp_customize->add_control( new Image_Radio( $wp_customize, 'listing_archive_style',
			[
				'label'       => esc_html__( 'Listing Layout', 'petslist' ),
				'description' => esc_html__( 'This is listing archive box listing style', 'petslist' ),
				'section'     => 'listings_section',
				'choices'     => [
					'1' => [
						'image' => trailingslashit( get_template_directory_uri() ) . 'assets/img/theme/listing-1.jpg',
						'name'  => esc_html__( 'Layout 1', 'petslist' ),
					],
					'2' => [
						'image' => trailingslashit( get_template_directory_uri() ) . 'assets/img/theme/listing-2.jpg',
						'name'  => esc_html__( 'Layout 2', 'petslist' ),
					],
				],
			]
		) );

		//Listing Banner Search Items
		$wp_customize->add_setting('listing_header_search', array(
            'default' => '',
            'sanitize_callback' => 'esc_html',
        ));
        $wp_customize->add_control(new Heading($wp_customize, 'listing_header_search', array(
            'label' => esc_html__( 'Header Search', 'petslist' ),
            'section' => 'listings_section',
        )));
		
		// Banner Search Type
		$wp_customize->add_setting(
			'header_search_type',
			[
				'default'           => $this->defaults['header_search_type'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control(
			new Switcher( $wp_customize, 'header_search_type',
				[
					'label'   => esc_html__( 'Search by Type', 'petslist' ),
					'section' => 'listings_section',
				]
			)
		);

		// Banner Search Location
		$wp_customize->add_setting(
			'header_search_location',
			[
				'default'           => $this->defaults['header_search_location'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control(
			new Switcher( $wp_customize, 'header_search_location',
				[
					'label'   => esc_html__( 'Search by Location', 'petslist' ),
					'section' => 'listings_section',
				]
			)
		);

		// Banner Search Radius
		$wp_customize->add_setting(
			'header_search_radius',
			[
				'default'           => $this->defaults['header_search_radius'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control(
			new Switcher( $wp_customize, 'header_search_radius',
				[
					'label'   => esc_html__( 'Search by Radius', 'petslist' ),
					'section' => 'listings_section',
				]
			)
		);

		// Banner Search Category
		$wp_customize->add_setting(
			'header_search_category',
			[
				'default'           => $this->defaults['header_search_category'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control(
			new Switcher( $wp_customize, 'header_search_category',
				[
					'label'   => esc_html__( 'Search by Category', 'petslist' ),
					'section' => 'listings_section',
				]
			)
		);

		// Banner Search Keyword
		$wp_customize->add_setting(
			'header_search_keyword',
			[
				'default'           => $this->defaults['header_search_keyword'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control(
			new Switcher( $wp_customize, 'header_search_keyword',
				[
					'label'   => esc_html__( 'Search by keyword', 'petslist' ),
					'section' => 'listings_section',
				]
			)
		);

		// Search Style
		$wp_customize->add_setting( 'header_search_style', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'rttheme_text_sanitization',
			'default'           => $this->defaults['header_search_style'],
		] );

		$wp_customize->add_control( 'header_search_style', [
			'type'    => 'select',
			'section' => 'listings_section', // Add a default or your own section
			'label'   => esc_html__( 'Search Style', 'petslist' ),
			'choices' => Helper::get_search_form_style(),
		] );
    }

	/* Listing Archive
	==================================================================================================*/
	protected function __listing_archive_settings_controls($wp_customize) {
		// Listing Archive Title
		$wp_customize->add_setting(
			'listing_archive_title',
			[
				'default'           => $this->defaults['listing_archive_title'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control(
			new Switcher( $wp_customize, 'listing_archive_title',
				[
					'label'   => esc_html__( 'Listing Archive Title Visibility', 'petslist' ),
					'section' => 'listing_archive_section',
				]
			)
		);

		// Listing archive title text
        $wp_customize->add_setting( 'listing_archive_title_text',
            array(
                'default' => $this->defaults['listing_archive_title_text'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_text_sanitization',
            )
        );
        $wp_customize->add_control( 'listing_archive_title_text',
            array(
                'label' => esc_html__( 'Listing Archive Title Text', 'petslist' ),
                'section' => 'listing_archive_section',
                'type' => 'text',
				'active_callback' => [ '\RadiusTheme\Petslist\Helper', 'is_listing_archive_title_enabled' ],
            )
        );

		//Listing archive filter
		$wp_customize->add_setting( 'listing_archive_filter_type', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'rttheme_text_sanitization',
			'default'           => $this->defaults['listing_archive_filter_type'],
		] );

		$wp_customize->add_control( 'listing_archive_filter_type', [
			'type'    => 'select',
			'section' => 'listing_archive_section',
			'label'   => esc_html__( 'Filter Type', 'petslist' ),
			'choices' => [
				'default'   => esc_html__( 'Detault Filter', 'petslist' ),
				'custom' => esc_html__( 'Custom Filter', 'petslist' ),
			],
		] );

		//Listing archive filter
		$wp_customize->add_setting( 'listing_archive_search_filter_style', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'rttheme_text_sanitization',
			'default'           => $this->defaults['listing_archive_search_filter_style'],
		] );

		$wp_customize->add_control( 'listing_archive_search_filter_style', [
			'type'    => 'select',
			'section' => 'listing_archive_section',
			'label'   => esc_html__( 'Filter Style', 'petslist' ),
			'choices' => [
				'standard'   => esc_html__('Standard', 'petslist'),
				'popup'      => esc_html__('Popup', 'petslist'),
				'suggestion' => esc_html__('Auto Suggestion', 'petslist'),
				'dependency' => esc_html__('Dependency Selection', 'petslist'),
			],
		] );

		// Search filter by keyword
		$wp_customize->add_setting(
			'listing_archive_search_filter_by_keyword',
			[
				'default'           => $this->defaults['listing_archive_search_filter_by_keyword'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control(
			new Switcher( $wp_customize, 'listing_archive_search_filter_by_keyword',
				[
					'label'   => esc_html__( 'Search Filter by Keyword', 'petslist' ),
					'section' => 'listing_archive_section',
					'active_callback' => [ '\RadiusTheme\Petslist\Helper', 'is_listing_archive_custom_search_filter' ],
				]
			)
		);

		// Search filter by type
		$wp_customize->add_setting(
			'listing_archive_search_filter_by_type',
			[
				'default'           => $this->defaults['listing_archive_search_filter_by_type'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control(
			new Switcher( $wp_customize, 'listing_archive_search_filter_by_type',
				[
					'label'   => esc_html__( 'Search Filter by Type', 'petslist' ),
					'section' => 'listing_archive_section',
					'active_callback' => [ '\RadiusTheme\Petslist\Helper', 'is_listing_archive_custom_search_filter' ],
				]
			)
		);

		// Search filter by category
		$wp_customize->add_setting(
			'listing_archive_search_filter_by_category',
			[
				'default'           => $this->defaults['listing_archive_search_filter_by_category'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control(
			new Switcher( $wp_customize, 'listing_archive_search_filter_by_category',
				[
					'label'   => esc_html__( 'Search Filter by Category', 'petslist' ),
					'section' => 'listing_archive_section',
					'active_callback' => [ '\RadiusTheme\Petslist\Helper', 'is_listing_archive_custom_search_filter' ],
				]
			)
		);

		// Search filter by location
		$wp_customize->add_setting(
			'listing_archive_search_filter_by_location',
			[
				'default'           => $this->defaults['listing_archive_search_filter_by_location'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control(
			new Switcher( $wp_customize, 'listing_archive_search_filter_by_location',
				[
					'label'   => esc_html__( 'Search Filter by Location', 'petslist' ),
					'section' => 'listing_archive_section',
					'active_callback' => [ '\RadiusTheme\Petslist\Helper', 'is_listing_archive_custom_search_filter' ],
				]
			)
		);

		// Search filter by radius search
		$wp_customize->add_setting(
			'listing_archive_search_filter_by_radius_search',
			[
				'default'           => $this->defaults['listing_archive_search_filter_by_radius_search'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control(
			new Switcher( $wp_customize, 'listing_archive_search_filter_by_radius_search',
				[
					'label'   => esc_html__( 'Search Filter by Radius Search', 'petslist' ),
					'section' => 'listing_archive_section',
					'active_callback' => [ '\RadiusTheme\Petslist\Helper', 'is_listing_archive_custom_search_filter' ],
				]
			)
		);

		// Search filter by radius price
		$wp_customize->add_setting(
			'listing_archive_search_filter_by_price',
			[
				'default'           => $this->defaults['listing_archive_search_filter_by_price'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control(
			new Switcher( $wp_customize, 'listing_archive_search_filter_by_price',
				[
					'label'   => esc_html__( 'Search Filter by Price', 'petslist' ),
					'section' => 'listing_archive_section',
					'active_callback' => [ '\RadiusTheme\Petslist\Helper', 'is_listing_archive_custom_search_filter' ],
				]
			)
		);

		// Search filter by custom fields
		$wp_customize->add_setting(
			'listing_archive_search_filter_by_custom_field',
			[
				'default'           => $this->defaults['listing_archive_search_filter_by_custom_field'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control(
			new Switcher( $wp_customize, 'listing_archive_search_filter_by_custom_field',
				[
					'label'   => esc_html__( 'Search Filter by Custom Fields', 'petslist' ),
					'section' => 'listing_archive_section',
					'active_callback' => [ '\RadiusTheme\Petslist\Helper', 'is_listing_archive_custom_search_filter' ],
				]
			)
		);

		// Listing content excerpt
        $wp_customize->add_setting( 'listing_excerpt',
            array(
                'default' => $this->defaults['listing_excerpt'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_sanitize_integer',
            )
        );
        $wp_customize->add_control( 'listing_excerpt',
            array(
                'label' => esc_html__( 'Listing Content Excerpt', 'petslist' ),
                'section' => 'listing_archive_section',
                'type' => 'number',
            )
        );
	}

	/* Listing Single
	==================================================================================================*/
	protected function __listing_single_settings_controls($wp_customize) {
		$group_list = $this->custom_field_group_list();

		// Listing single layout     
        $wp_customize->add_setting( 'listing_single_style',
            array(
                'default' => $this->defaults['listing_single_style'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_radio_sanitization'
            )
        );
        $wp_customize->add_control( 'listing_single_style',
            array(
                'label' => esc_html__( 'Listing Single Layout', 'petslist' ),
                'section' => 'listing_single_section',
                'description' => esc_html__( 'Listing single layout variation for gallery slider or gallery grid', 'petslist' ),
                'type' => 'select',
                'choices' => array(
                    '1' => esc_html__( 'Layout 1', 'petslist' ),
                    '2' => esc_html__( 'Layout 2', 'petslist' ),
                ),
            )
        );

		// Listing owner information
		$wp_customize->add_setting( 'custom_group_individual', 
            array(
                'default'           => $this->defaults['custom_group_individual'],
                'transport'         => 'refresh',
                'sanitize_callback' => 'sanitize_multiple_checkbox',
            ) 
        );
        $wp_customize->add_control( new Multiple_Checkbox( $wp_customize, 'custom_group_individual', 
            array(
                'label'    => esc_html__( 'Custom Fileds data', 'petslist' ),
                'description'    => esc_html__( 'Select type to display field group in listing details.', 'petslist' ),
                'section'  => 'listing_single_section',
                'type'  => 'checkbox-multiple',
                'choices'  => $group_list,
            ) 
        ) );

		// Listing owner information      
        $wp_customize->add_setting( 'single_sidebar_listing_info',
            array(
                'default' => $this->defaults['single_sidebar_listing_info'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_radio_sanitization'
            )
        );
        $wp_customize->add_control( 'single_sidebar_listing_info',
            array(
                'label' => esc_html__( 'Listing Author', 'petslist' ),
                'section' => 'listing_single_section',
                'description' => esc_html__( 'This is listing information like store info, store owner info, store manager info', 'petslist' ),
                'type' => 'select',
                'choices' => array(
                    'listing_owner_info' => esc_html__( 'Listing Owner Information', 'petslist' ),
                    'listing_info' => esc_html__( 'Listing Information', 'petslist' ),
                ),
            )
        );

		// Related Listing
		$wp_customize->add_setting(
			'listing_related',
			[
				'default'           => $this->defaults['listing_related'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'rttheme_switch_sanitization',
			]
		);
		$wp_customize->add_control(
			new Switcher( $wp_customize, 'listing_related',
				[
					'label'   => esc_html__( 'Related Listing', 'petslist' ),
					'section' => 'listing_single_section',
				]
			)
		);
	}

	/* Listings Search
	==================================================================================================*/
	protected function __listings_search_settings_controls($wp_customize) {
		$group_list = $this->custom_field_group_list();
		//Listing Banner Search Items
		$wp_customize->add_setting('listing_search_widget_search', array(
            'default' => '',
            'sanitize_callback' => 'esc_html',
        ));
        $wp_customize->add_control(new Heading($wp_customize, 'listing_search_widget_search', array(
            'label' => esc_html__( 'Search Widget', 'petslist' ),
            'section' => 'listings_search_section',
        )));

		$wp_customize->add_setting( 'custom_fields_search_items', 
            array(
                'default'           => $this->defaults['custom_fields_search_items'],
                'transport'         => 'refresh',
                'sanitize_callback' => 'sanitize_multiple_checkbox',
            ) 
        );
        $wp_customize->add_control( new Multiple_Checkbox( $wp_customize, 'custom_fields_search_items', 
            array(
                'label'    => esc_html__( 'Custom Fileds', 'petslist' ),
                'description'    => esc_html__( 'Select type to display in search widget.', 'petslist' ),
                'section'  => 'listings_search_section',
                'type'  => 'checkbox-multiple',
                'choices'  => $group_list,
            ) 
        ) );
		
		// Listing Search Type
        $wp_customize->add_setting( 'listing_price_search_type',
        array(
            'default' => $this->defaults['listing_price_search_type'],
            'transport' => 'refresh',
            'sanitize_callback' => 'rttheme_text_sanitization'
        )
        );
        $wp_customize->add_control( 'listing_price_search_type',
            array(
                'label'    => esc_html__( 'Price Search Type', 'petslist' ),
                'section' => 'listings_search_section',
                'description'    => esc_html__( 'Price Search style.', 'petslist' ),
                'type' => 'select',
                'choices'  => array(
                    'input'      => esc_html__( 'Input Box', 'petslist' ),
                    'range'   => esc_html__( 'Range Slider', 'petslist' ),
                ),
            )
        );

		// Price minimum range
        $wp_customize->add_setting( 'listing_widget_min_price',
            array(
                'default' => $this->defaults['listing_widget_min_price'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_sanitize_integer',
            )
        );
        $wp_customize->add_control( 'listing_widget_min_price',
            array(
                'label' => esc_html__( 'Min Price', 'petslist' ),
                'section' => 'listings_search_section',
                'type' => 'number',
            )
        );
		// Price maximum range
        $wp_customize->add_setting( 'listing_widget_max_price',
            array(
                'default' => $this->defaults['listing_widget_max_price'],
                'transport' => 'refresh',
                'sanitize_callback' => 'rttheme_sanitize_integer',
            )
        );
        $wp_customize->add_control( 'listing_widget_max_price',
            array(
                'label' => esc_html__( 'Max Price', 'petslist' ),
                'section' => 'listings_search_section',
                'type' => 'number',
            )
        );
	}

	public function custom_field_group_list() {
        $list = [];
        $group_ids = Functions::get_cfg_ids();
        foreach ( $group_ids as $id ) {
            $list[ $id ] = get_the_title( $id );
        }
        return $list;
    }
}