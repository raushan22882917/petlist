<?php

namespace Rtcl\Controllers\Ajax;

use Exception;
use Rtcl\Helpers\Functions;

class AIController {
	/**
	 * Handle AI request
	 *
	 * @param string $endpoint
	 * @param string $serviceMethod
	 * @param array $formData
	 * @return void
	 */
	private function handle_ai_request(string $endpoint, string $serviceMethod, array $formData)
	{
		if (!wp_verify_nonce($_REQUEST[rtcl()->nonceId] ?? null, rtcl()->nonceText) || !current_user_can('manage_rtcl_options')) {
			wp_send_json_error(esc_html__('Session error !!', 'classified-listing'));
		}
		$body = json_encode($formData);

		if (Functions::is_ai_enabled()) {
			try {
				$aiService = rtcl()->factory->initializeAIService();
				$response = $aiService->{$serviceMethod}($body);
				if (is_wp_error($response)) {
					wp_send_json_error($response->get_error_message());
					return;
				}
				wp_send_json_success(['response' => $response]);
			} catch (Exception $e) {
				wp_send_json_error($e->getMessage());
			}
		} else {
			$api_url = "https://www.radiustheme.net/api/ai/rtcl/api/{$endpoint}";
			$response = $this->send_api_request($api_url, $body,[
				'X-Requested-With' => 'XMLHttpRequest',
				'Origin'           => site_url()
			]);
			wp_send_json_success($response);
		}
	}

	/**
	 * Fetch AI keyword
	 * @return void
	 */
	public function fetch_ai_keyword()
	{
		$form_data = Functions::clean($_POST['ai_form_data'] ?? []);
		$this->handle_ai_request('get-keyword', 'generateKeywordResponse', $form_data);
	}

	/**
	 * Fetch AI form fields
	 * @return void
	 */
	public function fetch_ai_form_fields()
	{
		$form_data = Functions::clean($_POST['ai_form_data'] ?? []);
		$this->handle_ai_request('chat', 'generateFormFieldsResponse', $form_data);
	}

	/**
	 * Send API request to AI service
	 *
	 * @param string $url
	 * @param string $body
	 * @param array $extra_headers
	 * @return mixed
	 */
	private function send_api_request(string $url, string $body, array $extra_headers = [])
	{
		$response = wp_remote_post($url, [
			'method'    => 'POST',
			'headers'   => array_merge(['Content-Type' => 'application/json'], $extra_headers),
			'body'      => $body,
			'timeout'   => 45
		]);

		if (is_wp_error($response)) {
			wp_send_json_error(['message' => $response->get_error_message()]);
		}

		$response_body = wp_remote_retrieve_body($response);
		$response_data = json_decode($response_body, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			wp_send_json_error(['message' => 'Invalid JSON response from AI service']);
		}

		return $response_data;
	}
}
?>
