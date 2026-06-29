<?php

namespace Rtcl\Abstracts;

abstract class Provider {
	/**
	 * Booted method for any provider
	 * @return void
	 */
	public function booted() {
		// ...
	}

	/**
	 * Abstract booting method for provider
	 * @return void
	 */
	public abstract function booting();
}