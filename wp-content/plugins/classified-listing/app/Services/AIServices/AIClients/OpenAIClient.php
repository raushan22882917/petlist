<?php

namespace Rtcl\Services\AIServices\AIClients;

use CURLFile;
use Rtcl\Helpers\Functions;
use WP_Error;

/**
 * Class OpenAIClient
 *
 * This class integrates OpenAI's API for generating AI-driven responses.
 */
class OpenAIClient {
	/**
	 * @var string Model to be used for AI responses.
	 */
	protected $model = '';

	protected $token = '200';

	/**
	 * OpenAIClient constructor.
	 *
	 * Initializes the OpenAI client and sets API key and model from settings.
	 *
	 * @throws \Exception If required settings are missing.
	 */
	public function __construct() {
		$apiKey      = Functions::get_option_item( 'rtcl_ai_settings', 'gpt_api_key' );
		$this->model = Functions::get_option_item( 'rtcl_ai_settings', 'gpt_models' );
		$this->token = Functions::get_option_item( 'rtcl_ai_settings', 'gpt_max_token' );
		if ( empty( $apiKey ) || empty( $this->model ) ) {
			throw new \Exception( 'AI settings are not properly configured.' );
		}
	}

	/**
	 * Generates a response from the AI model based on the given prompt.
	 *
	 * @param  string  $prompt  The input text for the AI to respond to.
	 *
	 * @return string The AI-generated response or an error message.
	 */
	public function ask( string $prompt, string $system_prompt ): string {
		$apiKey = Functions::get_option_item( 'rtcl_ai_settings', 'gpt_api_key' );
		$url    = 'https://api.openai.com/v1/chat/completions'; // OpenAI API endpoint

		$response = wp_remote_post( $url, [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $apiKey,
			],
			'body'    => json_encode( [
				'model'       => $this->model, // Specify the model (e.g., "gpt-4")
				'messages'    => [
					[ 'role' => 'system', 'content' => $system_prompt . 'give me plain text no markdown and no html' ],
					[ 'role' => 'user', 'content' => $prompt ],
				],
				'temperature' => 0.7,
				'max_tokens'  => 500,
			] ),
			'timeout' => 100000,  // Increase timeout to 20 seconds
		] );

		if ( is_wp_error( $response ) ) {
			return 'Error: ' . $response->get_error_message();
		}

		// Parse the response body
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['choices'][0]['message']['content'] ) ) {
			$content = $data['choices'][0]['message']['content'];
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
		}

		return 'No response from AI.';
	}

	public function askKeyword( $data ) {
		return $this->callGPT4oForKeyword( $data );
	}

	public function askFormField( $data ) {
		$theme  = Functions::get_current_theme();
		$data   = json_decode( $data, true );
		$prompt = $data['prompt'] ?? '';

		return $this->callGPT4o( $prompt, $theme );
	}

	/**
	 * Calls the GPT-4o API to generate a response based on the provided prompt and instruction.
	 *
	 * @param  string  $prompt  The user’s prompt to send to the model.
	 * @param  string  $instruction  The instruction to provide context for the model.
	 * @param  float  $temperature  The temperature setting for randomness in the response. Default is 0.7.
	 * @param  string  $model  The OpenAI model to use, default is 'gpt-4o-mini'.
	 *
	 * @return false|string The cleaned and formatted JSON response from the API.
	 */
	public function callOpenAI( $prompt, $instruction, $temperature = 0.7, $model = null, $for = 'keyword' ) {
		$apiKey = Functions::get_option_item( 'rtcl_ai_settings', 'gpt_api_key' );
		$url    = 'https://api.openai.com/v1/chat/completions'; // OpenAI API endpoint

		$response = wp_remote_post( $url, [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $apiKey,
			],
			'body'    => json_encode( [
				'model'       => $this->model,
				'messages'    => [
					[
						'role'    => 'system',
						'content' => $instruction,
					],
					[
						'role'    => 'user',
						'content' => $prompt,
					],
				],
				'temperature' => $temperature,
			] ),
			'timeout' => 100000,  // Increase timeout to 20 seconds
		] );

		if ( is_wp_error( $response ) ) {
			return 'Error: ' . $response->get_error_message();
		}

		// Parse the response body
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! empty( $data['error'] ) ) {
			return $data;
		}

		// Extract and clean response
		$aiResponse    = $data['choices'][0]['message']['content'] ?? '[]';
		$cleanResponse = trim( $aiResponse, "```json\n \t\r\0\x0B" );

		if ( $for == 'keyword' ) {
			return wp_send_json_success( [ 'data' => json_decode( $cleanResponse, true ) ] );
		}

		// Return a JSON response with the cleaned and formatted content
		return wp_send_json_success( [ 'response' => json_decode( $cleanResponse, true ) ] );
	}

	/**
	 * @param  string  $text
	 *
	 * @return false|mixed
	 */
	public function generateEmbedding( string $text ) {
		$apiKey = Functions::get_option_item( 'rtcl_ai_settings', 'gpt_api_key' );
		$url    = 'https://api.openai.com/v1/embeddings';

		$response = wp_remote_post( $url, [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $apiKey,
			],
			'body'    => json_encode( [
				'model' => 'text-embedding-3-small',
				'input' => $text,
			] ),
			'timeout' => 60,
		] );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		return $data['data'][0]['embedding'] ?? false;
	}

	/**
	 * Modify an image using OpenAI image edits endpoint.
	 *
	 * Downloads the image, sends it to OpenAI's images/edits endpoint with the
	 * requested operation, and uploads the edited image to WordPress media.
	 *
	 * @param  string  $image_url  URL of the image to modify.
	 * @param  string  $feature  Feature/operation to apply (e.g., remove_watermark, brightness).
	 * @param  array  $options  Optional parameters for the feature.
	 *
	 * @return string|WP_Error Edited image URL on success, or WP_Error on failure.
	 */
	public function modifyImage( $image_url, $feature, $options = [] ) {
		$apiKey = Functions::get_option_item( 'rtcl_ai_settings', 'gpt_api_key' );

		if ( ! $apiKey ) {
			return new WP_Error( 'missing_key', __( 'Missing OpenAI API key.', 'classified-listing' ) );
		}

		// download image
		$image_response = wp_remote_get( $image_url );
		if ( is_wp_error( $image_response ) ) {
			return new WP_Error( 'fetch_error', $image_response->get_error_message() );
		}

		$image_body = wp_remote_retrieve_body( $image_response );
		if ( empty( $image_body ) ) {
			return new WP_Error( 'empty_body', __( 'Failed to download image content.', 'classified-listing' ) );
		}

		$mime_type = wp_remote_retrieve_header( $image_response, 'content-type' );
		if ( empty( $mime_type ) ) {
			$mime_type = 'image/png';
		}

		// Save to temporary file
		$tmp_file = wp_tempnam( $image_url );
		file_put_contents( $tmp_file, $image_body );

		// Prepare prompt text
		$prompt = $this->generateImageEnhancePrompt( $feature, $options );

		// Prepare multipart body
		$curl       = curl_init();
		$image_path = '/Users/radiustheme/Downloads/projects/wptest/app/public/wp-content/uploads/classified-listing/2025/11/pen.png';
		$body       = [
			'model'  => 'gpt-image-1',
			'prompt' => $prompt ?: 'Enhance this image',
			'size'   => '1024x1024',
			'image'  => new CURLFile( $image_path, 'image/png', basename( $image_path ) ),
		];
		curl_setopt_array( $curl, [
			CURLOPT_URL            => 'https://api.openai.com/v1/images/edits',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $body,
			CURLOPT_HTTPHEADER     => [
				'Authorization: Bearer ' . $apiKey,
			],
			CURLOPT_TIMEOUT        => 12000,
		] );
		$response = curl_exec( $curl );
		$error    = curl_error( $curl );
		$status   = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

		curl_close( $curl );

		$decoded = json_decode( $response, true );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'api_error', $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['data'][0]['url'] ) ) {
			return new WP_Error( 'no_image', __( 'No image returned by OpenAI.', 'classified-listing' ) );
		}

		$new_image_url = $body['data'][0]['url'];

		// Download the enhanced image from OpenAI's returned URL
		$new_image_response = wp_remote_get( $new_image_url );
		if ( is_wp_error( $new_image_response ) ) {
			return new WP_Error( 'download_failed', $new_image_response->get_error_message() );
		}

		$new_image_data = wp_remote_retrieve_body( $new_image_response );
		if ( empty( $new_image_data ) ) {
			return new WP_Error( 'empty_new_image', __( 'Failed to download enhanced image.', 'classified-listing' ) );
		}

		// Upload the enhanced image to WordPress
		$upload = wp_upload_bits( basename( $image_url ), null, $new_image_data );

		if ( $upload['error'] ) {
			return new WP_Error( 'upload_failed', $upload['error'] );
		}

		// Clean up temporary file
		@unlink( $tmp_file );

		return esc_url_raw( $upload['url'] );
	}

	/**
	 * Generate a natural-language prompt for image editing based on the requested feature.
	 *
	 * @param  string  $feature  The image modification feature (e.g. `remove_watermark`, `brightness`, `crop`, `resize`, `upscale`).
	 * @param  array  $options  Optional associative array of parameters for the feature (e.g. `['brightness' => 120]`, `['width' => 800, 'height' => 600]`).
	 *
	 * @return string A text prompt describing the desired image edit.
	 */
	private function generateImageEnhancePrompt( $feature, $options ) {
		switch ( $feature ) {
			case 'remove_watermark':
				return 'Remove any watermark or logo from the image while keeping all other details intact.';
			case 'brightness':
				$level = $options['brightness'] ?? 120;

				return "Enhance image brightness by {$level}% while maintaining natural color balance.";
			case 'crop':
				return 'Crop the image to focus on the main object or subject.';
			case 'resize':
				$w = $options['width'] ?? 800;
				$h = $options['height'] ?? 600;

				return "Resize the image to {$w}x{$h} pixels while maintaining quality.";
			case 'upscale':
				return 'Increase image resolution and sharpness without distortion.';
			default:
				return 'Enhance the overall quality of the image.';
		}
	}

	/**
	 * Calls the GPT-4o API to generate form field suggestions based on the provided prompt.
	 *
	 * @param  string  $prompt  The user’s prompt describing the form or fields to be suggested.
	 *
	 * @return string A JSON-encoded string containing the form field suggestions.
	 */
	public function callGPT4o( $prompt, $theme ) {
		$systemPrompts = [
			"cl-classified" => "You are a helpful assistant that suggests form fields for General Classified listings (Classima). Provide a JSON array of form field suggestions. Each object should have 'label', 'type', 'placeholder', 'section', and 'required' properties. The 'type' should be one of: address, business_hours, category, checkbox, color_picker, custom_html, date, description, email, excerpt, file, images, input_hidden, location, map, number, phone, pricing, radio, recaptcha, repeater, select, social_profiles, switch,  text, textarea, title, url, view_count, website, whatsapp, zipcode, terms_and_condition. Title type must be provided and For profession-based directories (e.g., Doctor, Lawyer), the label should be the profession name (e.g., Doctor Name, Lawyer Name) and the type should be 'title'.If description field the type should be 'description'. If the type is select, checkbox, or radio, options should be an array of values. Provide at least 20 fields across 3 sections.If the type is select, checkbox, or radio, options should be an array of values. Try to Terms and Conditions in the last .Provide at least 20 fields across 3 sections. Keep the answer short and only return the JSON format",

			"classima" => "You are a helpful assistant that suggests form fields for General Classified listings (Classima). Provide a JSON array of form field suggestions. Each object should have 'label', 'type', 'placeholder', 'section', and 'required' properties. The 'type' should be one of: address, business_hours, category, checkbox, color_picker, custom_html, date, description, email, excerpt, file, images, input_hidden, location, map, number, phone, pricing, radio, recaptcha, repeater, select, social_profiles, switch, text, textarea, title, url, view_count, website, whatsapp, zipcode, terms_and_condition. Title type must be provided and For profession-based directories (e.g., Doctor, Lawyer), the label should be the profession name (e.g., Doctor Name, Lawyer Name) and the type should be 'title'.If description field the type should be 'description'. If the type is select, checkbox, or radio, options should be an array of values. Provide at least 20 fields across 3 sections. If the type is select, checkbox, or radio, options should be an array of values.Try to Terms and Conditions in the last. Provide at least 20 fields across 3 sections. Keep the answer short and only return the JSON format",

			"homlisti" => "You are a helpful assistant that suggests form fields for Real Estate listings (Homlisti). Provide a JSON array of form field suggestions. Each object should have 'label', 'type', 'placeholder', 'section', and 'required' properties. The 'type' should be one of: address, business_hours, category, checkbox, color_picker, custom_html, date, description, email, excerpt, file, images, input_hidden, location, map, number, phone, pricing, radio, recaptcha, repeater, select, social_profiles, switch, text, textarea, title, url, view_count, website, whatsapp, zipcode, terms_and_condition. Include real estate-specific fields such as Property Features (checkbox), Parking (Yes/No), Bedrooms, Bathrooms, Property Size, Build Year, and Proposed Sale Type (Sell/Buy/Rent). If the type is select, checkbox, or radio, options should be an array of values.Try to Terms and Conditions in the last.If description field the type should be 'description'. Provide at least 20 fields across 3 sections. Keep the answer short and only return the JSON format.",

			"hotel_directory" => "You are a helpful assistant that suggests form fields for Hotel Directory listings. Provide a JSON array of form field suggestions. Each object should have 'label', 'type', 'placeholder', 'section', and 'required' properties. The 'type' should be one of: address, business_hours, category, checkbox, color_picker, custom_html, date, description, email, excerpt, file, images, input_hidden, location, map, number, phone, pricing, radio, recaptcha, repeater, select, social_profiles, switch, text, textarea, title, url, view_count, website, whatsapp, zipcode, terms_and_condition. Include hotel-specific fields such as Amenities (checkbox), Opening Hours, and Instant Booking. If the type is select, checkbox, or radio, options should be an array of values.Try to Terms and Conditions in the last.If description field the type should be 'description'. Provide at least 20 fields across 3 sections. Keep the answer short and only return the JSON format.",
		];

		$defaultPrompt
			= "You are a helpful assistant that suggests form fields based on user prompts. Provide a JSON array of form field suggestions. Each object should have 'label', 'type', 'placeholder','section', and 'required' properties. The 'type' should be one of: address, business_hours, category, checkbox, color_picker, custom_html, date, description, email, excerpt, file, images, input_hidden, location, map, number, phone, pricing, radio, recaptcha, repeater, select, social_profiles, switch, text, textarea, title, url, view_count, website, whatsapp, zipcode, terms_and_condition. Title type must be provided and For profession-based directories (e.g., Doctor, Lawyer), the label should be the profession name (e.g., Doctor Name, Lawyer Name) and the type should be 'title'.If description field the type should be 'description'.If the type is select, checkbox, or radio, options should be an array of values. Provide at least 20 fields across 3 sections. Keep the answer short and only return the JSON format.";


		$systemPrompt = $systemPrompts[ $theme ] ?? $defaultPrompt;

		return $this->callOpenAI( $prompt, $systemPrompt, 0.7, $this->model, 'form' );
	}

	/**
	 * Calls the GPT-4o API to generate relevant keywords based on the provided prompt.
	 *
	 * @param  string  $prompt  The user’s prompt describing the context for keyword generation.
	 *
	 * @return string A JSON-encoded array of 10 relevant keywords.
	 */
	public function callGPT4oForKeyword( $prompt ) {
		$data   = json_decode( $prompt, true );
		$prompt = $data['prompt'] ?? '';

		return $this->callOpenAI(
			$prompt,
			"You are an AI that generates relevant keywords based on user prompts. Return only a JSON array of keywords without any extra text. Provide exactly 10 keywords.",
		);
	}
}
