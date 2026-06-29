<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.1.0
 */

namespace RadiusTheme\Petslist;

use Rtcl\Helpers\Breadcrumb;

class General {

	protected static $instance = null;

	public function __construct() {
		add_action( 'after_setup_theme', [ $this, 'theme_setup' ] );
		add_action( 'widgets_init', [ $this, 'register_sidebars' ], 99 );
		add_action( 'petslist_breadcrumb', [ $this, 'breadcrumb' ] );
		add_filter( 'body_class', [ $this, 'body_classes' ] );
		add_action( 'wp_head', [ $this, 'pingback' ] );
		add_filter( 'upload_mimes',	array( $this, 'petslist_mime_types' ));
		add_action( 'wp_footer', [ $this, 'scroll_to_top_html' ], 1 );
		add_filter( 'get_search_form', [ $this, 'search_form' ] );
		add_filter( 'post_class', [ $this, 'hentry_config' ] );
		add_filter( 'wp_list_categories', [ $this, 'add_span_cat_count' ] );
		add_filter( 'get_archives_link', [ $this, 'add_span_archive_count' ] );
		add_filter( 'widget_text', 'do_shortcode' );
		add_action( 'site_prealoader', array( $this, 'preloader' ) );
		// Disable Gutenberg widget block
		add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );// Disables the block editor from managing widgets in the Gutenberg plugin.
		add_filter( 'use_widgets_block_editor', '__return_false' ); // Disables the block editor from managing widgets.
	}

	// disable wp responsive images
	function disable_wp_responsive_images() {
		return 1;
	}

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function theme_setup() {
		// Theme supports
		add_theme_support( 'title-tag' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'html5', [ 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ] );
		add_theme_support( 'custom-logo' );
		add_theme_support( "custom-header" );
		add_theme_support( "custom-background" );

		add_theme_support( 'editor-color-palette', array(
			array(
				'name' => esc_html__( 'Primary Color', 'petslist' ),
				'slug' => 'petslist-primary',
				'color' => '#02c5bd',
			),
			array(
				'name' => esc_html__( 'Secondary Color', 'petslist' ),
				'slug' => 'petslist-secondary',
				'color' => '#ff3d41',
			),
			array(
				'name' => esc_html__( 'Dark gray', 'petslist' ),
				'slug' => 'petslist-dark-gray',
				'color' => '#070c3e',
			),
			array(
				'name' => esc_html__( 'Light gray', 'petslist' ),
				'slug' => 'petslist-light-gray',
				'color' => '#F6F9F9',
			),
			array(
				'name' => esc_html__( 'White', 'petslist' ),
				'slug' => 'white',
				'color' => '#ffffff',
			),
			array(
				'name' => esc_html__( 'Black', 'petslist' ),
				'slug' => 'black',
				'color' => '#000000',
			),
			array(
				'name' => esc_html__( 'Luminous vivid amber', 'petslist' ),
				'slug' => 'luminous-vivid-amber',
				'color' => '#FCB900',
			),
			array(
				'name' => esc_html__( 'Vivid purple', 'petslist' ),
				'slug' => 'vivid-purple',
				'color' => '#9B51E0',
			),
			array(
				'name' => esc_html__( 'Pale pink', 'petslist' ),
				'slug' => 'pale-pink',
				'color' => '#F78DA7',
			),
			array(
				'name' => esc_html__( 'Vivid green cyan', 'petslist' ),
				'slug' => 'vivid-green-cyan',
				'color' => '#00D084',
			),
			array(
				'name' => esc_html__( 'Light green cyan', 'petslist' ),
				'slug' => 'light-green-cyan',
				'color' => '#7BDCB5',
			),
			array(
				'name' => esc_html__( 'Vivid cyan blue', 'petslist' ),
				'slug' => 'vivid-cyan-blue',
				'color' => '#0693E3',
			),
			array(
				'name' => esc_html__( 'Pale cyan blue', 'petslist' ),
				'slug' => 'pale-cyan-blue',
				'color' => '#8ED1FC',
			),
		) );

		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'editor-styles' );

		// Image sizes
		$sizes = [
			'petslist-size1' => [ 1200, 650, true ], // When Full width
			'petslist-size2' => [ 470, 340, true ], // Listing Thumbnail Size
		];

		$sizes = apply_filters( 'petslist_image_size', $sizes );

		foreach ( $sizes as $size => $value ) {
			add_image_size( $size, $value[0], $value[1], $value[2] );
		}

		// Register menus
		register_nav_menus(
			[
				'primary' => esc_html__( 'Primary', 'petslist' ),
			]
		);
	}

	public function register_sidebars() {
		register_sidebar(
			[
				'name'          => esc_html__( 'Sidebar', 'petslist' ),
				'id'            => 'sidebar',
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			]
		);

		//Footer 1
		$footer_widget_titles1 = array(
			'1' => esc_html__( 'Footer (Style 1) 1', 'petslist' ),
			'2' => esc_html__( 'Footer (Style 1) 2', 'petslist' ),
			'3' => esc_html__( 'Footer (Style 1) 3', 'petslist' ),
			'4' => esc_html__( 'Footer (Style 1) 4', 'petslist' ),
		);	
		$f1_widgets_area = Options::$options['f1_widgets_area'];
		for ( $i = 1; $i <= $f1_widgets_area; $i++ ) {
			register_sidebar( array(
				'name'          => $footer_widget_titles1[$i],
				'id'            => 'footer-widget-1-'.$i,
				'before_widget' => '<div id="%1$s" class="widget footer-'.$i.'-widgets %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			) );
		}
	}

	public function body_classes( $classes ) {
		// Theme Version
		$theme     = wp_get_theme();
		$classes[] = $theme->TextDomain . '-version-' . $theme->Version;
		$classes[] = 'theme-petslist';

		if ( Options::$has_tr_header ) {
			$classes[] = 'trheader';
		} else {
			$classes[] = 'no-trheader';
		}

		if ( is_front_page() && ! is_home() ) {
			$classes[] = 'front-page';
		}

        if ( is_author() ) {
            $classes[] = 'rtcl';
        }

		if ( Helper::has_full_width() ) {
			$classes[] = 'is-full-width';
		}

		if ( Options::$layout === 'left-sidebar' ) {
			$classes[] = 'sidebar-in-left';
		}
		$classes[] = Options::$options['header_link_btn_mobile'] ? '' : 'mobile-link-btn-off';
		$classes[] = Options::$options['header_login_btn_mobile'] ? '' : 'mobile-login-btn-off';
		$classes[] = Options::$options['header_chat_btn_mobile'] ? '' : 'mobile-chat-btn-off';
		return $classes;
	}

	public function is_blog() {
		return ( is_archive() || is_author() || is_category() || is_home() || is_single() || is_tag() ) && 'post' == get_post_type();
	}

	public function pingback() {
		if ( is_singular() && pings_open() ) {
			printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
		}
	}

	public function wp_body_open() {
		do_action( 'wp_body_open' );
	}

	public function scroll_to_top_html() {
		if ( Options::$options['back_to_top'] ) {
			echo '<a href="#" class="scrollToTop" style=""><i class="fa-solid fa-angle-up"></i></a>';
		}
	}

	public function search_form() {
		$output = '
		<form method="get" class="custom-search-form" action="' . esc_url( home_url( '/' ) ) . '">
            <div class="search-box">
				<div class="form-group mb-0">
					<input type="text" class="form-control" placeholder="' . esc_attr__( 'Search here...', 'petslist' ) . '" value="' . get_search_query() . '" name="s" />
					<button>
						<span class="search-btn">
							<i class="icon-pl-search"></i>
						</span>
					</button>
				</div>
            </div>
		</form>
		';

		return $output;
	}

	public function hentry_config( $classes ) {
		if ( is_search() || is_page() ) {
			$classes = array_diff( $classes, [ 'hentry' ] );
		}

		return $classes;
	}

	public function add_span_cat_count( $links ) {
		$links = str_replace( '</a> (', '<span>(', $links );
		$links = str_replace( ')', ')</span></a>', $links );

		return $links;
	}

	public function add_span_archive_count( $links ) {
		$links = str_replace( '</a>&nbsp;(', '<span>(', $links );
		$links = str_replace( ')', ')</span></a>', $links );

		return $links;
	}

	public function preloader() {
		$loading = wp_get_attachment_image( Options::$options['preloader_gif'], 'full' );
        echo '<div id="pageoverlay" class="pageoverlay"><span class="pageLoader">'.$loading.'</span></div>';
	}

	public function breadcrumb() {
		$args = [
			'delimiter'   => '&nbsp;<i class="icon-pl-angle-down-fat"></i>&nbsp;',
			'wrap_before' => '<nav class="rtcl-breadcrumb">',
			'wrap_after'  => '</nav>',
			'before'      => '',
			'after'       => '',
			'home'        => _x( 'Home', 'breadcrumb', 'petslist' ),
		];

		$breadcrumbs = new Breadcrumb();

		if ( ! empty( $args['home'] ) ) {
			$breadcrumbs->add_crumb( $args['home'], home_url() );
		}

		$args['breadcrumb'] = $breadcrumbs->generate();

		if ( ! empty( $args['breadcrumb'] ) ) {
			?>
            <section class="breadcrumbs-area">
                <div class="container">
					<?php
					printf( "%s", $args['wrap_before'] );
					foreach ( $args['breadcrumb'] as $key => $crumb ) {
						printf( "%s", $args['before'] );
						if ( ! empty( $crumb[1] ) && sizeof( $args['breadcrumb'] ) !== $key + 1 ) {
							echo '<a href="' . esc_url( $crumb[1] ) . '">' . esc_html( $crumb[0] ) . '</a>';
						} else {
							echo '<span>' . esc_html( $crumb[0] ) . '</span>';
						}
						printf( "%s", $args['after'] );
						if ( sizeof( $args['breadcrumb'] ) !== $key + 1 ) {
							printf( "%s", $args['delimiter'] );
						}
					}
					printf( "%s", $args['wrap_after'] );
					?>
                </div>
            </section>
			<?php
		}
	}

	public function petslist_mime_types( $mimes ){
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	}
}