<?php

namespace Rtcl\Controllers\Hooks;


use Rtcl\Helpers\Cache;
use Rtcl\Helpers\Functions;

class AdminHooks {

	public static function init() {
		add_action( "rtcl_sent_email_to_user_by_moderator", [
			__CLASS__,
			'update_user_notification_by_moderator'
		], 10 );
		add_action( "rtcl_sent_email_to_user_by_visitor", [ __CLASS__, 'update_user_notification_by_visitor' ], 10 );
		add_action( 'update_option_rtcl_general_settings', [
			__CLASS__,
			'update_taxonomy_cache_at_taxonomy_order_change'
		], 10, 2 );
		add_filter( 'quick_edit_show_taxonomy', [ __CLASS__, 'listing_remove_taxonomy_from_quick_edit' ], 10, 3 );
		add_action( 'parse_request', [ __CLASS__, 'listing_payment_search_by_id' ] );
		add_action( 'wp_ajax_rtcl_tax_country_state', [ __CLASS__, 'load_country_state' ] );
		add_action( 'wp_ajax_rtcl_tax_remove_record', [ __CLASS__, 'remove_tax_record' ] );
		add_action( 'in_admin_header', [ __CLASS__, 'remove_all_notices' ], 1000 );
		add_action( 'admin_notices', [ __CLASS__, 'cl_toolkits_addon_notice' ] );
		add_action( 'wp_ajax_cl_toolkits_dismiss_notice', [ __CLASS__, 'cl_toolkit_dismiss_notice' ] );

	}

	public static function cl_toolkits_addon_notice() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// Don't show again if dismissed by this user
		if ( get_user_meta( get_current_user_id(), '_cl_toolkits_addon_notice_dismissed', true ) ) {
			return;
		}

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		$plugin_slug = 'classified-listing-toolkits';
		$plugin_file = 'classified-listing-toolkits/classified-listing-toolkits.php';

		$is_divi_active      = defined( 'ET_CORE_VERSION' ) || is_plugin_active( 'divi-builder/divi-builder.php' );
		$is_elementor_active = is_plugin_active( 'elementor/elementor.php' );

		if ( ! $is_divi_active && ! $is_elementor_active ) {
			return;
		}

		$installed_plugins = get_plugins();
		$is_installed      = isset( $installed_plugins[ $plugin_file ] );
		$is_active         = is_plugin_active( $plugin_file );

		$plugin_name        = 'Classified Listing Toolkits';
		$plugin_description = 'This addon is required to enable integration with Elementor widgets, and Divi modules in <strong>Classified Listing</strong>.';

		if ( ! $is_installed || ! $is_active ) {
			$action_url = $is_installed
				? wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . $plugin_file ), 'activate-plugin_' . $plugin_file )
				: wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin_slug ), 'install-plugin_' . $plugin_slug );

			$button_label = $is_installed ? 'Activate Now' : 'Install Now';

			printf(
				'<div class="notice notice-warning is-dismissible cl-toolkits-addon-notice"><div style="display: flex; align-items: center; gap: 12px;">
				<div>
					<p><strong>%1$s</strong> addon %2$s.</p>
					<p>%3$s</p>
					<p><a href="%4$s" class="button button-primary">%5$s</a></p>
				</div>
			</div></div>',
				esc_html( $plugin_name ),
				$is_installed ? 'is installed but not active' : 'is not installed',
				esc_html( $plugin_description ),
				esc_url( $action_url ),
				esc_html( $button_label )
			);

			add_action( 'admin_footer', [ __CLASS__, 'cl_toolkits_addon_notice_js' ] );
		}
	}


	public static function cl_toolkits_addon_notice_js() {
		?>
		<script type="text/javascript">
			jQuery(function ($) {
				var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>'; // fallback definition

				$(document).on('click', '.cl-toolkits-addon-notice .notice-dismiss', function () {
					$.post(ajaxurl, {
						action: 'cl_toolkits_dismiss_notice',
						nonce: '<?php echo esc_js( wp_create_nonce( 'cl_toolkits_dismiss_nonce' ) ); ?>'
					})
						.done(function () {
							console.log('Dismiss saved');
						})
						.fail(function (xhr) {
							console.error('AJAX failed:', xhr.responseText);
						});
				});
			});
		</script>
		<?php
	}


	public static function cl_toolkit_dismiss_notice() {
		check_ajax_referer( 'cl_toolkits_dismiss_nonce', 'nonce' );

		update_user_meta( get_current_user_id(), '_cl_toolkits_addon_notice_dismissed', 1 );

		wp_send_json_success();
	}


	public static function remove_tax_record() {


		if ( !current_user_can( 'manage_options' ) || !Functions::verify_nonce() ) {
			wp_send_json( [
				'error' => true,
				'msg'   => __( 'Unauthorized access!!', 'classified-listing' )
			] );
		}
		
		global $wpdb;

		$error      = true;
		$table_name = $wpdb->prefix . 'rtcl_tax_rates';
		$ids        = isset( $_POST['data'] ) && is_array( $_POST['data'] ) ? $_POST['data'] : [];
		$ids        = array_values( array_unique( array_map( 'absint', $ids ) ) );
		$ids        = array_filter( $ids );

		if ( ! empty( $ids ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
			if ( $wpdb->query( $wpdb->prepare( "DELETE FROM `{$table_name}` WHERE tax_rate_id IN ($placeholders)", ...$ids ) ) ) {
				$error = false;
			}
		}

		wp_send_json( [
			'error' => $error,
		] );
	}

	public static function load_country_state() {

		if ( !current_user_can( 'manage_options' ) || !Functions::verify_nonce() ) {
			wp_send_json( [
				'error' => true,
				'msg'   => __( 'Unauthorized access!!', 'classified-listing' )
			] );
		}
		
		$country_code = isset( $_POST['country_code'] ) ? sanitize_text_field( $_POST['country_code'] ) : '';

		if ( ! empty( $country_code ) ) {
			$states = rtcl()->countries->get_states( $country_code );

			wp_send_json( [
				'error' => false,
				'data'  => $states
			] );
		}

		wp_send_json( [
			'error' => true,
			'msg'   => __( 'Country states not found!', 'classified-listing' )
		] );
	}


	/**
	 * Remove admin notices
	 */
	public static function remove_all_notices() {
		$screen = get_current_screen();

		if ( isset( $screen->base ) && ( 'classified-listing_page_rtcl-settings' == $screen->base ) || 'classified-listing_page_rtcl-fb' == $screen->base
			 || 'toplevel_page_rtcl-admin' == $screen->base
			 || 'classified-listing_page_rtcl-ajax-filter' == $screen->base
			 || 'classified-listing_page_rtcl-import-export' == $screen->base
			 || 'classified-listing_page_rtcl-extension' == $screen->base
			 || 'classified-listing_page_rtcl-setup-wizard' == $screen->base
		) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}

	public static function listing_remove_taxonomy_from_quick_edit( $show_in_quick_edit, $taxonomy_name, $post_type ) {
		if ( rtcl()->post_type === $post_type && in_array( $taxonomy_name, [ rtcl()->location, rtcl()->category ] ) ) {
			return false;
		}

		return $show_in_quick_edit;
	}

	public static function update_user_notification_by_moderator( $post_id ) {
		$count = absint( get_post_meta( $post_id, "notification_by_moderation", true ) );

		update_post_meta( $post_id, 'notification_by_moderation', $count + 1 );
	}

	public static function update_user_notification_by_visitor( $post_id ) {

		$count = absint( get_post_meta( $post_id, "notification_by_visitor", true ) );

		update_post_meta( $post_id, 'notification_by_visitor', $count + 1 );

	}

	public static function update_taxonomy_cache_at_taxonomy_order_change( $old_options, $new_options ) {
		if ( ( isset( $old_options['taxonomy_orderby'] ) && isset( $new_options['taxonomy_orderby'] )
			   && ( $old_options['taxonomy_orderby'] !== $new_options['taxonomy_orderby'] ) )
			 || ( isset( $old_options['taxonomy_order'] ) && isset( $new_options['taxonomy_order'] )
				  && ( $old_options['taxonomy_order'] !== $new_options['taxonomy_order'] ) )
		) {
			Cache::remove_all_taxonomy_cache();
		}
	}

	public static function listing_payment_search_by_id( $wp ) {
		global $pagenow;
		if ( ! is_admin() && 'edit.php' != $pagenow
			 && ( 'rtcl_listing' !== $_GET['post_type']
				  || 'rtcl_payment' !== $_GET['post_type'] )
		) { /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
			return;
		}

		if ( ! isset( $wp->query_vars['s'] ) ) {
			return;
		}

		$post_id = absint( $wp->query_vars['s'] );
		if ( ! $post_id ) {
			return;
		}

		unset( $wp->query_vars['s'] );
		$wp->query_vars['p'] = $post_id;
	}

}