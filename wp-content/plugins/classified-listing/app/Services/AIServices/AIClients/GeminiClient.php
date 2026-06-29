<?php

namespace Rtcl\Services\AIServices\AIClients;

use JsonException;
use Rtcl\Helpers\Functions;
use WP_Error;

/**
 * Class GeminiClient
 *
 * This class integrates Google's Gemini API for generating AI-driven responses.
 */
class GeminiClient {
	/**
	 * @var string Model to be used for AI responses.
	 */
	protected $model = 'gemini-2.5-flash'; // Default Gemini model

	protected $token = '200';

	/**
	 * GeminiClient constructor.
	 *
	 * Initializes the Gemini client and sets API key and model from settings.
	 *
	 * @throws \Exception If required settings are missing.
	 */
	public function __construct() {
		$apiKey      = Functions::get_option_item( 'rtcl_ai_settings', 'gemini_api_key' );
		$model       = Functions::get_option_item( 'rtcl_ai_settings', 'gemini_models' );
		$this->model = ! empty( $model ) ? $model : 'gemini-2.5-flash';
		$this->token = Functions::get_option_item( 'rtcl_ai_settings', 'gpt_max_token' );
		if ( empty( $apiKey ) ) {
			throw new \Exception( 'Gemini API key is not properly configured.' );
		}
	}

	/**
	 * Generates a response from the AI model based on the given prompt.
	 *
	 * @param  string  $prompt  The input text for the AI to respond to.
	 * @param  string  $system_prompt  Not applicable for Gemini API.
	 *
	 * @return string The AI-generated response or an error message.
	 */
	public function ask( string $prompt, string $system_prompt = '' ): string { //System prompt removed
		$apiKey    = Functions::get_option_item( 'rtcl_ai_settings', 'gemini_api_key' ); // Get the API Key
		$url       = 'https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent?key=' . $apiKey; // Gemini API endpoint
		$contents  = [
			[
				'role'  => 'user', // Or 'model'. Experiment if needed. User generally appropriate for instruction/context
				'parts' => [
					[
						'text' => $system_prompt . 'give me plain text no markdown and no html',
					],
				],
			],
			[
				'role'  => 'user', // User sends the actual question/prompt
				'parts' => [
					[
						'text' => $prompt,
					],
				],
			],
		];
		$body_data = [
			'contents'         => $contents,
			'generationConfig' => [ // added temperature here as well
			                        'temperature' => 0.7,
			],
		];

		$response = wp_remote_post(
			$url,
			[
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'body'    => wp_json_encode( $body_data ),  // Use wp_json_encode for WordPress compatibility
				'timeout' => 12000, // Increase timeout (seconds)
			],
		);
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			error_log( "Gemini API Error (wp_remote_post): " . $error_message );

			return "Error: " . $error_message; // Or a specific error message for the user
		} else {
			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );

			if ( 200 !== $response_code ) {
				error_log( "Gemini API Error: HTTP " . $response_code . " - " . $response_body );

				return "Error: HTTP " . $response_code . " - " . $response_body; // Or a specific error message for the user
			}

			$result = json_decode( $response_body, true );

			if ( isset( $result['error'] ) ) {
				error_log( 'Gemini API error: ' . json_encode( $result['error'] ) );

				return "Error: " . json_encode( $result['error'] );
			}
			if ( isset( $result['candidates'][0]['content']['parts'][0]['text'] ) ) {
				$content = $result['candidates'][0]['content']['parts'][0]['text'];
				// Decode and format JSON response
				$jsonData = json_decode( $content, true );
				if ( json_last_error() === JSON_ERROR_NONE ) {
					if ( is_array( $jsonData ) ) {
						return implode( "\n", array_map( function ( $item ) {
							return "- " . $item;
						}, $jsonData ) );
					} else {
						return json_encode( $jsonData, JSON_PRETTY_PRINT );
					}
				} else {
					return $content;
				}
			} else {
				error_log( 'Unexpected response format from Gemini API: ' . $response_body );

				return "Error: Unexpected response format";
			}
		}
	}

	public function askKeyword( $data ) {
		return $this->callGeminiForKeyword( $data );
	}

	public function askFormField( $data ) {
		$theme  = Functions::get_current_theme();
		$data   = json_decode( $data, true );
		$prompt = $data['prompt'] ?? '';

		return $this->callGeminiForField( $prompt, $theme );
	}

	/**
	 * Calls the Gemini API to generate a response based on the provided prompt and instruction.
	 *
	 * @param  string  $prompt  The user’s prompt to send to the model.
	 * @param  string  $instruction  The instruction to provide context for the model.
	 * @param  float  $temperature  The temperature setting for randomness in the response. Default is 0.7.
	 * @param  string  $model  The OpenAI model to use, default is 'gemini-pro'.
	 *
	 * @return false|string The cleaned and formatted JSON response from the API.
	 */
	public function callGemini( $prompt, $instruction, $temperature = 0.7, $model = null, $for = 'keyword' ) {
		$apiKey = Functions::get_option_item( 'rtcl_ai_settings', 'gemini_api_key' );
		$url    = 'https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent?key=' . $apiKey;
		// Construct the contents array with the instruction as context.  Always ensure instruction goes before prompt
		$contents = [
			[
				'role'  => 'user', // Or 'model'. Experiment if needed. User generally appropriate for instruction/context
				'parts' => [
					[
						'text' => $instruction,
					],
				],
			],
			[
				'role'  => 'user', // User sends the actual question/prompt
				'parts' => [
					[
						'text' => $prompt,
					],
				],
			],
		];

		$body_data = [
			'contents'         => $contents,
			'generationConfig' => [ // added temperature here as well
			                        'temperature' => $temperature,
			],
		];

		$response = wp_remote_post(
			$url,
			[
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'body'    => wp_json_encode( $body_data ),  // Use wp_json_encode for WordPress compatibility
				'timeout' => 12000, // Increase timeout (seconds)
			],
		);

		if ( is_wp_error( $response ) ) {
			return 'Error: ' . $response->get_error_message();
		}

		// Parse the response body
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		if ( ! empty( $data['error'] ) ) {
			return $data;
		}

		$aiResponse    = $data['candidates'][0]['content']['parts'][0]['text'] ?? '[]';
		$cleanResponse = trim( $aiResponse, "```json\n \t\r\0\x0B" );


		try {
			$decodedResponse = json_decode( $cleanResponse, true, 512, JSON_THROW_ON_ERROR ); // throws exception
		} catch ( JsonException $e ) {
			error_log( "JSON Decode Error: " . $e->getMessage() . " - Raw Response: " . $cleanResponse );

			return 'Error: Invalid JSON response';
		}


		if ( $for == 'keyword' ) {
			return wp_send_json_success( [ 'data' => $decodedResponse ] );
		}

		// Return a JSON response with the cleaned and formatted content
		return wp_send_json_success( [ 'response' => $decodedResponse ] );
	}

	/**
	 * Generate embedding via Google Gemini Embedding API
	 *
	 * @param  string  $text
	 *
	 * @return false|array Embedding vector (array of floats) or false on failure
	 */
	public function generateEmbedding( string $text ) {
		// Get Google API key from your settings
		$apiKey = Functions::get_option_item( 'rtcl_ai_settings', 'gemini_api_key' );

		// Set model resource name — adjust according to your key / project config
		$modelName = 'models/gemini-embedding-001';

		// Build the endpoint URL
		$url = 'https://generativelanguage.googleapis.com/v1beta/' . $modelName . ':embedContent';

		// Prepare request body structure
		$body = [
			'content' => [
				'parts' => [
					[
						'text' => $text,
					],
				],
			],
		];

		$response = wp_remote_post( $url, [
			'headers' => [
				'Content-Type'   => 'application/json',
				'x-goog-api-key' => $apiKey,
			],
			'body'    => wp_json_encode( $body ),
			'timeout' => 12000,
		] );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body_raw = wp_remote_retrieve_body( $response );
		$data     = json_decode( $body_raw, true );

		// The embedding vector is returned under `embeddings` in results
		if ( isset( $data['embedding']['values'] ) && is_array( $data['embedding']['values'] ) ) {
			return $data['embedding']['values'];
		}

		return false;
	}

	/**
	 * Modify an image using Gemini image model.
	 *
	 * @param  string  $image_url  URL of the image to modify.
	 * @param  string  $feature  The modification feature to apply.
	 * @param  array  $options  Optional settings for the modification.
	 *
	 * @return array | WP_Error Returns the URL of the modified image on success, or false on failure.
	 */
	public function modifyImage( $image_base64, $mime_type, $feature, $options = [], $prompt = '' ) {
		$apiKey = Functions::get_option_item( 'rtcl_ai_settings', 'gemini_api_key' );

		if ( ! $apiKey ) {
			return new WP_Error( 'missing_api_key', esc_html__( 'Gemini API key not found.', 'classified-listing' ) );
		}

		if ( empty( $prompt ) ) {
			$prompt = $this->generateImageEnhancePrompt( $feature, $options );
		}

		$body = [
			'contents' => [
				[
					'parts' => [
						[
							'inline_data' => [
								'mime_type' => $mime_type,
								'data'      => $image_base64,
							],
						],
						[
							'text' => $prompt ?: 'Enhance this image.',
						],
					],
				],
			],
		];

		$response = wp_remote_post(
			'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image:generateContent',
			[
				'headers' => [
					'Content-Type'   => 'application/json',
					'x-goog-api-key' => $apiKey,
				],
				'body'    => wp_json_encode( $body ),
				'timeout' => 12000,
			],
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'gemini_error', esc_html__( 'Error from Gemini API: ', 'classified-listing' ) . $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		$encoded = $body['candidates'][0]['content']['parts'][0]['inlineData']['data'] ?? '';

		if ( ! $encoded ) {
			return new WP_Error( 'no_image', esc_html__( 'No image returned by Gemini.', 'classified-listing' ) );
		}

		$mime_type = $body['candidates'][0]['content']['parts'][0]['inlineData']['mimeType'] ?? 'image/png';

		return [
			'encoded'    => $encoded,
			'mime_type'  => $mime_type,
			'image_data' => "data:$mime_type;base64,$encoded",
		];
	}

	private function generateImageEnhancePrompt( $feature, $options = [] ) {
		switch ( $feature ) {
			case 'remove_watermark':
				return 'Remove all visible watermarks from this image and keep natural texture.';
			case 'brightness':
				$level = $options['level'] ?? 'moderate';

				return "Increase the brightness to a {$level} level while preserving color balance.";
			case 'crop':
				return 'Crop the image to center the main object.';
			case 'resize':
				$w = $options['width'] ?? 1024;
				$h = $options['height'] ?? 1024;

				return "Resize this image to {$w}x{$h} resolution with clear details.";
			default:
				return 'Enhance image quality.';
		}
	}

	/**
	 * Calls the Gemini API to generate form field suggestions based on the provided prompt.
	 *
	 * @param  string  $prompt  The user’s prompt describing the form or fields to be suggested.
	 *
	 * @return string A JSON-encoded string containing the form field suggestions.
	 */
	public function callGeminiForField( $prompt, $theme ) {
		$systemPrompts = [
			"cl-classified" => "You are a helpful assistant that suggests form fields for General Classified listings (Classima). Provide a JSON array of form field suggestions. Each object should have 'label', 'type', 'placeholder', 'section', and 'required' properties. The 'type' should be one of: address, business_hours, category, checkbox, color_picker, custom_html, date, description, email, excerpt, file, images, input_hidden, location, map, number, phone, pricing, radio, recaptcha, repeater, select, social_profiles, switch,  text, textarea, title, url, view_count, website, whatsapp, zipcode, terms_and_condition. Title type must be provided and For profession-based directories (e.g., Doctor, Lawyer), the label should be the profession name (e.g., Doctor Name, Lawyer Name) and the type should be 'title'.If description field the type should be 'description'. If the type is select, checkbox, or radio, options should be an array of values. Provide at least 20 fields across 3 sections.If the type is select, checkbox, or radio, options should be an array of values. Try to Terms and Conditions in the last .Provide at least 20 fields across 3 sections. Keep the answer short and only return the JSON format",

			"classima" => "You are a helpful assistant that suggests form fields for General Classified listings (Classima). Provide a JSON array of form field suggestions. Each object should have 'label', 'type', 'placeholder', 'section', and 'required' properties. The 'type' should be one of: address, business_hours, category, checkbox, color_picker, custom_html, date, description, email, excerpt, file, images, input_hidden, location, map, number, phone, pricing, radio, recaptcha, repeater, select, social_profiles, switch, text, textarea, title, url, view_count, website, whatsapp, zipcode, terms_and_condition. Title type must be provided and For profession-based directories (e.g., Doctor, Lawyer), the label should be the profession name (e.g., Doctor Name, Lawyer Name) and the type should be 'title'.If description field the type should be 'description'. If the type is select, checkbox, or radio, options should be an array of values. Provide at least 20 fields across 3 sections. If the type is select, checkbox, or radio, options should be an array of values.Try to Terms and Conditions in the last. Provide at least 20 fields across 3 sections. Keep the answer short and only return the JSON format",

			"homlisti" => "You are a helpful assistant that suggests form fields for Real Estate listings (Homlisti). Provide a JSON array of form field suggestions. Each object should have 'label', 'type', 'placeholder', 'section', and 'required' properties. The 'type' should be one of: address, business_hours, category, checkbox, color_picker, custom_html, date, description, email, excerpt, file, images, input_hidden, location, map, number, phone, pricing, radio, recaptcha, repeater, select, social_profiles, switch, text, textarea, title, url, view_count, website, whatsapp, zipcode, terms_and_condition. Include real estate-specific fields such as Property Features (checkbox), Parking (Yes/No), Bedrooms, Bathrooms, Property Size, Build Year, and Proposed Sale Type (Sell/Buy/Rent). If the type is select, checkbox, or radio, options should be an array of values.Try to Terms and Conditions in the last.If description field the type should be 'description'. Provide at least 20 fields across 3 sections. Keep the answer short and only return the JSON format.",

			"hotel_directory" => "You are a helpful assistant that suggests form fields for Hotel Directory listings. Provide a JSON array of form field suggestions. Each object should have 'label', 'type', 'placeholder', 'section', and 'required' properties. The 'type' should be one of: address, business_hours, category, checkbox, color_picker, custom_html, date, description, email, excerpt, file, images, input_hidden, location, map, number, phone, pricing, radio, recaptcha, repeater, select, social_profiles, switch, text, textarea, title, url, view_count, website, whatsapp, zipcode, terms_and_condition. Include hotel-specific fields such as Amenities (checkbox), Opening Hours, and Instant Booking. If the type is select, checkbox, or radio, options should be an array of values.Try to Terms and Conditions in the last.If description field the type should be 'description'. Provide at least 20 fields across 3 sections. Keep the answer short and only return the JSON format.",
		];

		$defaultPrompt
			= "You are a helpful assistant that suggests form fields based on user prompts. Provide a JSON array of form field suggestions. Each object should have 'label', 'type', 'placeholder','section', and 'required' properties. The 'type' should be one of: address, business_hours, category, checkbox, color_picker, custom_html, date, description, email, excerpt, file, images, input_hidden, location, map, number, phone, pricing, radio, recaptcha, repeater, select, social_profiles, switch, text, textarea, title, url, view_count, website, whatsapp, zipcode, terms_and_condition. Title type must be provided and For profession-based directories (e.g., Doctor, Lawyer), the label should be the profession name (e.g., Doctor Name, Lawyer Name) and the type should be 'title'.If description field the type should be 'description'.If the type is select, checkbox, or radio, options should be an array of values. Provide at least 20 fields across 3 sections. Keep the answer short and only return the JSON format.";


		$systemPrompt = $systemPrompts[ $theme ] ?? $defaultPrompt;

		return $this->callGemini( $prompt, $systemPrompt, 0.7, $this->model, 'form' );
	}

	/**
	 * Calls the Gemini API to generate relevant keywords based on the provided prompt.
	 *
	 * @param  string  $prompt  The user’s prompt describing the context for keyword generation.
	 *
	 * @return string A JSON-encoded array of 10 relevant keywords.
	 */
	public function callGeminiForKeyword( $prompt ) {
		$data   = json_decode( $prompt, true );
		$prompt = $data['prompt'] ?? '';

		return $this->callGemini(
			$prompt,
			"You are an AI that generates relevant keywords based on user prompts. Return only a JSON array of keywords without any extra text. Provide exactly 10 keywords.",
		);
	}
}