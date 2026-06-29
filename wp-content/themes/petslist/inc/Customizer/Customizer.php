<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

namespace RadiusTheme\Petslist\Customizer;

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class Customizer {

	// Get our default values
	protected $defaults;
	protected static $instance = null;

	public function __construct() {
		// Register Panels
		add_action( 'customize_register', [ $this, 'add_customizer_panels' ] );
		// Register sections
		add_action( 'customize_register', [ $this, 'add_customizer_sections' ] );
	}

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function populated_default_data() {
		$this->defaults = Default_Data::default_values();
	}

	/**
	 * Customizer Panels
	 */
	public function add_customizer_panels( $wp_customize ) {
		// Add General Panel
        $wp_customize->add_panel( 'rttheme_general_settings',
            [
                'title' => esc_html__( 'General', 'petslist' ),
                'description' => esc_html__( 'All general settings here.', 'petslist' ),
                'priority' => 1,
            ]
        );
		// Add Header Panel
        $wp_customize->add_panel( 'rttheme_header_settings',
            [
                'title' => esc_html__( 'Header', 'petslist' ),
                'description' => esc_html__( 'All header settings here.', 'petslist' ),
                'priority' => 2,
            ]
        );
		// Add Footer Panel
        $wp_customize->add_panel( 'rttheme_footer_settings',
            [
                'title' => esc_html__( 'Footer', 'petslist' ),
                'description' => esc_html__( 'All footer settings here.', 'petslist' ),
                'priority' => 3,
            ]
        );
		// Color Panel
		$wp_customize->add_panel( 'rttheme_color_panel',
			[
				'title'       => esc_html__( 'Color', 'petslist' ),
				'description' => esc_html__( 'Change site color', 'petslist' ),
				'priority'    => 5,
			]
		);
		// Add Blog Panel
        $wp_customize->add_panel( 'rttheme_blog_settings',
            [
                'title' => esc_html__( 'Blog', 'petslist' ),
                'description' => esc_html__( 'Blog archive & post single settings.', 'petslist' ),
                'priority' => 6,
            ]
        );
		// Layout Panel
		$wp_customize->add_panel( 'rttheme_layouts_defaults',
			[
				'title'       => esc_html__( 'Layout', 'petslist' ),
				'description' => esc_html__( 'Adjust the overall layout for your site.', 'petslist' ),
				'priority'    => 7,
			]
		);
		// Listing Panel
		$wp_customize->add_panel( 'rttheme_listing_settings',
			[
				'title'       => esc_html__( 'Listing', 'petslist' ),
				'description' => esc_html__( 'Adjust the overall layout for your site.', 'petslist' ),
				'priority'    => 8,
			]
		);
		
	}

	/**
	 * Customizer sections
	 */
	public function add_customizer_sections( $wp_customize ) {
		// Rename the default Colors section
		$wp_customize->get_section( 'colors' )->title = 'Background';
		// Move the default Colors section to our new Colors Panel
		$wp_customize->get_section( 'colors' )->panel = 'colors_panel';
		// Change the Priority of the default Colors section so it's at the top of our Panel
		$wp_customize->get_section( 'colors' )->priority = 10;

		/* = General Panel
		===================================================================*/
		$wp_customize->add_section( 'general_section',
			[
				'title'    => esc_html__( 'General', 'petslist' ),
				'priority' => 1,
				'panel'    => 'rttheme_general_settings',
			]
		);
		$wp_customize->add_section( 'contact_info_section',
			[
				'title'    => esc_html__( 'Contact & Social', 'petslist' ),
				'priority' => 2,
				'panel'    => 'rttheme_general_settings',
			]
		);
		
		/* = Header Panel
		===================================================================*/
		$wp_customize->add_section( 'header_main_section',
			[
				'title'    => esc_html__( 'Header Variation', 'petslist' ),
				'priority' => 1,
				'panel'    => 'rttheme_header_settings',
			]
		);
		$wp_customize->add_section( 'header_switches_section',
			[
				'title'    => esc_html__( 'Header Control Switches', 'petslist' ),
				'priority' => 2,
				'panel'    => 'rttheme_header_settings',
			]
		);

		/* = Footer Panel
		===================================================================*/
		$wp_customize->add_section( 'footer_all_section',
			[
				'title'    => esc_html__( 'All Footer', 'petslist' ),
				'panel'    => 'rttheme_footer_settings',
				'priority' => 1,
			]
		);
		$wp_customize->add_section( 'footer_1',
			[
				'title'    => esc_html__( 'Footer 1', 'petslist' ),
				'panel'    => 'rttheme_footer_settings',
				'priority' => 2,
			]
		);
		$wp_customize->add_section( 'footer_2',
			[
				'title'    => esc_html__( 'Footer 2', 'petslist' ),
				'panel'    => 'rttheme_footer_settings',
				'priority' => 3,
			]
		);
		$wp_customize->add_section( 'footer_3',
			[
				'title'    => esc_html__( 'Footer 3', 'petslist' ),
				'panel'    => 'rttheme_footer_settings',
				'priority' => 4,
			]
		);

		/* = Blog Panel
		===================================================================*/
		$wp_customize->add_section( 'blog_archive_section',
			[
				'title'    => esc_html__( 'Blog', 'petslist' ),
				'priority' => 1,
				'panel'    => 'rttheme_blog_settings',
			]
		);
		$wp_customize->add_section( 'single_post_section',
			[
				'title'    => esc_html__( 'Post Details', 'petslist' ),
				'priority' => 2,
				'panel'    => 'rttheme_blog_settings',
			]
		);

		/* = Listings Panel
		===================================================================*/
		$wp_customize->add_section( 'listings_section',
			[
				'title'    => esc_html__( 'Listing Settings', 'petslist' ),
				'priority' => 1,
				'panel'    => 'rttheme_listing_settings',
			]
		);
		$wp_customize->add_section( 'listing_archive_section',
			[
				'title'    => esc_html__( 'Listing Archive', 'petslist' ),
				'priority' => 2,
				'panel'    => 'rttheme_listing_settings',
			]
		);
		$wp_customize->add_section( 'listing_single_section',
			[
				'title'    => esc_html__( 'Listing Single', 'petslist' ),
				'priority' => 3,
				'panel'    => 'rttheme_listing_settings',
			]
		);
		$wp_customize->add_section( 'listings_search_section',
			[
				'title'    => esc_html__( 'Listings Search', 'petslist' ),
				'priority' => 3,
				'panel'    => 'rttheme_listing_settings',
			]
		);

		/* = Color Panel settings
		===================================================================*/
		$wp_customize->add_section( 'site_color_section',
			[
				'title'    => esc_html__( 'Site Color', 'petslist' ),
				'panel'    => 'rttheme_color_panel',
				'priority' => 1,
			]
		);
		$wp_customize->add_section( 'button_color_section',
			[
				'title'    => esc_html__( 'Button Color', 'petslist' ),
				'panel'    => 'rttheme_color_panel',
				'priority' => 2,
			]
		);
		$wp_customize->add_section( 'footer_color_section',
			[
				'title'    => esc_html__( 'Footer Color', 'petslist' ),
				'panel'    => 'rttheme_color_panel',
				'priority' => 3,
			]
		);

		/* = Layout Panel settings
		===================================================================*/
		// Add Blog Layout Section
		$wp_customize->add_section( 'blog_layout_section',
			[
				'title'    => esc_html__( 'Blog Layout', 'petslist' ),
				'priority' => 1,
				'panel'    => 'rttheme_layouts_defaults',
			]
		);
		// Add Single Post Layout Section
		$wp_customize->add_section( 'single_post_layout_section',
			[
				'title'    => esc_html__( 'Single Post Layout', 'petslist' ),
				'priority' => 2,
				'panel'    => 'rttheme_layouts_defaults',
			]
		);
		// Add Pages Layout Section
		$wp_customize->add_section( 'page_layout_section',
			[
				'title'    => esc_html__( 'Pages Layout', 'petslist' ),
				'priority' => 2,
				'panel'    => 'rttheme_layouts_defaults',
			]
		);
		// Add Error Layout Section
		$wp_customize->add_section( 'error_layout_section',
			[
				'title'    => esc_html__( 'Error Layout', 'petslist' ),
				'priority' => 3,
				'panel'    => 'rttheme_layouts_defaults',
			]
		);
		// Add Listing Layout Section
		$wp_customize->add_section( 'listing_archive_layout_section',
			[
				'title'    => esc_html__( 'Listing Archive Layout', 'petslist' ),
				'priority' => 4,
				'panel'    => 'rttheme_layouts_defaults',
			]
		);
		// Add Listing Single Layout Section
		$wp_customize->add_section( 'listing_single_layout_section',
			[
				'title'    => esc_html__( 'Listing Single Layout', 'petslist' ),
				'priority' => 5,
				'panel'    => 'rttheme_layouts_defaults',
			]
		);
		
		/* = 404 Panel settings
		===================================================================*/
		// Add Error Page Section
		$wp_customize->add_section( 'error_section',
			[
				'title'    => esc_html__( 'Error Page', 'petslist' ),
				'priority' => 19,
			]
		);
	}

}
