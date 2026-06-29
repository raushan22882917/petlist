<?php

namespace Rtcl\Services\AIServices;

use Rtcl\Interfaces\AIServiceInterface;

/**
 * Class OpenAIAdapter
 *
 * This class adapts the OpenAI client to the AIServiceInterface.
 */
class OpenAIAdapter implements AIServiceInterface {
	/**
	 * @var object The OpenAI client instance.
	 */
	private $openAIClient;

	/**
	 * OpenAIAdapter constructor.
	 *
	 * @param object $openAIClient An instance of the OpenAI client.
	 */
	public function __construct($openAIClient) {
		$this->openAIClient = $openAIClient;
	}

	/**
	 * Generates a response from the OpenAI client.
	 *
	 * @param string $input The input text for the AI to respond to.
	 *
	 * @return string The AI-generated response.
	 */
	public function generateResponse(string $input, string $system_prompt): string {
		return $this->openAIClient->ask($input, $system_prompt);
	}

	public function generateKeywordResponse($data)
	{
		return $this->openAIClient->askKeyword($data);
	}

	public function generateFormFieldsResponse($data)
	{
		return $this->openAIClient->askFormField($data);
	}
	
}
