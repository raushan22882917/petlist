<?php

namespace RadiusTheme\Petslist\Customizer;

use RadiusTheme\Petslist\Helper;
use RadiusTheme\Petslist\Customizer\Settings\Blog;
use RadiusTheme\Petslist\Customizer\Settings\Blog_Layout;
use RadiusTheme\Petslist\Customizer\Settings\Color;
use RadiusTheme\Petslist\Customizer\Settings\Contact_Info;
use RadiusTheme\Petslist\Customizer\Settings\Error;
use RadiusTheme\Petslist\Customizer\Settings\Error_Layout;
use RadiusTheme\Petslist\Customizer\Settings\General;
use RadiusTheme\Petslist\Customizer\Settings\Header;
use RadiusTheme\Petslist\Customizer\Settings\Footer;
use RadiusTheme\Petslist\Customizer\Settings\Listings;
use RadiusTheme\Petslist\Customizer\Settings\Listing_Archive_Layout;
use RadiusTheme\Petslist\Customizer\Settings\Listing_Single_Layout;
use RadiusTheme\Petslist\Customizer\Settings\Page_Layout;
use RadiusTheme\Petslist\Customizer\Settings\Post;
use RadiusTheme\Petslist\Customizer\Settings\Post_Layout;
use RadiusTheme\Petslist\Customizer\Typography\Typography;

Helper::requires( 'customizer/controls/Sanitization.php');

class Init {
	protected static $instance = null;

	/**
	 * Create an inaccessible constructor.
	 */
	private function __construct() {
		$this->includes();
	}

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function includes() {
		new General();
		new Header();
		new Footer();
		new Blog();
		new Post();
		new Error();
		new Contact_Info();
		new Typography();
		new Color();
		// Layout
		new Blog_Layout();
		new Post_Layout();
		new Page_Layout();
		new Error_Layout();
		// Listings
		if ( class_exists( 'Rtcl' ) ) {
			new Listings();
			new Listing_Archive_Layout();
			new Listing_Single_Layout();
		}
	}
}