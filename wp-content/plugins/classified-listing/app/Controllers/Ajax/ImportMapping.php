<?php

namespace Rtcl\Controllers\Ajax;

use Rtcl\Models\Form\Form;
use Rtcl\Services\Importers\AiMappingAssistant;
use Rtcl\Services\Importers\FieldCatalog;
use Rtcl\Services\Importers\FieldMapper;
use Rtcl\Services\Importers\FormFieldAdapter;
use Rtcl\Services\Importers\GooglePlacesImporter;
use Rtcl\Services\Importers\MappingRepository;

/**
 * Mapping-UI AJAX endpoints. Used by the Google Places tab to:
 *
 *   - rtcl_import_form_fields       fetch the mappable target fields of a form
 *   - rtcl_import_mapping_get       load the saved mapping for (source, form)
 *   - rtcl_import_mapping_save      persist a mapping
 *   - rtcl_import_mapping_suggest   AI-driven mapping suggestion
 *   - rtcl_import_preview_row       apply a mapping to one Google place_id and
 *                                   return what the listing would look like
 *
 * RSS and CSV do not use these endpoints (they still use their own UIs).
 */
class ImportMapping {

	public function __construct() {
		add_action( 'wp_ajax_rtcl_import_form_fields',     [ $this, 'form_fields' ] );
		add_action( 'wp_ajax_rtcl_import_mapping_get',     [ $this, 'mapping_get' ] );
		add_action( 'wp_ajax_rtcl_import_mapping_save',    [ $this, 'mapping_save' ] );
		add_action( 'wp_ajax_rtcl_import_mapping_reset',   [ $this, 'mapping_reset' ] );
		add_action( 'wp_ajax_rtcl_import_mapping_suggest', [ $this, 'mapping_suggest' ] );
		add_action( 'wp_ajax_rtcl_import_preview_row',     [ $this, 'preview_row' ] );
	}

	public function form_fields(): void {
		$this->guard();

		$form_id = (int) ( $_POST['form_id'] ?? 0 );
		$form    = $form_id ? Form::query()->find( $form_id ) : null;
		if ( ! $form ) {
			wp_send_json_error( [ 'message' => __( 'Form not found.', 'classified-listing' ) ] );
		}

		wp_send_json_success( [
			'form_id' => (int) $form->id,
			'title'   => (string) ( $form->title ?? '' ),
			'fields'  => FormFieldAdapter::for_form( $form ),
		] );
	}

	public function mapping_get(): void {
		$this->guard();

		$source_type = $this->sanitize_source_type( wp_unslash( $_POST['source_type'] ?? '' ) );
		$form_id     = (int) ( $_POST['form_id'] ?? 0 );

		wp_send_json_success( [
			'mapping' => MappingRepository::get( $source_type, $form_id ),
			'source_fields' => FieldCatalog::describe_source( $source_type ),
		] );
	}

	public function mapping_save(): void {
		$this->guard();

		$source_type = $this->sanitize_source_type( wp_unslash( $_POST['source_type'] ?? '' ) );
		$form_id     = (int) ( $_POST['form_id'] ?? 0 );
		$raw         = $_POST['mapping'] ?? [];

		if ( ! is_array( $raw ) ) {
			$raw = [];
		}

		// Sanitize map keys/values (sanitize_text_field is fine — these are
		// short identifiers and meta keys, never user prose).
		$map = [];
		foreach ( $raw as $k => $v ) {
			$k = sanitize_text_field( wp_unslash( (string) $k ) );
			$v = sanitize_text_field( wp_unslash( (string) $v ) );
			if ( '' !== $k && '' !== $v ) {
				$map[ $k ] = $v;
			}
		}

		MappingRepository::save( $source_type, $form_id, $map );

		wp_send_json_success( [ 'message' => __( 'Mapping saved.', 'classified-listing' ) ] );
	}

	/**
	 * Wipe the saved mapping for (source, form). The UI clears its dropdowns
	 * locally so the admin can rebuild the mapping from scratch.
	 */
	public function mapping_reset(): void {
		$this->guard();

		$source_type = $this->sanitize_source_type( wp_unslash( $_POST['source_type'] ?? '' ) );
		$form_id     = (int) ( $_POST['form_id'] ?? 0 );

		MappingRepository::delete( $source_type, $form_id );

		wp_send_json_success( [ 'message' => __( 'Mapping reset.', 'classified-listing' ) ] );
	}

	public function mapping_suggest(): void {
		$this->guard();

		$source_type = $this->sanitize_source_type( wp_unslash( $_POST['source_type'] ?? '' ) );
		$form_id     = (int) ( $_POST['form_id'] ?? 0 );
		$form        = $form_id ? Form::query()->find( $form_id ) : null;
		if ( ! $form ) {
			wp_send_json_error( [ 'message' => __( 'Form not found.', 'classified-listing' ) ] );
		}

		$ai = new AiMappingAssistant();
		if ( ! $ai->is_available() ) {
			wp_send_json_error( [ 'message' => __( 'No AI provider is configured under Settings → AI Integration.', 'classified-listing' ) ] );
		}

		$suggestion = $ai->suggest(
			FieldCatalog::describe_source( $source_type ),
			FormFieldAdapter::for_form( $form )
		);

		if ( is_wp_error( $suggestion ) ) {
			wp_send_json_error( [ 'message' => $suggestion->get_error_message() ] );
		}
		if ( ! is_array( $suggestion ) || empty( $suggestion ) ) {
			wp_send_json_error( [ 'message' => __( 'AI returned no mappings. Try again or map fields manually.', 'classified-listing' ) ] );
		}

		wp_send_json_success( [ 'mapping' => $suggestion ] );
	}

	/**
	 * Fetch one Google place, apply the mapping currently being edited, and
	 * return a preview of how the listing would look.
	 */
	public function preview_row(): void {
		$this->guard();

		$source_type = $this->sanitize_source_type( wp_unslash( $_POST['source_type'] ?? '' ) );
		if ( 'google_places' !== $source_type ) {
			wp_send_json_error( [ 'message' => __( 'Preview is only available for Google Places.', 'classified-listing' ) ] );
		}

		$place_id = sanitize_text_field( wp_unslash( $_POST['place_id'] ?? '' ) );
		if ( '' === $place_id ) {
			wp_send_json_error( [ 'message' => __( 'Missing place_id.', 'classified-listing' ) ] );
		}

		$raw_mapping = $_POST['mapping'] ?? [];
		$mapping     = [];
		if ( is_array( $raw_mapping ) ) {
			foreach ( $raw_mapping as $k => $v ) {
				$k = sanitize_text_field( wp_unslash( (string) $k ) );
				$v = sanitize_text_field( wp_unslash( (string) $v ) );
				if ( '' !== $k && '' !== $v ) {
					$mapping[ $k ] = $v;
				}
			}
		}

		$form_id = (int) ( $_POST['form_id'] ?? 0 );
		$form    = $form_id ? Form::query()->find( $form_id ) : null;

		$importer = new GooglePlacesImporter();
		$rows     = $importer->fetch( [ 'place_ids' => [ $place_id ], 'max_photos' => 3 ] );
		if ( is_wp_error( $rows ) || empty( $rows[0] ) ) {
			$msg = is_wp_error( $rows ) ? $rows->get_error_message() : __( 'No data returned for that place.', 'classified-listing' );
			wp_send_json_error( [ 'message' => $msg ] );
		}

		$row = $rows[0];
		if ( ! empty( $row['_error'] ) ) {
			wp_send_json_error( [ 'message' => $row['_error'] ] );
		}

		$mapped = ( new FieldMapper() )->apply( $row, $mapping, $source_type, $form );

		wp_send_json_success( [
			'source'  => [
				'name'    => $row['title']    ?? '',
				'address' => $row['meta']['_rtcl_geo_address'] ?? '',
				'phone'   => $row['meta']['_rtcl_phone']        ?? '',
				'website' => $row['meta']['_rtcl_website']      ?? '',
				'image'   => $row['gallery_urls'][0] ?? '',
			],
			'mapped'  => [
				'title'        => $mapped['title']        ?? '',
				'content'      => $mapped['content']      ?? '',
				'excerpt'      => $mapped['excerpt']      ?? '',
				'categories'   => $mapped['categories']   ?? [],
				'locations'    => $mapped['locations']    ?? [],
				'tags'         => $mapped['tags']         ?? [],
				'gallery_urls' => $mapped['gallery_urls'] ?? [],
				'meta'         => $mapped['meta']         ?? [],
			],
		] );
	}

	private function guard(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'classified-listing' ) ], 403 );
		}
		$nonce = $_POST[ rtcl()->nonceId ] ?? '';
		if ( ! wp_verify_nonce( $nonce, rtcl()->nonceText ) ) {
			wp_send_json_error( [ 'message' => __( 'Session expired.', 'classified-listing' ) ], 403 );
		}
	}

	private function sanitize_source_type( $value ): string {
		$value = is_string( $value ) ? sanitize_key( $value ) : '';
		// Only Google is wired in Phase 5; other types are accepted for future
		// reuse but currently route through their own UIs.
		return in_array( $value, [ 'google_places', 'rss' ], true ) ? $value : 'google_places';
	}
}
