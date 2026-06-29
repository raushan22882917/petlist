<?php

namespace Rtcl\Services\AIServices\AIClients;

use Rtcl\Helpers\Functions;

/**
 * Class ClaudeClient
 *
 * This class integrates Anthropic Claude's Messages API for generating AI-driven responses.
 */
class ClaudeClient {
	/**
	 * @var string Model to be used for AI responses.
	 */
	protected $model = '';

	protected $token = '200';

	/**
	 * Anthropic Messages API endpoint.
	 *
	 * @var string
	 */
	protected $endpoint = 'https://api.anthropic.com/v1/messages';

	/**
	 * Anthropic API version pinned for the Messages endpoint.
	 *
	 * @var string
	 */
	protected $api_version = '2023-06-01';

	/**
	 * ClaudeClient constructor.
	 *
	 * Initializes the Claude client and sets API key and model from settings.
	 *
	 * @throws \Exception If required settings are missing.
	 */
	public function __construct() {
		$apiKey      = Functions::get_option_item( 'rtcl_ai_settings', 'claude_api_key' );
		$this->model = Functions::get_option_item( 'rtcl_ai_settings', 'claude_models' );
		$this->token = Functions::get_option_item( 'rtcl_ai_settings', 'claude_max_token' );
		if ( empty( $apiKey ) || empty( $this->model ) ) {
			throw new \Exception( 'AI settings are not properly configured.' );
		}
	}

	/**
	 * Generates a response from the AI model based on the given prompt.
	 *
	 * @param string $prompt The input text for the AI to respond to.
	 * @param string $system_prompt The system prompt to guide the AI's response.
	 *
	 * @return string The AI-generated response or an error message.
	 */
	public function ask( string $prompt, string $system_prompt ) {
		$apiKey = Functions::get_option_item( 'rtcl_ai_settings', 'claude_api_key' );

		if ( empty( $apiKey ) ) {
			return 'Error: Claude API key is missing.';
		}

		$response = wp_remote_post( $this->endpoint, [
			'headers' => [
				'Content-Type'      => 'application/json',
				'x-api-key'         => $apiKey,
				'anthropic-version' => $this->api_version,
			],
			'body'    => wp_json_encode( [
				'model'      => $this->model,
				'max_tokens' => 500,
				'system'     => $system_prompt . 'give me plain text no markdown and no html',
				'messages'   => [
					[ 'role' => 'user', 'content' => $prompt ],
				],
			] ),
			'timeout' => 100000,
		] );

		if ( is_wp_error( $response ) ) {
			return 'Error: ' . $response->get_error_message();
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['error'] ) ) {
			return 'Error: ' . ( $data['error']['message'] ?? 'Unknown error from Claude API.' );
		}

		$content = $this->extract_text( $data );
		if ( $content === null ) {
			return 'No response from AI.';
		}

		$jsonData = json_decode( $content, true );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			if ( is_array( $jsonData ) ) {
				return implode( "\n", array_map( function ( $item ) {
					return '- ' . $item;
				}, $jsonData ) );
			}
			return wp_json_encode( $jsonData, JSON_PRETTY_PRINT );
		}

		return $content;
	}

	public function askKeyword( $data ) {
		return $this->callClaudeForKeyword( $data );
	}

	public function askFormField( $data ) {
		$theme  = Functions::get_current_theme();
		$data   = json_decode( $data, true );
		$prompt = $data['prompt'] ?? '';
		return $this->callClaudeForField( $prompt, $theme );
	}

	/**
	 * Calls the Claude API to generate a response based on the provided prompt and instruction.
	 *
	 * @param string $prompt The user’s prompt to send to the model.
	 * @param string $instruction The instruction to provide context for the model.
	 * @param float  $temperature The temperature setting for randomness in the response. Default is 0.7.
	 * @param string $model The Claude model to use.
	 * @param string $for Either 'keyword' or 'form'.
	 *
	 * @return false|string|void The cleaned and formatted JSON response from the API.
	 */
	public function callClaude( $prompt, $instruction, $temperature = 0.7, $model = null, $for = 'keyword' ) {
		$apiKey = Functions::get_option_item( 'rtcl_ai_settings', 'claude_api_key' );

		$response = wp_remote_post( $this->endpoint, [
			'headers' => [
				'Content-Type'      => 'application/json',
				'x-api-key'         => $apiKey,
				'anthropic-version' => $this->api_version,
			],
			'body'    => wp_json_encode( [
				'model'      => $this->model,
				'max_tokens' => 4096,
				'system'     => $instruction,
				'messages'   => [
					[ 'role' => 'user', 'content' => $prompt ],
				],
			] ),
			'timeout' => 100000,
		] );

		if ( is_wp_error( $response ) ) {
			return 'Error: ' . $response->get_error_message();
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! empty( $data['error'] ) ) {
			return $data;
		}

		$aiResponse    = $this->extract_text( $data ) ?? '[]';
		$cleanResponse = trim( $aiResponse, "```json\n \t\r\0\x0B" );

		if ( $for == 'keyword' ) {
			return wp_send_json_success( [ 'data' => json_decode( $cleanResponse, true ) ] );
		}

		return wp_send_json_success( [ 'response' => json_decode( $cleanResponse, true ) ] );
	}

	/**
	 * Calls the Claude API to generate form field suggestions based on the provided prompt.
	 *
	 * @param string $prompt The user’s prompt describing the form or fields to be suggested.
	 * @param string $theme The active theme stylesheet (used to pick a theme-specific system prompt).
	 *
	 * @return bool|string A JSON-encoded string containing the form field suggestions.
	 */
	public function callClaudeForField( string $prompt, $theme ) {
		$systemPrompts = [
			"cl-classified" => "You are a helpful assistant that suggests form fields for General Classified listings (Classima). Provide a JSON array of form field suggestions. Each object should have 'label', 'type', 'placeholder', 'section', and 'required' properties. The 'type' should be one of: address, business_hours, category, checkbox, color_picker, custom_html, date, description, email, excerpt, file, images, input_hidden, location, map, number, phone, pricing, radio, recaptcha, repeater, select, social_profiles, switch,  text, textarea, title, url, view_count, website, whatsapp, zipcode, terms_and_condition. Title type must be provided and For profession-based directories (e.g., Doctor, Lawyer), the label should be the profession name (e.g., Doctor Name, Lawyer Name) and the type should be 'title'.If description field the type should be 'description'. If the type is select, checkbox, or radio, options should be an array of values. Provide at least 20 fields across 3 sections.If the type is select, checkbox, or radio, options should be an array of values. Try to Terms and Conditions in the last .Provide at least 20 fields across 3 sections. Keep the answer short and only return the JSON format",

			"classima" => "You are a helpful assistant that suggests form fields for General Classified listings (Classima). Provide a JSON array of form field suggestions. Each object should have 'label', 'type', 'placeholder', 'section', and 'required' properties. The 'type' should be one of: address, business_hours, category, checkbox, color_picker, custom_html, date, description, email, excerpt, file, images, input_hidden, location, map, number, phone, pricing, radio, recaptcha, repeater, select, social_profiles, switch, text, textarea, title, url, view_count, website, whatsapp, zipcode, terms_and_condition. Title type must be provided and For profession-based directories (e.g., Doctor, Lawyer), the label should be the profession name (e.g., Doctor Name, Lawyer Name) and the type should be 'title'.If description field the type should be 'description'. If the type is select, checkbox, or radio, options should be an array of values. Provide at least 20 fields across 3 sections. If the type is select, checkbox, or radio, options should be an array of values.Try to Terms and Conditions in the last. Provide at least 20 fields across 3 sections. Keep the answer short and only return the JSON format",

			"homlisti" => "You are a helpful assistant that suggests form fields for Real Estate listings (Homlisti). Provide a JSON array of form field suggestions. Each object should have 'label', 'type', 'placeholder', 'section', and 'required' properties. The 'type' should be one of: address, business_hours, category, checkbox, color_picker, custom_html, date, description, email, excerpt, file, images, input_hidden, location, map, number, phone, pricing, radio, recaptcha, repeater, select, social_profiles, switch, text, textarea, title, url, view_count, website, whatsapp, zipcode, terms_and_condition. Include real estate-specific fields such as Property Features (checkbox), Parking (Yes/No), Bedrooms, Bathrooms, Property Size, Build Year, and Proposed Sale Type (Sell/Buy/Rent). If the type is select, checkbox, or radio, options should be an array of values.Try to Terms and Conditions in the last.If description field the type should be 'description'. Provide at least 20 fields across 3 sections. Keep the answer short and only return the JSON format.",

			"hotel_directory" => "You are a helpful assistant that suggests form fields for Hotel Directory listings. Provide a JSON array of form field suggestions. Each object should have 'label', 'type', 'placeholder', 'section', and 'required' properties. The 'type' should be one of: address, business_hours, category, checkbox, color_picker, custom_html, date, description, email, excerpt, file, images, input_hidden, location, map, number, phone, pricing, radio, recaptcha, repeater, select, social_profiles, switch, text, textarea, title, url, view_count, website, whatsapp, zipcode, terms_and_condition. Include hotel-specific fields such as Amenities (checkbox), Opening Hours, and Instant Booking. If the type is select, checkbox, or radio, options should be an array of values.Try to Terms and Conditions in the last.If description field the type should be 'description'. Provide at least 20 fields across 3 sections. Keep the answer short and only return the JSON format.",
		];

		$defaultPrompt = "You are a helpful assistant that suggests form fields based on user prompts. Provide a JSON array of form field suggestions. Each object should have 'label', 'type', 'placeholder','section', and 'required' properties. The 'type' should be one of: address, business_hours, category, checkbox, color_picker, custom_html, date, description, email, excerpt, file, images, input_hidden, location, map, number, phone, pricing, radio, recaptcha, repeater, select, social_profiles, switch, text, textarea, title, url, view_count, website, whatsapp, zipcode, terms_and_condition. Title type must be provided and For profession-based directories (e.g., Doctor, Lawyer), the label should be the profession name (e.g., Doctor Name, Lawyer Name) and the type should be 'title'.If description field the type should be 'description'.If the type is select, checkbox, or radio, options should be an array of values. Provide at least 20 fields across 3 sections. Keep the answer short and only return the JSON format.";

		$systemPrompt = $systemPrompts[ $theme ] ?? $defaultPrompt;
		return $this->callClaude( $prompt, $systemPrompt, 0.7, $this->model, 'form' );
	}

	/**
	 * Calls the Claude API to generate relevant keywords based on the provided prompt.
	 *
	 * @param string $prompt The user’s prompt describing the context for keyword generation.
	 *
	 * @return bool|string A JSON-encoded array of 10 relevant keywords.
	 */
	public function callClaudeForKeyword( $prompt ) {
		$data   = json_decode( $prompt, true );
		$prompt = $data['prompt'] ?? '';
		return $this->callClaude(
			$prompt,
			"You are an AI that generates relevant keywords based on user prompts. Return only a JSON array of keywords without any extra text. Provide exactly 10 keywords."
		);
	}

	/**
	 * Extract the assistant text from a Claude Messages API response.
	 *
	 * Anthropic returns `content` as an array of blocks; we concatenate every
	 * `text`-typed block so multi-block responses are not silently truncated.
	 *
	 * @param array|null $data Decoded response body.
	 *
	 * @return string|null
	 */
	private function extract_text( $data ) {
		if ( empty( $data['content'] ) || ! is_array( $data['content'] ) ) {
			return null;
		}
		$out = '';
		foreach ( $data['content'] as $block ) {
			if ( isset( $block['type'], $block['text'] ) && $block['type'] === 'text' ) {
				$out .= $block['text'];
			}
		}
		return $out !== '' ? $out : null;
	}
}
