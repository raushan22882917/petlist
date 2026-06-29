<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.1.0
 */

namespace RadiusTheme\Petslist;

class Scripts {

	public $version;
	protected static $instance = null;

	public function __construct() {
		$this->version = Constants::$theme_version;
		add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ], 12 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 15 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 1 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'admin_enqueue_scripts' ), 1 );
	}

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function fonts_url() {
		$fonts_url = '';
		$subsets   = '';
		$bodyFont  = 'Plus Jakarta Sans';
		$bodyFW    = '300,400,500,600,700';

		$menuFont  = 'Baloo Bhaijaan 2';
		$menuFontW = '300,400,500,600,700';

		$hFont  = 'Baloo Bhaijaan 2';
		$hFontW = '400,500,600,700,800';
		$h1Font = '';
		$h2Font = '';
		$h3Font = '';
		$h4Font = '';
		$h5Font = '';
		$h6Font = '';

		// Body Font
		$body_font = json_decode( Options::$options['typo_body'], true );

		if ( $body_font['font'] == 'Inherit' ) {
			$bodyFont = 'Lato';
		} else {
			$bodyFont = $body_font['font'];
		}
		$bodyFontW = $body_font['regularweight'];

		// Menu Font
		$menu_font = json_decode( Options::$options['typo_menu'], true );
		if ( $menu_font['font'] == 'Inherit' ) {
			$menuFont = 'Nunito';
		} else {
			$menuFont = $menu_font['font'];
		}
		$menuFontW = $menu_font['regularweight'];

		// Heading Font
		$h_font = json_decode( Options::$options['typo_heading'], true );
		if ( $h_font['font'] == 'Inherit' ) {
			$hFont = 'Nunito';
		} else {
			$hFont = $h_font['font'];
		}
		$hFontW  = $h_font['regularweight'];
		$h1_font = json_decode( Options::$options['typo_h1'], true );
		$h2_font = json_decode( Options::$options['typo_h2'], true );
		$h3_font = json_decode( Options::$options['typo_h3'], true );
		$h4_font = json_decode( Options::$options['typo_h4'], true );
		$h5_font = json_decode( Options::$options['typo_h5'], true );
		$h6_font = json_decode( Options::$options['typo_h6'], true );

		if ( 'off' !== _x( 'on', 'Google font: on or off', 'petslist' ) ) {
			if ( ! empty( $h1_font['font'] ) ) {
				if ( $h1_font['font'] == 'Inherit' ) {
					$h1Font  = $hFont;
					$h1FontW = $hFontW;
				} else {
					$h1Font  = $h2_font['font'];
					$h1FontW = $h1_font['regularweight'];
				}
			}
			if ( ! empty( $h2_font['font'] ) ) {
				if ( $h2_font['font'] == 'Inherit' ) {
					$h2Font  = $hFont;
					$h2FontW = $hFontW;
				} else {
					$h2Font  = $h2_font['font'];
					$h2FontW = $h2_font['regularweight'];
				}
			}
			if ( ! empty( $h3_font['font'] ) ) {
				if ( $h3_font['font'] == 'Inherit' ) {
					$h3Font  = $hFont;
					$h3FontW = $hFontW;
				} else {
					$h3Font  = $h3_font['font'];
					$h3FontW = $h3_font['regularweight'];
				}
			}
			if ( ! empty( $h4_font['font'] ) ) {
				if ( $h4_font['font'] == 'Inherit' ) {
					$h4Font  = $hFont;
					$h4FontW = $hFontW;
				} else {
					$h4Font  = $h4_font['font'];
					$h4FontW = $h4_font['regularweight'];
				}
			}
			if ( ! empty( $h5_font['font'] ) ) {
				if ( $h5_font['font'] == 'Inherit' ) {
					$h5Font  = $hFont;
					$h5FontW = $hFontW;
				} else {
					$h5Font  = $h5_font['font'];
					$h5FontW = $h5_font['regularweight'];
				}
			}
			if ( ! empty( $h6_font['font'] ) ) {
				if ( $h6_font['font'] == 'Inherit' ) {
					$h6Font  = $hFont;
					$h6FontW = $hFontW;
				} else {
					$h6Font  = $h6_font['font'];
					$h6FontW = $h6_font['regularweight'];
				}
			}

			$check_families = [];
			$font_families  = [];

			// Body Font
			if ( 'off' !== $bodyFont ) {
				$font_families[] = $bodyFont . ':300,400,500,600,700';
			}
			$check_families[] = $bodyFont;

			// Menu Font
			if ( 'off' !== $menuFont ) {
				if ( ! in_array( $menuFont, $check_families ) ) {
					$font_families[]  = $menuFont . ':300,400,500,600,700';
					$check_families[] = $menuFont;
				}
			}

			// Heading Font
			if ( 'off' !== $hFont ) {
				if ( ! in_array( $hFont, $check_families ) ) {
					$font_families[]  = $hFont . ':300,400,500,600,700';
					$check_families[] = $hFont;
				}
			}

			// Heading 1 Font
			if ( ! empty( $h1_font['font'] ) ) {
				if ( 'off' !== $h1Font ) {
					if ( ! in_array( $h1Font, $check_families ) ) {
						$font_families[]  = $h1Font . ':' . $h1FontW;
						$check_families[] = $h1Font;
					}
				}
			}
			// Heading 2 Font
			if ( ! empty( $h2_font['font'] ) ) {
				if ( 'off' !== $h2Font ) {
					if ( ! in_array( $h2Font, $check_families ) ) {
						$font_families[]  = $h2Font . ':' . $h2FontW;
						$check_families[] = $h2Font;
					}
				}
			}
			// Heading 3 Font
			if ( ! empty( $h3_font['font'] ) ) {
				if ( 'off' !== $h3Font ) {
					if ( ! in_array( $h3Font, $check_families ) ) {
						$font_families[]  = $h3Font . ':' . $h3FontW;
						$check_families[] = $h3Font;
					}
				}
			}
			// Heading 4 Font
			if ( ! empty( $h4_font['font'] ) ) {
				if ( 'off' !== $h4Font ) {
					if ( ! in_array( $h4Font, $check_families ) ) {
						$font_families[]  = $h4Font . ':' . $h4FontW;
						$check_families[] = $h4Font;
					}
				}
			}

			// Heading 5 Font
			if ( ! empty( $h5_font['font'] ) ) {
				if ( 'off' !== $h5Font ) {
					if ( ! in_array( $h5Font, $check_families ) ) {
						$font_families[]  = $h5Font . ':' . $h5FontW;
						$check_families[] = $h5Font;
					}
				}
			}
			// Heading 6 Font
			if ( ! empty( $h6_font['font'] ) ) {
				if ( 'off' !== $h6Font ) {
					if ( ! in_array( $h6Font, $check_families ) ) {
						$font_families[]  = $h6Font . ':' . $h6FontW;
						$check_families[] = $h6Font;
					}
				}
			}
			$final_fonts = array_unique( $font_families );
			$query_args  = [
				'family'  => urlencode( implode( '|', $final_fonts ) ),
				'subset'  => urlencode( $subsets ),
				'display' => urlencode( 'fallback' ),
			];

			$fonts_url = add_query_arg( $query_args, "//fonts.googleapis.com/css" );
		}

		return esc_url_raw( $fonts_url );
	}

	public function register_scripts() {
		/* Deregister */
		wp_deregister_style( 'font-awesome' );
		// Google fonts
		wp_register_style( 'petslist-gfonts', $this->fonts_url(), [], $this->version );
		// Style
		wp_register_style( 'font-awesome', Helper::get_css( 'font-awesome-all.min' ), [], $this->version );
		wp_register_style( 'fontello', Helper::get_css( 'fontello' ), [], $this->version );
		wp_register_style( 'bootstrap', Helper::get_css( 'bootstrap.min' ), [], $this->version );
		wp_register_style( 'animate', Helper::get_css( 'animate.min' ), [], $this->version );
		wp_register_style( 'rangeSlider', Helper::get_css( 'ion.rangeSlider.min' ), [], $this->version );
		wp_register_style( 'select2', Helper::get_css( 'select2.min' ), [], $this->version );
		wp_register_style( 'petslist-default', Helper::get_maybe_rtl_css( 'default' ), [], $this->version );
		wp_register_style( 'petslist-main', Helper::get_maybe_rtl_css( 'main' ), [], $this->version );

		// Script
		wp_register_script( 'bootstrap', Helper::get_js( 'bootstrap.bundle.min' ), [ 'jquery' ], $this->version, true );
		wp_register_script( 'wow', Helper::get_js( 'wow.min' ), [ 'jquery' ], $this->version, true );
		wp_register_script( 'rangeSlider', Helper::get_js( 'ion.rangeSlider.min' ), [ 'jquery' ], $this->version, true );
		wp_register_script( 'select2', Helper::get_js( 'select2.min' ), [ 'jquery' ], $this->version, true );
		wp_register_script( 'petslist-main', Helper::get_js( 'main' ), [ 'jquery', 'select2' ], $this->version, true );
	}

	public function enqueue_scripts() {
		/*------CSS--------*/
		wp_enqueue_style( 'petslist-gfonts' );
		wp_enqueue_style( 'bootstrap' );
		wp_enqueue_style( 'animate' );
		wp_enqueue_style( 'rangeSlider' );
		wp_enqueue_style( 'select2' );
		wp_enqueue_style( 'font-awesome' );
		wp_enqueue_style( 'fontello' );
		$this->dynamic_style();
		wp_enqueue_style( 'petslist-default' );
		wp_enqueue_style( 'petslist-main' );

		/*-------JS--------*/
		$this->conditional_scripts(); // Conditional Scripts
		wp_enqueue_script( 'bootstrap' );
		wp_enqueue_script( 'wow' );
		wp_enqueue_script( 'rangeSlider' );
		wp_enqueue_script( 'select2' );

		wp_enqueue_script( 'petslist-main' );
		$this->localized_scripts(); // Localization
	}

	private function conditional_scripts() {
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}

	public function admin_enqueue_scripts(){
        //Css
		wp_enqueue_style( 'petslist-gfonts', $this->fonts_url(), array(), $this->version );
        wp_enqueue_style( 'select2',  Helper::get_css( 'select2.min' ), array(), $this->version );

        // Js
        wp_enqueue_script( 'select2', Helper::get_js( 'select2.min' ), array(), $this->version );
    }

	private function localized_scripts() {
		global $post;

		$localize_data = [
			'ajaxUrl'              => admin_url( 'admin-ajax.php' ),
			'appendHtml'           => '',
			'themeUrl'             => get_stylesheet_directory_uri(),
		];

		$localize_data = apply_filters( 'petslist_localized_data', $localize_data );
		wp_localize_script( 'petslist-main', 'PetslistObj', $localize_data );
	}

	private function dynamic_style() {
		$dynamic_css = $this->template_style();
		ob_start();
		Helper::requires( 'frontend.php', 'dynamic-styles' );
		$dynamic_css .= ob_get_clean();
		$dynamic_css = $this->optimized_css( $dynamic_css );
		wp_register_style( 'petslist-dynamic', false );
		wp_enqueue_style( 'petslist-dynamic' );
		wp_add_inline_style( 'petslist-dynamic', $dynamic_css );
	}

	private function template_style() {
		$style = '';

		if ( Options::$padding_top != '' ) {
			$style .= 'body .content-area {padding-top:' . Options::$padding_top . '!important;}';
		}
		if ( Options::$padding_bottom != '' ) {
			$style .= 'body .content-area {padding-bottom:' . Options::$padding_bottom . '!important}';
		}

		$bgimg = ! empty( wp_get_attachment_image_url( Options::$options['banner_image'], 'full' ) ) ? wp_get_attachment_image_url( Options::$options['banner_image'],
			'full' ) : '';

		if ( ! empty( $bgimg ) ) {
			$style .= '.banner-search {background-image:url(' . $bgimg . ');}';
		}

		return $style;
	}

	private function optimized_css( $css ) {
		$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );
		$css = str_replace( [ "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ], ' ', $css );

		return $css;
	}

	private function add_wrapper_to_css( $css, $base ) {
		$parts = explode( '}', $css );
		foreach ( $parts as &$part ) {
			if ( empty( $part ) ) {
				continue;
			}
			$firstPart = substr( $part, 0, strpos( $part, '{' ) + 1 );
			$lastPart  = substr( $part, strpos( $part, '{' ) + 2 );
			$subParts  = explode( ',', $firstPart );
			foreach ( $subParts as &$subPart ) {
				$subPart = str_replace( "\n", '', $subPart );
				$subPart = $base . ' ' . trim( $subPart );
			}
			$part = implode( ', ', $subParts ) . $lastPart;
		}
		$resultCSS = implode( "}\n", $parts );
		return $resultCSS;
	}
}