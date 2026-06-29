<?php

/**
 * Main GutenbergController  Class.
 *
 * The main class that initiates and runs the plugin.
 *
 * @package  Classifid-listing
 *
 * @since    1.0.0
 */

namespace Rtcl\Controllers;

use Exception;
use Rtcl\Controllers\Blocks\AdminAjaxController;
use Rtcl\Controllers\Blocks\AllLocation;
use Rtcl\Controllers\Blocks\ListingAds;
use Rtcl\Controllers\Blocks\ListingCatBox;
use Rtcl\Controllers\Blocks\ListingsAjaxController;
use Rtcl\Controllers\Blocks\SingleLocation;
use Rtcl\Controllers\Blocks\ListingSearch;
use Rtcl\Helpers\Functions;
use WP_Error;

/**
 * Main GutenbergController  Class.
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.0.0
 */
class BlockController {

	public function __construct() {
		add_filter( 'init', [ $this, 'register_meta' ] );
		add_action( 'wp_enqueue_scripts', [ &$this, 'frontend_assets' ] );
		//add_action( 'admin_enqueue_scripts', [ &$this, 'admin_assets' ] );
		add_action( 'enqueue_block_editor_assets', [ &$this, 'editor_assets' ] );
		add_filter( 'block_categories_all', [ &$this, 'rtcl_gb_block_category' ], 10, 2 );

		// Save css when block trigger to save data
		add_action( 'wp_ajax_rtcl_block_css_save', [ $this, 'save_block_css' ] );
		add_action( 'wp_ajax_rtcl_block_css_get_posts', [ $this, 'get_posts_call' ] );
		add_action( 'wp_ajax_rtcl_block_css_appended', [ $this, 'appended' ] );

		// Decide how css file will be loaded. default filesystem eg: filesystem or at header
		if ( apply_filters( 'rtcl_block_css_filesystem', true ) ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'add_block_css_file' ] );
		} else {
			add_action( 'wp_head', [ $this, 'add_block_inline_css' ], 100 );
		}

		new AdminAjaxController();
		new ListingsAjaxController();
		new ListingAds();
		new ListingCatBox();
		new AllLocation();
		new SingleLocation();
		new ListingSearch();
	}

	public function add_block_css_file() {
		$this->set_css_style( function_exists( 'is_shop' ) ? ( is_shop() ? wc_get_page_id( 'shop' ) : get_the_ID() ) : get_the_ID() );
	}

	/**
	 * Save Import CSS in the top of the File
	 *
	 * @return void Array of the Custom Message
	 */
	public function save_block_css() {
		try {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( [ 'message' => __( 'User permission error', 'classified-listing' ) ] );
			}

			if ( ! wp_verify_nonce( $_POST['rtcl_nonce'] ?? '', 'rtcl-nonce' ) ) {
				wp_send_json_error( [ 'message' => __( 'Session Expired!!', 'classified-listing' ) ] );
			}

			global $wp_filesystem;
			if ( ! $wp_filesystem ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			$post_id = sanitize_text_field( $_POST['post_id'] );
			if ( $post_id == 'rtcl-widget' && $_POST['has_block'] ) {
				update_option( $post_id, $_POST['block_css'] );
				wp_send_json_success( [ 'message' => esc_html__( 'Widget CSS Saved', 'classified-listing' ) ] );
			}

			$post_id        = (int) $post_id;
			$filename       = "rtcl-block-css-{$post_id}.css";
			$upload_dir_url = wp_upload_dir();
			$dir            = trailingslashit( $upload_dir_url['basedir'] ) . 'rtcl/';

			if ( $_POST['has_block'] ) {
				update_post_meta( $post_id, '_rtcl_block_active', 1 );
				$block_css = $this->set_top_css( $_POST['block_css'] );
				WP_Filesystem( false, $upload_dir_url['basedir'], true );
				if ( ! $wp_filesystem->is_dir( $dir ) ) {
					$wp_filesystem->mkdir( $dir );
				}
				if ( ! $wp_filesystem->put_contents( $dir . $filename, $block_css ) ) {
					wp_send_json_error( [ 'message' => __( 'CSS can not be saved due to permission!!!', 'classified-listing' ) ] );
				}
				update_post_meta( $post_id, '_rtcl_block_css', $block_css );
				wp_send_json_success( [ 'message' => __( 'Css file has been updated', 'classified-listing' ) ] );
			} else {
				delete_post_meta( $post_id, '_rtcl_block_active' );
				if ( file_exists( $dir . $filename ) ) {
					wp_delete_file( $dir . $filename );
				}
				delete_post_meta( $post_id, '_rtcl_block_css' );
				wp_send_json_success( [ 'message' => __( 'Data Delete Done', 'classified-listing' ) ] );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( [ 'message' => $e->getMessage() ] );
		}
	}


	/**
	 * Set CSS Style
	 *
	 * @return void
	 */
	public function set_css_style( $post_id ) {
		if ( $post_id ) {
			$upload_dir_url     = wp_get_upload_dir();
			$upload_css_dir_url = trailingslashit( $upload_dir_url['basedir'] );
			$css_dir_path       = $upload_css_dir_url . "rtcl/rtcl-block-css-{$post_id}.css";

			$css_dir_url = trailingslashit( $upload_dir_url['baseurl'] );
			if ( is_ssl() ) {
				$css_dir_url = str_replace( 'http://', 'https://', $css_dir_url );
			}

			// Reusable CSS
			$reusable_id = $this->reusable_id( $post_id );
			foreach ( $reusable_id as $id ) {
				$reusable_dir_path = $upload_css_dir_url . "rtcl/rtcl-block-css-{$id}.css";
				if ( file_exists( $reusable_dir_path ) ) {
					$css_url = $css_dir_url . "rtcl/rtcl-block-css-{$id}.css";
					wp_enqueue_style( "rtcl-block-post-{$id}", $css_url, [], RTCL_VERSION );
				} else {
					$css = get_post_meta( $id, '_rtcl_block_css', true );
					if ( $css ) {
						wp_enqueue_style( "rtcl-block-post-{$id}", $css, false, RTCL_VERSION );
					}
				}
			}

			if ( file_exists( $css_dir_path ) ) {
				$css_url = $css_dir_url . "rtcl/rtcl-block-css-{$post_id}.css";
				wp_enqueue_style( "rtcl-block-post-{$post_id}", $css_url, [], RTCL_VERSION );
			} elseif ( $css = get_post_meta( $post_id, '_rtcl_block_css', true ) ) {
				wp_enqueue_style( "rtcl-block-post-{$post_id}", $css, false, RTCL_VERSION );
			}
		}
	}


	/**
	 * Get All Reusable ID
	 *
	 * @return array Arg
	 */
	public function reusable_id( $post_id ) {
		$reusable_id = [];
		if ( $post_id ) {
			$post = get_post( $post_id );
			if ( has_blocks( $post->post_content ) ) {
				$blocks = parse_blocks( $post->post_content );
				foreach ( $blocks as $key => $value ) {
					if ( isset( $value['attrs']['ref'] ) ) {
						$reusable_id[] = $value['attrs']['ref'];
					}
				}
			}
		}

		return $reusable_id;
	}


	/**
	 * Save Import CSS in the top of the File
	 *
	 * @return void
	 * @throws Exception
	 * @since v.1.0.0
	 */
	public function appended( $server ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'rtcl_block_user_permission', __( 'User permission error', 'classified-listing' ) ) );
		}

		if ( ! wp_verify_nonce( $_POST['rtcl_nonce'] ?? '', 'rtcl-nonce' ) ) {
			wp_send_json_error( new WP_Error( 'rtcl_block_user_permission', esc_html__( 'Session Expired!!', 'classified-listing' ) ) );
		}

		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		$post    = $server->get_params();
		$css     = $post['inner_css'];
		$post_id = (int) sanitize_text_field( $post['post_id'] );
		if ( $post_id ) {
			$upload_dir_url = wp_upload_dir();
			$filename       = "rtcl-block-css-$post_id.css";
			$dir            = trailingslashit( $upload_dir_url['basedir'] ) . 'rtcl/';
			WP_Filesystem( false, $upload_dir_url['basedir'], true );
			if ( ! $wp_filesystem->is_dir( $dir ) ) {
				$wp_filesystem->mkdir( $dir );
			}
			if ( ! $wp_filesystem->put_contents( $dir . $filename, $css ) ) {
				wp_send_json_error( [ 'message' => __( 'CSS can not be saved due to permission!!!', 'classified-listing' ) ] );
			}
			wp_send_json_success( [ 'message' => __( 'Data retrieve done', 'classified-listing' ) ] );
		} else {
			wp_send_json_error( [ 'message' => __( 'Data not found!!', 'classified-listing' ) ] );
		}
	}

	/**
	 * Save Import CSS in the top of the File
	 *
	 * @return void
	 */
	public function get_posts_call() {
		if ( ! wp_verify_nonce( $_POST['rtcl_nonce'] ?? '', 'rtcl-nonce' ) ) {
			wp_send_json_error( new WP_Error( 'rtcl_block_data_not_found', __( 'Session Expired!!', 'classified-listing' ) ) );
		}

		if ( ! current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( new WP_Error( 'rtcl_block_user_permission', __( 'User permission error!!', 'classified-listing' ) ) );
		}

		$post = $_POST;
		if ( isset( $post['postId'] ) ) {
			$post = get_post( $post['postId'] );
			wp_send_json_success( $post ? $post->post_content : '' );
		} else {
			wp_send_json_error( new WP_Error( 'rtcl_block_data_not_found', __( 'Data not found!!', 'classified-listing' ) ) );
		}
	}


	/**
	 * Save Import CSS in the top of the File
	 *
	 * @param  STRING
	 *
	 * @return STRING
	 * @since v.1.0.0
	 */
	public function set_top_css( $get_css = '' ) {
		$css_url     = "@import url('https://fonts.googleapis.com/css?family=";
		$font_exists = substr_count( $get_css, $css_url );
		if ( $font_exists ) {
			$pattern = sprintf( '/%s(.+?)%s/ims', preg_quote( $css_url, '/' ), preg_quote( "');", '/' ) );
			if ( preg_match_all( $pattern, $get_css, $matches ) ) {
				$fonts   = $matches[0];
				$get_css = str_replace( $fonts, '', $get_css );
				if ( preg_match_all( '/font-weight[ ]?:[ ]?[\d]{3}[ ]?;/', $get_css, $matche_weight ) ) {
					$weight = array_map(
						function ( $val ) {
							$process = trim( str_replace( [ 'font-weight', ':', ';' ], '', $val ) );
							if ( is_numeric( $process ) ) {
								return $process;
							}
						},
						$matche_weight[0],
					);
					foreach ( $fonts as $key => $val ) {
						$fonts[ $key ] = str_replace( "');", '', $val ) . ':' . implode( ',', $weight ) . "');";
					}
				}
				$fonts   = array_unique( $fonts );
				$get_css = implode( '', $fonts ) . $get_css;
			}
		}

		return $get_css;
	}

	public function frontend_assets() {
		wp_enqueue_style( 'rtcl-public' );
		wp_enqueue_style( 'gb-frontend-block', rtcl()->get_assets_uri( 'css/gb-frontend-block.css' ), [], RTCL_VERSION );
	}

	public function editor_assets() {
		wp_enqueue_script( 'rtcl-gb-blocks-js', rtcl()->get_assets_uri( 'block/main.js' ), [ 'wp-block-editor', 'wp-blocks', 'wp-components', 'wp-element', 'wp-i18n' ], RTCL_VERSION, true );
		wp_localize_script(
			'rtcl-gb-blocks-js',
			'rtcl_block_script',
			[
				'ajaxurl'    => admin_url( 'admin-ajax.php' ),
				'rtcl_nonce' => wp_create_nonce( 'rtcl-nonce' ),
				'plugin'     => RTCL_URL,
			],
		);

		$localize_obj = [
			'single_location'     => [
				'style_options' => [
					[
						'value' => '1',
						'label' => __( 'Style 1', 'classified-listing' ),
					],
					[
						'value' => '2',
						'label' => __( 'Style 2', 'classified-listing' ),
					],
				],
			],
			'category'            => [
				'style_options' => [
					[
						'value' => '1',
						'label' => __( 'Style 1', 'classified-listing' ),
					],
				],
			],
			'listing'             => [
				'grid_style_options' => [
					[
						'value' => '1',
						'label' => __( 'Style 1', 'classified-listing' ),
					],
				],
				'list_style_options' => [
					[
						'value' => '1',
						'label' => __( 'Style 1', 'classified-listing' ),
					],
				],
			],
			'location_type'       => 'local',
			'listing_store_block' => false,
		];
		wp_localize_script( 'rtcl-gb-blocks-js', 'rtcl_block_localize_obj', apply_filters( 'rtcl_gb_localize_script', $localize_obj ) );
		wp_enqueue_style( 'gb-frontend-block-editor', rtcl()->get_assets_uri( 'css/gb-frontend-block.css' ), [], RTCL_VERSION );
		wp_enqueue_style( 'rtcl-gb-admin-block', rtcl()->get_assets_uri( 'css/gb-admin-block.css' ), [], RTCL_VERSION );
		wp_enqueue_style( 'rtcl-gb-component-style', rtcl()->get_assets_uri( 'block/style-main.css' ), [], RTCL_VERSION );

		wp_enqueue_script( 'swiper' );
		wp_enqueue_style( 'swiper' );
	}

	public function admin_assets() {
		wp_enqueue_style( 'rtcl-admin', rtcl()->get_assets_uri( 'css/rtcl-admin.min.css' ), [], RTCL_VERSION );
		wp_enqueue_style( 'rtcl-public', rtcl()->get_assets_uri( 'css/rtcl-public.min.css' ), [], RTCL_VERSION );
	}

	public function rtcl_gb_block_category( $categories ) {
		$gb_category = [
			'slug'  => 'classified-list-gb',
			'title' => __( 'Classified Listing', 'classified-listing' ),
		];

		$modifiedCategory   = [];
		$modifiedCategory   = apply_filters( 'rtcl_block_category_lists', $modifiedCategory );
		$modifiedCategory[] = $gb_category;
		$modifiedCategory   = array_merge( $modifiedCategory, $categories );

		return $modifiedCategory;
	}

	public function register_meta() {
		register_meta(
			'post',
			'_rtcl_gb_attr',
			[
				'show_in_rest'  => true,
				'single'        => true,
				'auth_callback' => [ $this, 'auth_callback' ],
			],
		);
	}

	public function auth_callback() {
		return current_user_can( 'edit_posts' );
	}
}
