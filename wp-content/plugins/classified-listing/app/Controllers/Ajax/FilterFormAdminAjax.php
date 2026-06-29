<?php

namespace Rtcl\Controllers\Ajax;

use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;
use Rtcl\Traits\SingletonTrait;

class FilterFormAdminAjax {
	use SingletonTrait;

	public function init() {
		add_action( 'wp_ajax_rtcl_admin_settings_filter_update_name', [ $this, 'create_update_filter' ] );
		add_action( 'wp_ajax_rtcl_admin_settings_filter_remove', [ $this, 'remove_filter' ] );
		add_action( 'wp_ajax_rtcl_admin_settings_filter_update_item', [ $this, 'update_filter_item' ] );
		add_action( 'wp_ajax_rtcl_admin_settings_filter_remove_item', [ $this, 'remove_filter_item' ] );
		add_action( 'wp_ajax_rtcl_admin_settings_filter_update_items_order', [ $this, 'update_filter_items_order' ] );
		// add_action( 'wp_ajax_rtcl_admin_settings_filter', [ $this, 'update_filters' ] );
	}

	public function create_update_filter() {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session error !!', 'classified-listing' ) );
		}
		if ( !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission to manage filter settings.', 'classified-listing' ) );
		}
		$filterId = !empty( $_POST['filterId'] ) ? sanitize_text_field( wp_unslash( $_POST['filterId'] ) ) : '';
		$name = !empty( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$id = !empty( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		if ( empty( $name ) ) {
			wp_send_json_error( esc_html__( 'Empty filter name !!!', 'classified-listing' ) );
		}
		$filters = Functions::get_option( 'rtcl_filter_settings' );
		if ( $filterId ) {
			if ( empty( $filters[$filterId] ) ) {
				wp_send_json_error( esc_html__( 'No form is found to update.', 'classified-listing' ) );
			}
			$action = 'update';
			$filters[$filterId]['name'] = $name;
		} else {
			$id = Functions::slugify( !empty( $id ) ? $id : $name );
			if ( empty( $id ) ) {
				wp_send_json_error( esc_html__( 'Form slug/id can not be empty', 'classified-listing' ) );
			}

			if ( !empty( $filters[$id] ) ) {
				wp_send_json_error( esc_html__( 'Form slug/id already exist, please try different one', 'classified-listing' ) );
			}

			$action = 'new';
			$filterId = $id;
			$filters[$filterId] = [
				'name'  => $name,
				'items' => []
			];
		}

		update_option( 'rtcl_filter_settings', $filters );
		wp_send_json_success( [
			'message'  => esc_html__( 'Filter created !!', 'classified-listing' ),
			'filterId' => $filterId,
			'filters'  => $filters,
			'action'   => $action
		] );
	}

	public function remove_filter() {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session error !!', 'classified-listing' ) );
		}
		if ( !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission to remove filter forms.', 'classified-listing' ) );
		}

		$filterId = !empty( $_POST['filterId'] ) ? sanitize_text_field( wp_unslash( $_POST['filterId'] ) ) : '';
		$filters = Functions::get_option( 'rtcl_filter_settings' );
		if ( !$filterId || empty( $filters[$filterId] ) ) {
			wp_send_json_error( esc_html__( 'No filter form found to remove !!!', 'classified-listing' ) );

			return;
		}
		unset( $filters[$filterId] );
		$filters = empty( $filters ) ? [] : $filters;
		update_option( 'rtcl_filter_settings', $filters );
		wp_send_json_success( [
			'message'  => esc_html__( 'Filter form removed !!', 'classified-listing' ),
			'filterId' => $filterId,
			'filters'  => $filters,
		] );
	}

	public function update_filter_item() {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session error !!', 'classified-listing' ) );
		}
		if ( !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission to update filter items.', 'classified-listing' ) );
		}

		$filterId = !empty( $_POST['filterId'] ) ? sanitize_text_field( wp_unslash( $_POST['filterId'] ) ) : '';
		$itemId = !empty( $_POST['itemId'] ) ? sanitize_text_field( wp_unslash( $_POST['itemId'] ) ) : '';
		$data = !empty( $_POST['data'] ) && is_array( $_POST['data'] ) ? $_POST['data'] : '';
		$filters = Functions::get_option( 'rtcl_filter_settings' );
		if ( !$filterId || empty( $filters[$filterId] ) ) {
			wp_send_json_error( esc_html__( 'No filter form found to remove !!!', 'classified-listing' ) );

			return;
		}
		$allItems = Options::filterFormItems();
		if ( !$itemId || empty( $allItems[$itemId] ) ) {
			wp_send_json_error( esc_html__( 'No filter item found to update !!!', 'classified-listing' ) );

			return;
		}

		$_item = $allItems[$itemId];
		$itemData = [ 'id' => $itemId ];
		if ( !empty( $_item['fields'] ) ) {
			foreach ( $_item['fields'] as $_field ) {
				if ( !empty( $_field['id'] ) && !empty( $data[$_field['id']] ) ) {
					if ( $_field['type'] === 'switch' ) {
						$itemData[$_field['id']] = 1;
					} elseif ( $_field['type'] === 'select' ) {
						if ( in_array( $data[$_field['id']], array_keys( $_field['options'] ) ) ) {
							$itemData[$_field['id']] = !empty( $_field['validation'] ) && is_callable( $_field['validation'] ) ? $_field['validation']( $data[$_field['id']] ) : $data[$_field['id']];
						}
					} elseif ( 'checkbox' === $_field['type'] ) {
						if ( is_array( $data[$_field['id']] ) ) {
							$itemValues = $data[$_field['id']];
							if ( !empty( $_field['validation'] ) && is_callable( $_field['validation'] ) ) {
								$itemValues = array_map( $_field['validation'], $data[$_field['id']] );
							}
							$itemData[$_field['id']] = is_array( $_field['options'] ) ? array_values( array_intersect( $itemValues, array_keys( $_field['options'] ) ) ) : [];
						}
					} elseif ( $_field['type'] === 'cf_fields_order' ) {
						$ids = [];
						if ( is_array( $data[$_field['id']] ) ) {
							$ids = array_filter( array_map( function ( $_id ) {
								return sanitize_text_field( wp_unslash( $_id ) );
							}, $data[$_field['id']] ) );
							if ( empty( $ids ) ) {

							}
						}
						$itemData[$_field['id']] = empty( $ids ) ? '' : $ids;
					} else {
						$itemData[$_field['id']] = !empty( $_field['validation'] ) && is_callable( $_field['validation'] ) ? $_field['validation']( $data[$_field['id']] ) : sanitize_text_field( wp_unslash( $data[$_field['id']] ) );
					}
				}
			}
		}

		$exist = false;
		if ( !empty( $filters[$filterId]['items'] ) ) {
			foreach ( $filters[$filterId]['items'] as $key => $item ) {
				if ( $item['id'] === $itemId ) {
					$filters[$filterId]['items'][$key] = $itemData;
					$exist = true;
					break;
				}
			}
			if ( !$exist ) {
				$filters[$filterId]['items'][] = $itemData;
			}
		} else {
			$filters[$filterId]['items'][] = $itemData;
		}

		update_option( 'rtcl_filter_settings', $filters );

		wp_send_json_success( [
			'message' => esc_html__( 'Filter item updated !!', 'classified-listing' ),
			'filters' => $filters,
		] );
	}

	public function update_filter_items_order() {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session error !!', 'classified-listing' ) );
		}
		if ( !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission to reorder filter items.', 'classified-listing' ) );
		}

		$filterId = !empty( $_POST['filterId'] ) ? sanitize_text_field( wp_unslash( $_POST['filterId'] ) ) : '';
		$rawItemKeys = !empty( $_POST['itemKeys'] ) && is_array( $_POST['itemKeys'] ) ? $_POST['itemKeys'] : [];
		$filters = Functions::get_option( 'rtcl_filter_settings' );

		if ( !$filterId || empty( $filters[$filterId] ) ) {
			wp_send_json_error( esc_html__( 'No filter form found to remove.', 'classified-listing' ) );

			return;
		}

		if ( empty( $rawItemKeys ) || empty( $filters[$filterId]['items'] ) ) {
			wp_send_json_error( esc_html__( 'Item key list is empty.', 'classified-listing' ) );

			return;
		}

		$allFields = Options::filterFormItems();
		$items = [];
		$tempItems = [];
		foreach ( $rawItemKeys as $rawItemKey ) {
			if ( is_string( $rawItemKey ) && !empty( $allFields[$rawItemKey] ) ) {
				foreach ( $filters[$filterId]['items'] as $key => $item ) {
					if ( !in_array( $item, $tempItems ) && $item['id'] === $rawItemKey ) {
						$tempItems[] = $rawItemKey;
						$items[] = $item;
						break;
					}
				}
			}
		}

		if ( empty( $items ) ) {
			wp_send_json_error( esc_html__( 'No validated field found to sort !!!', 'classified-listing' ) );

			return;
		}

		$filters[$filterId]['items'] = $items;

		update_option( 'rtcl_filter_settings', $filters );

		wp_send_json_success( [
			'message' => esc_html__( 'Filter item sorted !!', 'classified-listing' ),
			'filters' => $filters,
		] );
	}

	public function remove_filter_item() {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session error !!', 'classified-listing' ) );
		}
		if ( !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission to remove filter items.', 'classified-listing' ) );
		}

		$filterId = !empty( $_POST['filterId'] ) ? sanitize_text_field( wp_unslash( $_POST['filterId'] ) ) : '';
		$itemId = !empty( $_POST['itemId'] ) ? sanitize_text_field( wp_unslash( $_POST['itemId'] ) ) : '';
		$filters = Functions::get_option( 'rtcl_filter_settings' );
		if ( !$filterId || empty( $filters[$filterId] ) ) {
			wp_send_json_error( esc_html__( 'No filter form found to remove !!!', 'classified-listing' ) );

			return;
		}

		$allFields = Options::filterFormItems();
		if ( !$itemId || empty( $allFields[$itemId] ) ) {
			wp_send_json_error( esc_html__( 'No filter item found to remove !!!', 'classified-listing' ) );

			return;
		}

		if ( !empty( $filters[$filterId]['items'] ) ) {
			$items = [];
			foreach ( $filters[$filterId]['items'] as $item ) {
				if ( $item['id'] !== $itemId ) {
					$items[] = $item;
				}
			}
			$filters[$filterId]['items'] = $items;
			update_option( 'rtcl_filter_settings', $filters );
		}

		wp_send_json_success( [
			'message'  => esc_html__( 'Filter item form removed !!', 'classified-listing' ),
			'filterId' => $filterId,
			'filters'  => $filters,
		] );
	}
}