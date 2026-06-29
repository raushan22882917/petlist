<?php

namespace Rtcl\Interfaces;
interface AIServiceInterface {
	public function generateResponse(string $input, string $system_prompt): string;
	
}
