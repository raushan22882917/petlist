<?php

namespace Rtcl\Helpers;

class Str {

	/**
	 * The cache of snake-cased words.
	 *
	 * @var array
	 */
	protected static $snakeCache = [];

	/**
	 * The cache of camel-cased words.
	 *
	 * @var array
	 */
	protected static $camelCache = [];

	/**
	 * The cache of studly-cased words.
	 *
	 * @var array
	 */
	protected static $studlyCache = [];

	/**
	 * Convert a value to camel case.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function camel( $value ) {
		if ( isset( static::$camelCache[ $value ] ) ) {
			return static::$camelCache[ $value ];
		}

		return static::$camelCache[ $value ] = lcfirst( static::studly( $value ) );
	}

	/**
	 * Convert the given string to upper-case.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function upper( $value ) {
		return function_exists('mb_strtoupper') ? mb_strtoupper( $value, 'UTF-8' ) : strtoupper( $value );
	}

	/**
	 * Begin a string with a single instance of a given value.
	 *
	 * @param string $value
	 * @param string $prefix
	 *
	 * @return string
	 */
	public static function start( $value, $prefix ) {
		$quoted = preg_quote( $prefix, '/' );

		return $prefix . preg_replace( '/^(?:' . $quoted . ')+/u', '', $value );
	}


	/**
	 * Returns the portion of the string specified by the start and length parameters.
	 *
	 * @param string $string
	 * @param int $start
	 * @param int|null $length
	 *
	 * @return string
	 */
	public static function substr( $string, $start, $length = null ) {
		return mb_substr( $string, $start, $length, 'UTF-8' );
	}

	/**
	 * Convert the given string to title case.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function title( $value ) {
		return mb_convert_case( $value, MB_CASE_TITLE, 'UTF-8' );
	}

	/**
	 * Pluralize the last word of an English, studly caps case string.
	 *
	 * @param string $value
	 * @param int|array|\Countable $count
	 *
	 * @return string
	 */
	public static function pluralStudly( $value, $count = 2 ) {
		$parts = preg_split( '/(.)(?=[A-Z])/u', $value, - 1, PREG_SPLIT_DELIM_CAPTURE );

		$lastWord = array_pop( $parts );

		return implode( '', $parts ) . self::plural( $lastWord, $count );
	}

	public static function class_basename( $class ) {
		$class = is_object( $class ) ? get_class( $class ) : $class;

		return basename( str_replace( '\\', '/', $class ) );
	}

	/**
	 * Get the plural form of an English word.
	 *
	 * @param string $value
	 * @param int|array $count
	 *
	 * @return string
	 */
	public static function plural( $value, $count = 2 ) {
		return $value;
	}

	/**
	 * Convert a string to snake case.
	 *
	 * @param string $value
	 * @param string $delimiter
	 *
	 * @return string
	 */
	public static function snake( $value, $delimiter = '_' ) {
		$key = $value;

		if ( isset( static::$snakeCache[ $key ][ $delimiter ] ) ) {
			return static::$snakeCache[ $key ][ $delimiter ];
		}

		if ( ! ctype_lower( $value ) ) {
			$value = preg_replace( '/\s+/u', '', ucwords( $value ) );

			$value = static::lower( preg_replace( '/(.)(?=[A-Z])/u', '$1' . $delimiter, $value ) );
		}

		return static::$snakeCache[ $key ][ $delimiter ] = $value;
	}


	/**
	 * Convert the given string to lower-case.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function lower( $value ) {
		return mb_strtolower( $value, 'UTF-8' );
	}

	/**
	 * Replace the given value in the given string.
	 *
	 * @param string|string[] $search
	 * @param string|string[] $replace
	 * @param string|string[] $subject
	 *
	 * @return string
	 */
	public static function replace( $search, $replace, $subject ) {
		return str_replace( $search, $replace, $subject );
	}

	/**
	 * Convert a value to studly caps case.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function studly( $value ) {
		$key = $value;

		if ( isset( static::$studlyCache[ $key ] ) ) {
			return static::$studlyCache[ $key ];
		}

		$words = explode( ' ', static::replace( [ '-', '_' ], ' ', $value ) );

		$studlyWords = array_map( function ( $word ) {
			return static::ucfirst( $word );
		}, $words );

		return static::$studlyCache[ $key ] = implode( $studlyWords );
	}


	/**
	 * Make a string's first character uppercase.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function ucfirst( $string ) {
		return static::upper( static::substr( $string, 0, 1 ) ) . static::substr( $string, 1 );
	}

}
