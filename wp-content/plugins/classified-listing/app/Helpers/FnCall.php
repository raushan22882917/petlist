<?php

namespace Rtcl\Helpers;
use Closure;
use InvalidArgumentException;

final class FnCall {
	/**
	 * @template T
	 * @param callable|string|array| \Closure $spec
	 * @param mixed ...$args
	 * @return mixed
	 */
	public static function call( $spec, ...$args) {
		
		if (is_string($spec) && strpos($spec, '::') !== false) {
			$spec = explode('::', $spec, 2);
		}
		if ($spec instanceof Closure) {
			return $spec(...$args);
		}
		if (is_callable($spec)) {
			return call_user_func_array($spec, $args);
		}
		throw new InvalidArgumentException('get_data_fn is not callable');
	}
}