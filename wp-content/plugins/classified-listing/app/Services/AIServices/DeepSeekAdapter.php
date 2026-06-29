<?php

namespace Rtcl\Services\AIServices;

use Rtcl\Interfaces\AIServiceInterface;

/**
 * Class DeepSeekAdapter
 *
 * This class adapts the DeepSeek client to the AIServiceInterface.
 */
class DeepSeekAdapter implements AIServiceInterface {
	/**
	 * @var object The DeepSeek client instance.
	 */
	private $deepSeekClient;

	/**
	 * DeepSeekAdapter constructor.
	 *
	 * @param object $deepSeekClient An instance of the DeepSeek client.
	 */
	public function __construct($deepSeekClient) {
		$this->deepSeekClient = $deepSeekClient;
	}

	/**
	 * Generates a response from the DeepSeek client.
	 *
	 * @param string $input The input text for the AI to respond to.
	 * @param string $system_prompt The system prompt to guide the AI's response.
	 *
	 * @return string The AI-generated response.
	 */
	public function generateResponse(string $input, string $system_prompt): string {
		return $this->deepSeekClient->ask($input, $system_prompt);
	}

	/**
	 * Generates a keyword response from the DeepSeek client.
	 *
	 * @param mixed $data The data containing the prompt for keyword generation.
	 *
	 * @return string The AI-generated keyword response.
	 */
	public function generateKeywordResponse($data) {
		return $this->deepSeekClient->askKeyword($data);
	}

	/**
	 * Generates form field suggestions from the DeepSeek client.
	 *
	 * @param mixed $data The data containing the prompt for form field generation.
	 *
	 * @return string The AI-generated form field suggestions.
	 */
	public function generateFormFieldsResponse($data) {
		return $this->deepSeekClient->askFormField($data);
	}
}