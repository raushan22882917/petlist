<?php

namespace Rtcl\Services\AIServices;

use Rtcl\Interfaces\AIServiceInterface;

/**
 * Class GeminiAdapter
 *
 * This class adapts the Gemini client to the AIServiceInterface.
 */
class GeminiAdapter implements AIServiceInterface {
	/**
	 * @var object The Gemini client instance.
	 */
	private $geminiClient;

	/**
	 * GeminiAdapter constructor.
	 *
	 * @param object $geminiClient An instance of the Gemini client.
	 */
	public function __construct($geminiClient) {
		$this->geminiClient = $geminiClient;
	}

	/**
	 * Generates a response from the Gemini client.
	 *
	 * @param string $input The input text for the AI to respond to.
	 * @param string $system_prompt The system prompt to provide context.
	 *
	 * @return string The AI-generated response.
	 */
	public function generateResponse(string $input, string $system_prompt): string {
		return $this->geminiClient->ask($input, $system_prompt);
	}

	public function generateKeywordResponse($data)
	{
		return $this->geminiClient->askKeyword($data);
	}

	public function generateFormFieldsResponse($data)
	{
		return $this->geminiClient->askFormField($data);
	}
}