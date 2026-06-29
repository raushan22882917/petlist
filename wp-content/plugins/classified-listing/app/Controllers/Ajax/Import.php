<?php

namespace Rtcl\Controllers\Ajax;

use Rtcl\Controllers\Hooks\Filters;
use Rtcl\Helpers\Functions;
use Rtcl\Services\Importers\ImportHistory;
use Rtcl\Services\Importers\ListingIngester;

class Import {

	function __construct() {
		add_action( 'wp_ajax_rtcl_import_location', [ $this, 'rtcl_import_location' ] );
		add_action( 'wp_ajax_rtcl_import_category', [ $this, 'rtcl_import_category' ] );
		add_action( 'wp_ajax_rtcl_import_settings', [ $this, 'rtcl_import_settings' ] );
		add_action( 'wp_ajax_rtcl_import_ad_types', [ $this, 'rtcl_import_ad_types' ] );
		add_action( 'wp_ajax_rtcl_import_listings', [ $this, 'rtcl_import_listings' ] );
		add_action( 'wp_ajax_rtcl_import_process_listing_data', [ $this, 'process_listing_data' ] );
	}

	/**
	 * @throws \Exception
	 */
	public function process_listing_data() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json(
				[
					'success' => false,
					'message' => esc_html__( 'Unauthorized access!!!', 'classified-listing' ),
				],
			);
		}

		if ( ! wp_verify_nonce( $_POST[ rtcl()->nonceId ] ?? '', rtcl()->nonceText ) ) {
			wp_send_json(
				[
					'success' => false,
					'message' => esc_html__( 'Session Expired!!', 'classified-listing' ),
				],
			);
		}


		$return = [
			'success' => false,
			'message' => esc_html__( 'Something wrong. Not added any listing!!', 'classified-listing' ),
		];

		$raw_rows = $_POST['rows'] ?? null;
		$rows     = is_string( $raw_rows ) ? json_decode( wp_unslash( $raw_rows ), true ) : $raw_rows;
		parse_str( $_POST['formData'] ?? '', $formData );
		$map_to = $formData['map_to'] ?? null;

		if ( empty( $rows ) || ! is_array( $rows ) ) {
			$return['message'] = esc_html__( 'Not found listings!', 'classified-listing' );
			wp_send_json( $return );
		}

		if ( empty( $map_to ) || ! is_array( $map_to ) ) {
			$return['message'] = esc_html__( 'Please, assign data field for listings!', 'classified-listing' );
			wp_send_json( $return );
		}

		$inserted_posts = [];
		$errors         = [];
		$row_number     = 0;
		$ingester       = new ListingIngester();
		$run_id         = ImportHistory::start_run( 'csv', '', [ 'row_count' => count( $rows ) ] );

		foreach ( $rows as $row ) {
			$row_number++;
			$result = $ingester->ingest_csv_row( $row, $map_to, $row_number );

			if ( ! empty( $result['errors'] ) ) {
				$errors = array_merge( $errors, $result['errors'] );
			}

			if ( ! empty( $result['post_id'] ) ) {
				$inserted_posts[] = $result['post_id'];
			}
		}

		$total_rows = $row_number;
		$success_count = count( $inserted_posts );
		$error_count   = count( $errors );

		if ( $run_id ) {
			ImportHistory::finish_run(
				$run_id,
				[
					'imported' => $success_count,
					'updated'  => 0,
					'skipped'  => max( 0, $total_rows - $success_count ),
					'total'    => $total_rows,
				],
				$errors
			);
		}

		if ( $success_count > 0 && $error_count > 0 ) {
			$return['success'] = true;
			/* translators: 1: Success count, 2: Total rows, 3: Error count */
			$return['message'] = sprintf(
				__( 'Imported %1$d of %2$d listings. %3$d failed.', 'classified-listing' ),
				$success_count,
				$total_rows,
				$error_count
			);
			$return['errors'] = $errors;
		} elseif ( $success_count > 0 ) {
			$return['success'] = true;
			/* translators: %d: Number of posts */
			$return['message'] = sprintf( __( 'Successfully imported %d listings.', 'classified-listing' ), $success_count );
		} elseif ( $error_count > 0 ) {
			$return['message'] = __( 'Failed to import any listings.', 'classified-listing' );
			$return['errors']  = $errors;
		}

		wp_send_json( $return );
	}

	public function rtcl_import_category() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json(
				[
					'success' => false,
					'message' => esc_html__( 'Unauthorized access!!!', 'classified-listing' ),
				],
			);
		}

		if ( ! wp_verify_nonce( $_POST[ rtcl()->nonceId ] ?? '', rtcl()->nonceText ) ) {
			wp_send_json(
				[
					'success' => false,
					'data'    => null,
					'message' => esc_html__( 'Session Expired!!', 'classified-listing' ),
				],
			);

			return;
		}

		$data   = $_REQUEST['data'];
		$return = Functions::create_term( rtcl()->category, $data );
		wp_send_json( $return );
	}

	public function rtcl_import_location() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json(
				[
					'success' => false,
					'message' => esc_html__( 'Unauthorized access!!!', 'classified-listing' ),
				],
			);
		}

		if ( ! wp_verify_nonce( $_POST[ rtcl()->nonceId ] ?? '', rtcl()->nonceText ) ) {
			wp_send_json(
				[
					'success' => false,
					'data'    => null,
					'message' => esc_html__( 'Session Expired!!', 'classified-listing' ),
				],
			);

			return;
		}

		$data   = $_REQUEST['data'];
		$return = Functions::create_term( rtcl()->location, $data );
		wp_send_json( $return );
	}

	public function rtcl_import_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json(
				[
					'success' => false,
					'message' => esc_html__( 'Unauthorized access!!!', 'classified-listing' ),
				],
			);
		}

		if ( ! wp_verify_nonce( $_POST[ rtcl()->nonceId ] ?? '', rtcl()->nonceText ) ) {
			wp_send_json(
				[
					'success' => false,
					'data'    => null,
					'message' => esc_html__( 'Session Expired!!', 'classified-listing' ),
				],
			);
		}

		$data   = $_REQUEST['data'];
		$return = $this->update_settings( $data );
		wp_send_json( $return );
	}

	public function rtcl_import_ad_types() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json(
				[
					'success' => false,
					'message' => esc_html__( 'Unauthorized access!!!', 'classified-listing' ),
				],
			);
		}

		if ( ! wp_verify_nonce( $_POST[ rtcl()->nonceId ] ?? '', rtcl()->nonceText ) ) {
			wp_send_json(
				[
					'success' => false,
					'data'    => null,
					'message' => esc_html__( 'Session Expired!!', 'classified-listing' ),
				],
			);
		}

		$data = $_REQUEST['data'];

		$return = [
			'success' => false,
			'data'    => null,
			'message' => '',
		];

		$title      = $data['value'] ?? '';
		$get_option = get_option( 'rtcl_listing_types' );
		if ( ! $get_option ) {
			$get_option = Functions::get_listing_types();
		}

		if ( is_array( $get_option ) && array_key_exists( $data['key'], $get_option ) ) {
			$return['success'] = 'exist';
			$return['data']    = '';
			/* translators: %s: Title. */
			$return['message'] = sprintf( __( '%s is already exist!', 'classified-listing' ), $title );
		} else {
			$get_option[ $data['key'] ] = $title;
			$update                     = update_option( 'rtcl_listing_types', $get_option );
			if ( $update ) {
				$return['success'] = true;
				/* translators: %s: Title. */
				$return['message'] = sprintf( __( '%s Successfully Created', 'classified-listing' ), $title );
			} else {
				$return['message'] = __( 'Error!!! in ', 'classified-listing' ) . $title;
			}
		}

		wp_send_json( $return );
	}

	private function update_settings( $data ) {
		$return = [
			'success' => false,
			'data'    => null,
			'message' => '',
		];

		$key = $data['key'];

		if ( ! empty( $key ) ) {
			$defaults = get_option( $key, [] );
			if ( ! empty( $defaults ) ) {
				$args = wp_parse_args( $data['value'], $defaults );
				update_option( $key, $args );
				$return['success'] = true;
				/* translators: %s: Key. */
				$return['message'] = sprintf( __( '%s Successfully Created', 'classified-listing' ), $key );
			}
		}

		wp_send_json( $return );
	}

	public function rtcl_import_listings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json(
				[
					'success' => false,
					'message' => esc_html__( 'Unauthorized access!!!', 'classified-listing' ),
				],
			);
		}

		if ( ! wp_verify_nonce( $_POST[ rtcl()->nonceId ] ?? '', rtcl()->nonceText ) ) {
			wp_send_json(
				[
					'success' => false,
					'data'    => null,
					'message' => esc_html__( 'Session Expired!!', 'classified-listing' ),
				],
			);
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$results = [
			'success' => false,
			'data'    => null,
			'message' => '',
		];

		$file = $_FILES['file'] ?? [];
		$rows = [];
		if ( ! empty( $file ) ) {
			Filters::beforeUpload();
			$status = wp_handle_upload(
				$file,
				[
					'test_form' => false,
				],
			);
			Filters::afterUpload();
			if ( $status && ! isset( $status['error'] ) ) {
				$filename = $status['file'];
				$filetype = wp_check_filetype( basename( $filename ) );
				if ( is_file( $filename ) ) {
					$results['file_url'] = $filename;

					$fopen = fopen( $filename, 'r' );

					while ( ( $data = fgetcsv( $fopen, 0, ',' ) ) !== false ) {
						$rows[] = $data;
					}

					$row_count = count( $rows );

					if ( $row_count > apply_filters( 'rtcl_import_listings_limit', 101 ) ) {
						$results['message'] = sprintf(
						/* translators: %s: $row_count. */
							esc_html__(
								'Please, add maximum 100 listings in one file. You added %s listings!!',
								'classified-listing',
							),
							$row_count - 1,
						);
					} else {
						$title_row          = $row_count > 1 ? array_shift( $rows ) : [];
						$results['rawData'] = $rows;
						if ( ! empty( $title_row ) ) {
							$results['success'] = true;
							ob_start();
							?>
							<form class="rtcl-listings-import-mapping-form" id="rtcl-listings-import-mapping-form"
								  name="rtcl-listings-import-mapping-form"
								  method="post">
								<header class="rtcl-ie-card-head">
									<div>
										<h2><?php esc_html_e( 'Map CSV fields to listings', 'classified-listing' ); ?></h2>
										<p>
											<?php
											esc_html_e(
												'Select fields from your CSV file to map against listings fields, or to ignore during import.',
												'classified-listing',
											);
											?>
										</p>
									</div>
								</header>
								<div class="rtcl-importer-mapping-table-wrapper">
									<table class="rtcl-importer-mapping-table">
										<thead>
										<tr>
											<th><?php esc_html_e( 'Column name', 'classified-listing' ); ?></th>
											<th><?php esc_html_e( 'Map to field', 'classified-listing' ); ?></th>
										</tr>
										</thead>
										<tbody>
										<?php
										foreach ( $title_row as $index => $title ) {
											?>
											<tr>
												<td><?php echo esc_html( $title ); ?></td>
												<td>
													<select class="rtcl_map_to"
															name="map_to[<?php echo esc_attr( $index ); ?>]">
														<option value=""><?php esc_html_e( 'Do not import', 'classified-listing' ); ?></option>
														<?php
														$fields = $this->get_listing_import_fields();
														foreach ( $fields as $key => $field ) {
															?>
															<option
																value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field ); ?></option>
															<?php
														}
														?>
													</select>
												</td>
											</tr>
											<?php
										}
										?>
										</tbody>
									</table>
									<button type="submit" id="rtcl_listings_import_submit" class="rtcl-ie-btn rtcl-ie-btn-primary">
										<?php esc_html_e( 'Continue', 'classified-listing' ); ?>
									</button>
								</div>
							</form>
							<?php
							$results['data'] = ob_get_clean();
						} else {
							$results['message'] = esc_html__( 'Please, add at least one listings!!', 'classified-listing' );
						}
					}
					fclose( $fopen );
					unlink( $filename );
				} else {
					$results['message'] = esc_html__( 'File does not exist!!', 'classified-listing' );
				}
			} else {
				$results['message'] = esc_html__( 'Error in file upload!!', 'classified-listing' );
			}
		} else {
			$results['message'] = esc_html__( 'File not found!!', 'classified-listing' );
		}

		wp_send_json( $results );
	}

	private function get_listing_import_fields() {
		return array_merge( Functions::get_listings_default_fields(), Functions::get_listings_custom_fields() );
	}
}