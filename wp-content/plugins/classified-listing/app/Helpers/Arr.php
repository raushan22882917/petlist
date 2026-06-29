<?php

namespace Rtcl\Helpers;

use ArrayAccess;
use Closure;

class Arr {


	/**
	 * Check if an item exists in an array using "dot" notation.
	 *
	 * @param \ArrayAccess|array $array
	 * @param string             $key
	 *
	 * @return bool
	 */
	public static function has( $array, $key ) {
		if ( ! $array ) {
			return false;
		}

		if ( is_null( $key ) ) {
			return false;
		}

		if ( static::exists( $array, $key ) ) {
			return true;
		}

		foreach ( explode( '.', $key ) as $segment ) {
			if ( static::accessible( $array ) && static::exists( $array, $segment ) ) {
				$array = $array[ $segment ];
			} else {
				return false;
			}
		}

		return true;
	}

	/**
	 * Determine whether the given value is array accessible.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public static function accessible( $value ) {
		return is_array( $value ) || $value instanceof ArrayAccess;
	}

	/**
	 * Determine if the given key exists in the provided array.
	 *
	 * @param ArrayAccess|array $array
	 * @param string|int        $key
	 *
	 * @return bool
	 */
	public static function exists( $array, $key ) {

		if ( $array instanceof ArrayAccess ) {
			return $array->offsetExists( $key );
		}

		return array_key_exists( $key, $array );
	}

	/**
	 * Get an item from an array using "dot" notation.
	 *
	 * @param ArrayAccess|array $array
	 * @param string|int|null   $key
	 * @param mixed             $default
	 *
	 * @return mixed
	 */
	public static function get( $array, $key, $default = null ) {
		if ( ! static::accessible( $array ) ) {
			return static::value( $default );
		}

		if ( is_null( $key ) ) {
			return $array;
		}

		if ( static::exists( $array, $key ) ) {
			return $array[ $key ];
		}

		if ( strpos( $key, '.' ) === false ) {
			return $array[ $key ] ?? static::value( $default );
		}

		foreach ( explode( '.', $key ) as $segment ) {
			if ( static::accessible( $array ) && static::exists( $array, $segment ) ) {
				$array = $array[ $segment ];
			} else {
				return static::value( $default );
			}
		}

		return $array;
	}

	static function value( $value, ...$args ) {
		return $value instanceof Closure ? $value( ...$args ) : $value;
	}

	/**
	 * If the given value is not an array and not null, wrap it in one.
	 *
	 * @param mixed $value
	 *
	 * @return array
	 */
	public static function wrap( $value ) {
		if ( is_null( $value ) ) {
			return [];
		}

		return is_array( $value ) ? $value : [ $value ];
	}

	/**
	 * Convert the array into a query string.
	 *
	 * @param array $array
	 *
	 * @return string
	 */
	public static function query( $array ) {
		return http_build_query( $array, '', '&', PHP_QUERY_RFC3986 );
	}
}