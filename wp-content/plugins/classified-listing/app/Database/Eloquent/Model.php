<?php

namespace Rtcl\Database\Eloquent;


use Exception;
use Rtcl\Database\Eloquent\Concerns\HasAttributes;
use Rtcl\Database\Eloquent\Concerns\HasTimestamps;

abstract class Model {

	use HasAttributes, HasTimestamps, Query;

	/**
	 * The array of global scopes on the model.
	 *
	 * @var self
	 */
	protected static $query = null;

	/**
	 * @var bool
	 */
	protected $exists = false;

	/**
	 * Create a new Eloquent model instance.
	 *
	 * @param array $attributes
	 *
	 * @return void
	 * @throws Exception
	 */
	public function __construct( array $attributes = [] ) {
		$this->setTable();
		if ( $this->primaryKey === 'id' && !array_key_exists( $this->primaryKey, $this->casts ) ) {
			$this->casts[$this->primaryKey] = 'absint';
		}
	}


	public function save() {
		if ( $this->exists || $this->{$this->primaryKey} ) {
			$this->update();
		} else {
			$this->insert();
		}

		return $this;
	}

	public static function query( $query_id = null ) {
		static::$query = new static();
		static::$query->query_id = !empty( $query_id ) ? $query_id : uniqid();

		return static::$query;
	}


	/**
	 * Convert the model instance to an array.
	 *
	 * @return array
	 */
	public function __toArray(): array {
		$data = [];
		foreach ( $this->attributes as $key => $value ) {
			$data[$key] = $this->$key;
		}

		return $data;
	}

	/**
	 * Returns collection as pure array.
	 * Does depth array casting.
	 *
	 * @return array
	 * @since 1.0.2
	 *
	 */
	public function toArray(): array {
		return $this->__toArray();
	}

	/**
	 * Convert the model instance to JSON.
	 *
	 * @param int $options
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public function toJson( $options = 0 ): string {
		$json = wp_json_encode( $this->jsonSerialize(), $options );

		if ( JSON_ERROR_NONE !== json_last_error() ) {
			throw new Exception( esc_html( json_last_error_msg() ) );
		}

		return $json;
	}

	/**
	 * Convert the object into something JSON serializable.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->toArray();
	}

	/**
	 * Handle dynamic static method calls into the model.
	 *
	 * @param string $method
	 * @param array $parameters
	 *
	 * @return mixed
	 */
	public static function __callStatic( $method, $parameters ) {
		return ( new static )->$method( ...$parameters );
	}


	/**
	 * @param array $attributes
	 *
	 * @return void
	 */
	private function getAttributeFormat( array $attributes ) {

	}

	/**
	 * @param array $attributes
	 *
	 * @return $this
	 */
	public function fill( array $attributes ) {

		foreach ( $attributes as $key => $value ) {
			$this->setAttribute( $key, $value );
		}

		return $this;
	}


	/**
	 * Dynamically retrieve attributes on the model.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->getAttribute( $key );
	}

	/**
	 * Dynamically set attributes on the model.
	 *
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function __set( $key, $value ) {
		$this->setAttribute( $key, $value );
	}

}