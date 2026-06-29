<?php

namespace Rtcl\Services\Importers;

use Rtcl\Helpers\Functions;
use Rtcl\Models\Form\Form;
use Rtcl\Services\FormBuilder\FBHelper;

/**
 * Orchestrates a single import run.
 *
 * Two entry points:
 *   run( int $source_id )                            for saved sources (RSS).
 *   run_with_importer( $importer, $params, $opts )   for ad-hoc runs (Google
 *                                                    Places search-then-import).
 *
 * Both funnel through process_rows(), which:
 *   - opens a history row,
 *   - calls importer->fetch,
 *   - loops normalized rows through the ingester (capping at max_per_run),
 *   - records counters + errors back into rtcl_import_history.
 */
class ImportRunner {

	/** @var ListingIngester */
	private $ingester;

	/** @var DedupeResolver */
	private $dedupe;

	public function __construct( ?ListingIngester $ingester = null, ?DedupeResolver $dedupe = null ) {
		$this->ingester = $ingester ?: new ListingIngester();
		$this->dedupe   = $dedupe ?: new DedupeResolver();
	}

	/**
	 * Run the importer associated with a saved source row.
	 *
	 * @param int $source_id Row id in rtcl_import_sources.
	 *
	 * @return array { run_id: int|null, imported: int, updated: int, skipped: int, errors: string[] }
	 */
	public function run( int $source_id ): array {
		$source = SourceRepository::find( $source_id );
		if ( ! $source ) {
			return self::empty_result( [ __( 'Import source not found.', 'classified-listing' ) ] );
		}

		$importer = $this->build_importer( (string) $source->source_type );
		if ( ! $importer ) {
			return self::empty_result( [ sprintf(
				/* translators: %s: source type slug */
				__( 'Unknown import source type: %s', 'classified-listing' ),
				$source->source_type
			) ] );
		}

		$max_per_run    = self::max_per_run();
		$default_status = self::default_status();

		$params = apply_filters(
			'rtcl_import_run_params',
			[
				'url'   => (string) $source->url,
				'limit' => $max_per_run,
			],
			$source,
			$importer
		);

		$opts = [
			'update_existing' => (bool) $source->update_existing,
			'target_category' => (int) $source->target_category,
			'target_location' => (int) $source->target_location,
			'default_status'  => $source->target_status ?: $default_status,
			'dedupe'          => $this->dedupe,
		];

		$result = $this->process_rows(
			$importer,
			$params,
			$opts,
			(string) $source->source_type,
			(string) $source->url,
			[ 'source_id' => $source_id ]
		);

		SourceRepository::touch_run( $source_id );
		return $result;
	}

	/**
	 * Ad-hoc entry: caller supplies the importer + params, we orchestrate the
	 * same fetch → ingest → history flow without touching rtcl_import_sources.
	 *
	 * @param ImporterInterface $importer
	 * @param array             $params      Passed to importer->fetch.
	 * @param array             $opts        Merged into ingester opts (target_*, update_existing, default_status, …).
	 * @param string            $source_key  Free-form key recorded in history (URL, search query, …).
	 * @param array             $params_log  Extra params persisted in history.params (json).
	 *
	 * @return array
	 */
	public function run_with_importer(
		ImporterInterface $importer,
		array $params,
		array $opts = [],
		string $source_key = '',
		array $params_log = []
	): array {
		$opts = wp_parse_args( $opts, [
			'update_existing'    => false,
			'target_category'    => 0,
			'target_location'    => 0,
			'default_status'     => self::default_status(),
			'dedupe'             => $this->dedupe,
			'form_id'            => 0,
			'mapping'            => [],
			'enrich'             => false,
			'enrich_cap'         => 10,
			'enrich_description' => false,
			// Featured image strategy: 'google' (use source photos),
			// 'fallback' (replace with default_fallback_image_url),
			// 'none' (strip images entirely — no featured, no gallery).
			'image_source'       => 'google',
			'fallback_image_url' => '',
		] );

		return $this->process_rows(
			$importer,
			$params,
			$opts,
			$importer->get_source_key(),
			$source_key,
			$params_log
		);
	}

	/**
	 * Single-shot entry: open a history run, fetch everything, process (capped
	 * at max_per_run), close the run. Used by the RSS / inline paths. The
	 * batched Google path uses run_google_batch() instead.
	 */
	private function process_rows(
		ImporterInterface $importer,
		array $params,
		array $opts,
		string $source_type,
		string $source_key,
		array $params_log
	): array {
		$max_per_run = self::max_per_run();
		$run_id      = ImportHistory::start_run( $source_type, $source_key, $params_log );

		$counters = [ 'imported' => 0, 'updated' => 0, 'skipped' => 0, 'total' => 0 ];
		$errors   = [];

		$rows = $importer->fetch( $params );
		if ( is_wp_error( $rows ) ) {
			$errors[] = $rows->get_error_message();
			if ( $run_id ) {
				ImportHistory::finish_run( $run_id, $counters, $errors, ImportHistory::STATUS_FAILED );
			}
			return array_merge( $counters, [ 'run_id' => $run_id ?: null, 'errors' => $errors ] );
		}

		$result = $this->process_normalized_rows( $rows, $opts, $source_type, $max_per_run );

		if ( $run_id ) {
			ImportHistory::finish_run( $run_id, $result, $result['errors'] );
		}

		return array_merge(
			[ 'imported' => $result['imported'], 'updated' => $result['updated'], 'skipped' => $result['skipped'], 'total' => $result['total'] ],
			[ 'run_id' => $run_id ?: null, 'errors' => $result['errors'] ]
		);
	}

	/**
	 * Process one batch of a larger, already-open run and fold its counts into
	 * that shared history row. Does NOT open or finalize the run — the caller
	 * (ImportScheduler) owns the run lifecycle so progress accumulates across
	 * every Action Scheduler batch.
	 *
	 * @param ImporterInterface $importer
	 * @param array             $params      Slice params passed to importer->fetch.
	 * @param array             $opts
	 * @param string            $source_type
	 * @param int               $run_id      Shared rtcl_import_history row id.
	 *
	 * @return array { imported, updated, skipped, total, errors }
	 */
	public function run_google_batch(
		ImporterInterface $importer,
		array $params,
		array $opts,
		string $source_type,
		int $run_id
	): array {
		$opts = wp_parse_args( $opts, [
			'update_existing'    => false,
			'target_category'    => 0,
			'target_location'    => 0,
			'default_status'     => self::default_status(),
			'dedupe'             => $this->dedupe,
			'form_id'            => 0,
			'mapping'            => [],
			'enrich'             => false,
			'enrich_cap'         => 10,
			'enrich_description' => false,
			'image_source'       => 'google',
			'fallback_image_url' => '',
		] );

		$rows = $importer->fetch( $params );
		if ( is_wp_error( $rows ) ) {
			// Whole-slice failure — count the slice as skipped so progress still
			// advances toward the known total, and record why.
			$slice_size = isset( $params['place_ids'] ) && is_array( $params['place_ids'] )
				? count( $params['place_ids'] )
				: ( isset( $params['preview_rows'] ) && is_array( $params['preview_rows'] ) ? count( $params['preview_rows'] ) : 0 );
			$errors = [ $rows->get_error_message() ];
			ImportHistory::bump_counts( $run_id, [ 'skipped' => $slice_size ], $errors );
			return [ 'imported' => 0, 'updated' => 0, 'skipped' => $slice_size, 'total' => $slice_size, 'errors' => $errors ];
		}

		// No cap here — the slice was already sized to max_per_run by the scheduler.
		$result = $this->process_normalized_rows( $rows, $opts, $source_type, null );

		ImportHistory::bump_counts(
			$run_id,
			[ 'imported' => $result['imported'], 'updated' => $result['updated'], 'skipped' => $result['skipped'] ],
			$result['errors']
		);

		return $result;
	}

	/**
	 * Run normalized rows through mapping → enrichment → ingest and tally the
	 * outcome. Shared by the single-shot and batched entry points.
	 *
	 * @param array       $rows        NormalizedRow[].
	 * @param array       $opts
	 * @param string      $source_type
	 * @param int|null    $max         Hard cap on rows processed, or null for no cap.
	 *
	 * @return array { imported, updated, skipped, total, errors }
	 */
	private function process_normalized_rows( array $rows, array $opts, string $source_type, ?int $max = null ): array {
		$counters = [ 'imported' => 0, 'updated' => 0, 'skipped' => 0, 'total' => 0 ];
		$errors   = [];

		// Phase 5: form-aware mapping + optional AI enrichment.
		$form        = $this->resolve_form( (int) ( $opts['form_id'] ?? 0 ) );
		$mapping     = is_array( $opts['mapping'] ?? null ) ? $opts['mapping'] : [];
		$mapper      = ( $form && ! empty( $mapping ) ) ? new FieldMapper() : null;
		$form_fields = $form ? FormFieldAdapter::for_form( $form ) : [];

		$enrich_on          = ! empty( $opts['enrich'] ) && $form && $mapper;
		$enrich_desc_on     = ! empty( $opts['enrich_description'] ) && $form && $mapper;
		$enrich_cap         = max( 0, (int) ( $opts['enrich_cap'] ?? 10 ) );
		$enrich_calls       = 0;
		$ai_assistant       = ( $enrich_on || $enrich_desc_on ) ? new AiMappingAssistant() : null;
		if ( $ai_assistant && ! $ai_assistant->is_available() ) {
			$ai_assistant   = null;
			$enrich_on      = false;
			$enrich_desc_on = false;
			$errors[]       = __( 'AI enrichment was requested but no AI provider is configured; falling back to plain mapping.', 'classified-listing' );
		}

		// Whether the chosen form actually has a description field — without
		// one there is nowhere to put a generated description.
		$has_description_slot = false;
		if ( $enrich_desc_on ) {
			foreach ( $form_fields as $f ) {
				if ( FormFieldAdapter::SLOT_CONTENT === ( $f['key'] ?? '' ) ) {
					$has_description_slot = true;
					break;
				}
			}
			if ( ! $has_description_slot ) {
				$enrich_desc_on = false;
			}
		}

		$processed = 0;
		foreach ( $rows as $row ) {
			if ( null !== $max && $processed >= $max ) {
				break;
			}
			$processed++;
			$counters['total']++;

			// Per-row hard error from an importer (e.g. one place_id failed details lookup).
			if ( ! empty( $row['_error'] ) ) {
				$counters['skipped']++;
				$errors[] = ( ! empty( $row['title'] ) ? '"' . $row['title'] . '": ' : '' ) . $row['_error'];
				continue;
			}

			$original = $row;
			if ( $mapper ) {
				$row = $mapper->apply( $original, $mapping, $source_type, $form );
			}

			// Featured image strategy. Applied AFTER the mapper so it overrides
			// whatever the mapping/source produced — admin's explicit choice
			// always wins over Google's photos.
			$image_source = (string) ( $opts['image_source'] ?? 'google' );
			if ( 'none' === $image_source ) {
				$row['gallery_urls'] = [];
			} elseif ( 'fallback' === $image_source ) {
				$fallback = trim( (string) ( $opts['fallback_image_url'] ?? '' ) );
				$row['gallery_urls'] = '' !== $fallback ? [ $fallback ] : [];
			}

			// Focused: generate a description when the content slot is empty.
			// Runs BEFORE the broader enrichment so that path won't waste an
			// AI call re-asking for the description.
			if ( $enrich_desc_on && '' === trim( (string) ( $row['content'] ?? '' ) ) ) {
				$this->generate_description( $original, $row, $ai_assistant, $source_type, $errors );
			}

			if ( $enrich_on && $enrich_calls < $enrich_cap ) {
				$this->enrich_row( $original, $row, $form_fields, $ai_assistant, $source_type, $errors );
				$enrich_calls++;
			}

			// The form builder resolves a listing's fields from its assigned
			// form, so without _rtcl_form_id its fields (business hours, custom
			// fields, …) won't render. FieldMapper stamps it, but only runs when
			// there is a non-empty field mapping ($mapper is null otherwise — see
			// where it's built above). Native imports (e.g. Google Places) can
			// reach here with no mapping, so the id would never be written. Stamp
			// it from the already-resolved form whenever the admin explicitly
			// picked one, or the form builder is active.
			$explicit_form = (int) ( $opts['form_id'] ?? 0 ) > 0;
			if ( $form && ! empty( $form->id ) && ( $explicit_form || FBHelper::isEnabled() ) ) {
				if ( empty( $row['meta'] ) || ! is_array( $row['meta'] ) ) {
					$row['meta'] = [];
				}
				if ( empty( $row['meta']['_rtcl_form_id'] ) ) {
					$row['meta']['_rtcl_form_id'] = (int) $form->id;
				}
			}

			$result = $this->ingester->ingest_normalized( $row, $opts );

			if ( ! empty( $result['errors'] ) ) {
				foreach ( $result['errors'] as $err ) {
					$errors[] = ( ! empty( $row['title'] ) ? '"' . $row['title'] . '": ' : '' ) . $err;
				}
			}

			switch ( $result['action'] ?? 'skipped' ) {
				case 'inserted':
					$counters['imported']++;
					break;
				case 'updated':
					$counters['updated']++;
					break;
				default:
					$counters['skipped']++;
			}
		}

		return array_merge( $counters, [ 'errors' => $errors ] );
	}

	/**
	 * Build an importer instance for a saved-source type slug. Filterable so
	 * future sources can register here without editing this file.
	 */
	private function build_importer( string $source_type ): ?ImporterInterface {
		$importer = null;
		switch ( $source_type ) {
			case 'rss':
				$importer = new RssImporter();
				break;
			case 'google_places':
				$importer = new GooglePlacesImporter();
				break;
		}
		return apply_filters( 'rtcl_import_runner_importer', $importer, $source_type );
	}

	private static function max_per_run(): int {
		return max( 1, (int) Functions::get_option_item( 'rtcl_import_settings', 'max_per_run', 50 ) );
	}

	private static function default_status(): string {
		return (string) Functions::get_option_item( 'rtcl_import_settings', 'default_import_status', 'pending' );
	}

	/**
	 * Resolve a Form by id, or fall back to the default form when id is 0.
	 * Returns null when no usable form is available.
	 */
	private function resolve_form( int $form_id ): ?Form {
		if ( $form_id > 0 ) {
			$form = Form::query()->find( $form_id );
			return $form instanceof Form ? $form : null;
		}
		// id=0 → caller did not specify; fall back to default form.
		$default = Form::query()->where( 'default', 1 )->one();
		return $default instanceof Form ? $default : null;
	}

	/**
	 * Focused AI fill for the description slot. Builds a small source
	 * summary from the importer catalog and asks the AI for a short
	 * paragraph. Failures are best-effort — row passes through unchanged.
	 *
	 * @param array              $original The pre-mapping NormalizedRow.
	 * @param array              $row      Post-mapping row (mutated on success).
	 * @param AiMappingAssistant $ai
	 * @param string             $source_type
	 * @param array              $errors   Errors collector (mutated on AI failure).
	 */
	private function generate_description(
		array $original,
		array &$row,
		AiMappingAssistant $ai,
		string $source_type,
		array &$errors
	): void {
		$summary = [];
		foreach ( FieldCatalog::for_source( $source_type ) as $source_key => $def ) {
			$value = ( $def['extract'] )( $original );
			if ( $value !== '' && $value !== [] && null !== $value ) {
				$summary[ $source_key ] = $value;
			}
		}

		$generated = $ai->generate_description( $summary );
		if ( is_wp_error( $generated ) ) {
			$errors[] = sprintf(
				/* translators: 1: business title, 2: AI error message */
				__( 'AI description skipped for "%1$s": %2$s', 'classified-listing' ),
				$row['title'] ?? '—',
				$generated->get_error_message()
			);
			return;
		}
		$generated = trim( (string) $generated );
		if ( '' === $generated ) {
			return; // AI honestly said NONE — leave empty.
		}

		$row['content'] = $generated;
	}

	/**
	 * Compute the empty target fields on a mapped row, ask the AI to fill them,
	 * and merge any returned values back into the row in-place.
	 *
	 * Failures are best-effort: the row passes through unchanged on AI error.
	 *
	 * @param array              $original     The pre-mapping NormalizedRow.
	 * @param array              $row          The post-mapping row (mutated).
	 * @param array              $form_fields  FormFieldAdapter output.
	 * @param AiMappingAssistant $ai
	 * @param string             $source_type
	 * @param array              $errors       Errors collector (mutated).
	 */
	private function enrich_row(
		array $original,
		array &$row,
		array $form_fields,
		AiMappingAssistant $ai,
		string $source_type,
		array &$errors
	): void {
		$empty = [];
		foreach ( $form_fields as $field ) {
			$key = $field['key'];
			// Skip taxonomy + gallery — text AI can't usefully fill those.
			if ( in_array( $key, [
				FormFieldAdapter::SLOT_CATEGORIES,
				FormFieldAdapter::SLOT_LOCATIONS,
				FormFieldAdapter::SLOT_TAGS,
				FormFieldAdapter::SLOT_GALLERY,
			], true ) ) {
				continue;
			}
			if ( FormFieldAdapter::is_slot( $key ) ) {
				$slot_value = $row[ self::slot_to_row_key( $key ) ] ?? '';
				if ( is_string( $slot_value ) && '' !== trim( $slot_value ) ) {
					continue;
				}
				if ( ! is_string( $slot_value ) && ! empty( $slot_value ) ) {
					continue;
				}
			} else {
				$meta_value = $row['meta'][ $key ] ?? '';
				if ( is_string( $meta_value ) && '' !== trim( $meta_value ) ) {
					continue;
				}
				if ( ! is_string( $meta_value ) && ! empty( $meta_value ) ) {
					continue;
				}
			}
			$empty[] = $field;
		}

		if ( empty( $empty ) ) {
			return;
		}

		// Build a flat source summary from the catalog so the LLM has context.
		$summary = [];
		foreach ( FieldCatalog::for_source( $source_type ) as $source_key => $def ) {
			$value = ( $def['extract'] )( $original );
			if ( $value !== '' && $value !== [] && null !== $value ) {
				$summary[ $source_key ] = $value;
			}
		}

		$suggested = $ai->enrich_row( $summary, $empty );
		if ( is_wp_error( $suggested ) ) {
			$errors[] = sprintf(
				/* translators: %s: AI error message */
				__( 'AI enrichment skipped: %s', 'classified-listing' ),
				$suggested->get_error_message()
			);
			return;
		}
		if ( ! is_array( $suggested ) || empty( $suggested ) ) {
			return;
		}

		foreach ( $suggested as $target_key => $value ) {
			$value = (string) $value;
			if ( '' === trim( $value ) ) {
				continue;
			}
			if ( FormFieldAdapter::is_slot( $target_key ) ) {
				$row[ self::slot_to_row_key( $target_key ) ] = $value;
			} else {
				$row['meta'][ $target_key ] = $value;
			}
		}
	}

	private static function slot_to_row_key( string $slot ): string {
		switch ( $slot ) {
			case FormFieldAdapter::SLOT_TITLE:      return 'title';
			case FormFieldAdapter::SLOT_CONTENT:    return 'content';
			case FormFieldAdapter::SLOT_EXCERPT:    return 'excerpt';
			case FormFieldAdapter::SLOT_CATEGORIES: return 'categories';
			case FormFieldAdapter::SLOT_LOCATIONS:  return 'locations';
			case FormFieldAdapter::SLOT_TAGS:       return 'tags';
			case FormFieldAdapter::SLOT_GALLERY:    return 'gallery_urls';
		}
		return $slot; // unreachable for valid slots
	}

	private static function empty_result( array $errors ): array {
		return [
			'run_id'   => null,
			'imported' => 0,
			'updated'  => 0,
			'skipped'  => 0,
			'total'    => 0,
			'errors'   => $errors,
		];
	}
}
