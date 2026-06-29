<?php

namespace Rtcl\Services\Importers;

use Rtcl\Helpers\Functions;
use Rtcl\Interfaces\AIServiceInterface;
use WP_Error;

/**
 * Wraps the plugin's configured AI provider for two import-time tasks:
 *
 *   1. suggest()     — given source field labels + target form-field labels,
 *                      return a JSON object mapping source keys to target keys.
 *                      Runs once per "Auto-suggest with AI" click. Cheap.
 *
 *   2. enrich_row()  — given a normalized row + the list of empty target form
 *                      fields, ask the LLM to derive values for those fields
 *                      from the available source data. One call per row when
 *                      per-record enrichment is enabled. Expensive.
 *
 * # Why the sentinel-wrapper trick?
 *
 * The shared AI clients (Claude / OpenAI / Gemini / DeepSeek) all run the LLM
 * response through `json_decode` and, on success, reshape it for "plain text"
 * output — destroying the mapping object we asked for. We work around that by
 * telling the LLM to wrap its JSON between `<<<MAP_BEGIN>>>` / `<<<MAP_END>>>`
 * markers. The wrapped string is not valid JSON, so the client's post-
 * processing falls through and returns the raw text untouched, and this class
 * extracts the JSON between the markers itself.
 */
class AiMappingAssistant {

	const BEGIN_MARK = '<<<MAP_BEGIN>>>';
	const END_MARK   = '<<<MAP_END>>>';

	/**
	 * @return AIServiceInterface|null
	 */
	private function service() {
		try {
			$service = rtcl()->factory->initializeAIService();
		} catch ( \Throwable $e ) {
			return null;
		}
		return $service instanceof AIServiceInterface ? $service : null;
	}

	/**
	 * Whether an AI provider is configured and ready.
	 */
	public function is_available(): bool {
		return Functions::is_ai_enabled() && null !== $this->service();
	}

	/**
	 * Ask the LLM to suggest a source → target mapping.
	 *
	 * @param array $source_fields [{key, label, kind}, …]  (from FieldCatalog::describe_source)
	 * @param array $form_fields   [{key, label, element}, …] (from FormFieldAdapter::for_form)
	 *
	 * @return array|WP_Error  source_key => target_key on success, WP_Error with reason on failure.
	 */
	public function suggest( array $source_fields, array $form_fields ) {
		$service = $this->service();
		if ( null === $service ) {
			return new WP_Error( 'rtcl_ai_unavailable', __( 'No AI provider is configured.', 'classified-listing' ) );
		}

		$system = 'You map import source fields to a target form\'s fields. ' .
			'Output ONE JSON object: keys are source field keys (strings); ' .
			'values are target field keys (strings). ' .
			'If no good target exists for a source key, set its value to "__skip". ' .
			'Built-in target keys are __title, __content, __excerpt, __categories, __locations, __gallery, __tags. ' .
			'You must wrap the JSON between these exact markers on their own lines: ' .
			self::BEGIN_MARK . ' then the JSON then ' . self::END_MARK . '. ' .
			'Do not add any other text outside the markers.';

		$user = wp_json_encode( [
			'instructions'  => 'Return one mapping object covering every source field. Wrap output in ' . self::BEGIN_MARK . ' / ' . self::END_MARK . ' markers.',
			'source_fields' => $source_fields,
			'form_fields'   => $form_fields,
		] );

		return $this->call_and_decode( $service, (string) $user, $system, 'suggest' );
	}

	/**
	 * Ask the LLM to fill empty target form fields for a single record.
	 *
	 * @param array $source_summary
	 * @param array $empty_targets  [{key, label, element}, …]
	 *
	 * @return array|WP_Error  target_key => value on success.
	 */
	public function enrich_row( array $source_summary, array $empty_targets ) {
		if ( empty( $empty_targets ) ) {
			return [];
		}

		$service = $this->service();
		if ( null === $service ) {
			return new WP_Error( 'rtcl_ai_unavailable', __( 'No AI provider is configured.', 'classified-listing' ) );
		}

		$system = 'You produce missing field values for a directory listing using a record from an external source. ' .
			'Output ONE JSON object whose keys are the supplied target field keys and whose values are short strings. ' .
			'Omit any field you cannot fill confidently — do not invent facts. ' .
			'Wrap the JSON between these exact markers on their own lines: ' .
			self::BEGIN_MARK . ' then JSON then ' . self::END_MARK . '. ' .
			'Do not add any other text outside the markers.';

		$user = wp_json_encode( [
			'source'        => $source_summary,
			'fields_needed' => $empty_targets,
		] );

		return $this->call_and_decode( $service, (string) $user, $system, 'enrich' );
	}

	/**
	 * Ask the LLM to write a short business description from the available
	 * source data (used when the mapped description target ends up empty).
	 *
	 * Plain-text response — no JSON, no markers — because the AI clients in
	 * this plugin pass non-JSON responses through untouched.
	 *
	 * @param array $source_summary  Flat key=>value source data (name, address, types, …).
	 *
	 * @return string|WP_Error  Description string on success.
	 */
	public function generate_description( array $source_summary ) {
		$service = $this->service();
		if ( null === $service ) {
			return new WP_Error( 'rtcl_ai_unavailable', __( 'No AI provider is configured.', 'classified-listing' ) );
		}

		$system = 'Write a concise 2-3 sentence business description from the data below. ' .
			'Plain text only — no markdown, no quotation marks, no labels like "Description:", no preamble. ' .
			'Do not invent specific facts (hours, prices, awards) that are not in the input. ' .
			'If the input lacks enough detail for a real description, reply with the single word: NONE';

		$user = 'Business data: ' . wp_json_encode( $source_summary );

		try {
			$raw = trim( (string) $service->generateResponse( $user, $system ) );
		} catch ( \Throwable $e ) {
			return new WP_Error( 'rtcl_ai_exception', $e->getMessage() );
		}

		if ( '' === $raw ) {
			return new WP_Error( 'rtcl_ai_empty', __( 'AI returned an empty description.', 'classified-listing' ) );
		}
		if ( 0 === stripos( $raw, 'Error:' ) || 'No response from AI.' === $raw ) {
			$this->maybe_log( 'description', $raw );
			return new WP_Error( 'rtcl_ai_provider_error', $raw );
		}
		if ( 'NONE' === strtoupper( $raw ) ) {
			return ''; // honest "I can't fill this" — caller leaves the field empty.
		}

		// Strip surrounding quotes if the model wrapped its answer.
		$raw = trim( $raw, "\"' \t\n\r\0\x0B" );
		return $raw;
	}

	/**
	 * Shared call → parse → error-reporting helper.
	 *
	 * @return array|WP_Error
	 */
	private function call_and_decode( AIServiceInterface $service, string $user, string $system, string $context ) {
		try {
			$raw = (string) $service->generateResponse( $user, $system );
		} catch ( \Throwable $e ) {
			return new WP_Error( 'rtcl_ai_exception', $e->getMessage() );
		}

		if ( '' === trim( $raw ) ) {
			return new WP_Error( 'rtcl_ai_empty', __( 'AI returned an empty response.', 'classified-listing' ) );
		}

		// AI clients in this plugin signal failures by prefixing 'Error:' / 'No response from AI.'.
		if ( 0 === strpos( ltrim( $raw ), 'Error:' ) || 'No response from AI.' === trim( $raw ) ) {
			$this->maybe_log( $context, $raw );
			return new WP_Error( 'rtcl_ai_provider_error', trim( $raw ) );
		}

		$json = $this->extract_json( $raw );
		if ( null === $json ) {
			$this->maybe_log( $context, $raw );
			return new WP_Error(
				'rtcl_ai_unparseable',
				sprintf(
					/* translators: %s: truncated raw response */
					__( 'AI returned an unparseable response: %s', 'classified-listing' ),
					self::truncate( $raw, 200 )
				)
			);
		}

		return $json;
	}

	/**
	 * Extract a JSON object from the LLM response. Tries, in order:
	 *   1. content between BEGIN_MARK / END_MARK,
	 *   2. content inside a ```json … ``` fence,
	 *   3. the first balanced {…} block in the string.
	 *
	 * Returns the decoded associative array of scalar values, or null.
	 */
	private function extract_json( string $raw ): ?array {
		$candidate = '';

		$b = strpos( $raw, self::BEGIN_MARK );
		$e = strrpos( $raw, self::END_MARK );
		if ( false !== $b && false !== $e && $e > $b ) {
			$candidate = substr( $raw, $b + strlen( self::BEGIN_MARK ), $e - $b - strlen( self::BEGIN_MARK ) );
		}

		if ( '' === trim( $candidate ) ) {
			$stripped = preg_replace( '/^```(?:json)?\s*|\s*```$/m', '', trim( $raw ) );
			if ( is_string( $stripped ) && '' !== trim( $stripped ) ) {
				$candidate = $stripped;
			}
		}

		$candidate = trim( $candidate );

		if ( '' === $candidate || '{' !== ( $candidate[0] ?? '' ) ) {
			// Last-chance regex grab.
			if ( preg_match( '/\{(?:[^{}]|(?R))*\}/s', $candidate ?: $raw, $m ) ) {
				$candidate = $m[0];
			}
		}

		if ( '' === $candidate ) {
			return null;
		}

		$decoded = json_decode( $candidate, true );
		if ( ! is_array( $decoded ) ) {
			return null;
		}

		$out = [];
		foreach ( $decoded as $k => $v ) {
			$k = is_string( $k ) ? trim( $k ) : '';
			if ( '' === $k ) {
				continue;
			}
			if ( is_scalar( $v ) ) {
				$out[ $k ] = (string) $v;
			}
		}
		return $out;
	}

	/**
	 * Best-effort log of the raw AI response so admins can debug with
	 * WP_DEBUG_LOG. No-op when debug logging is off.
	 */
	private function maybe_log( string $context, string $raw ): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( '[rtcl import-ai] ' . $context . ' raw: ' . self::truncate( $raw, 1000 ) );
		}
	}

	private static function truncate( string $s, int $max ): string {
		$s = trim( $s );
		if ( strlen( $s ) <= $max ) {
			return $s;
		}
		return substr( $s, 0, $max ) . '…';
	}
}
