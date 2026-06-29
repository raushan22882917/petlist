<?php

namespace Rtcl\Database\Eloquent\Concerns;

use Rtcl\Helpers\Arr;
use Rtcl\Helpers\Str;
use RuntimeException;

trait HasAttributes {


	protected $primaryKey = 'id';

	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];


	/**
	 * The model attribute's original state.
	 *
	 * @var array
	 */
	protected $original = [];

	/**
	 * The changed model attributes.
	 *
	 * @var array
	 */
	protected $changes = [];

	protected $attributeFormat = [];


	/**
	 * The attributes that should be cast.
	 *
	 * @var array
	 */
	protected $casts = [];


	/**
	 * The attributes that have been cast using custom classes.
	 *
	 * @var array
	 */
	protected $classCastCache = [];

	/**
	 * The attributes that have been cast using "Attribute" return type mutators.
	 *
	 * @var array
	 */
	protected $attributeCastCache = [];


	/**
	 * The built-in, primitive cast types supported by Eloquent.
	 *
	 * @var string[]
	 */
	protected static $primitiveCastTypes = [
		'array',
		'bool',
		'boolean',
		'collection',
		'custom_datetime',
		'date',
		'datetime',
		'decimal',
		'double',
		'encrypted',
		'encrypted:array',
		'encrypted:collection',
		'encrypted:json',
		'encrypted:object',
		'float',
		'immutable_date',
		'immutable_datetime',
		'immutable_custom_datetime',
		'int',
		'integer',
		'absint',
		'json',
		'object',
		'real',
		'string',
		'timestamp',
	];


	/**
	 * Get an attribute from the model.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getAttribute( $key ) {
		if ( ! $key ) {
			return;
		}

		// If the attribute exists in the attribute array or has a "get" mutator we will
		// get the attribute's value. Otherwise, we will proceed as if the developers
		// are asking for a relationship's value. This covers both types of values.
		if ( array_key_exists( $key, $this->attributes ) ||
		     array_key_exists( $key, $this->casts ) ||
		     $this->hasGetMutator( $key ) ||
		     $this->isClassCastable( $key ) ) {
			return $this->getAttributeValue( $key );
		}

		// Here we will determine if the model base class itself contains this given key
		// since we don't want to treat any of those methods as relationships because
		// they are all intended as helper methods and none of these are relations.
		if ( method_exists( self::class, $key ) ) {
			return;
		}
	}


	/**
	 * Get a plain attribute (not a relationship).
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getAttributeValue( $key ) {
		return $this->transformModelValue( $key, $this->getAttributeFromArray( $key ) );
	}


	/**
	 * Transform a raw model value using mutators, casts, etc.
	 *
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	protected function transformModelValue( $key, $value ) {
		if ( $this->hasGetMutator( $key ) ) {
			return $this->mutateAttribute( $key, $value );
		}
		// If the attribute exists within the cast array, we will convert it to
		// an appropriate native PHP type dependent upon the associated value
		// given with the key in the pair. Dayle made this comment line up.
		if ( $this->hasCast( $key ) ) {
			return $this->castAttribute( $key, $value );
		}

		// If the attribute is listed as a date, we will convert it to a DateTime
		// instance on retrieval, which makes it quite convenient to work with
		// date fields without having to create a mutator for each property.
		if ( $value !== null
		     && \in_array( $key, $this->getDates(), false ) ) {
			return $this->asDateTime( $value );
		}

		return $value;
	}

	/**
	 * Get the attributes that should be converted to dates.
	 *
	 * @return array
	 */
	public function getDates() {
		if ( ! $this->usesTimestamps() ) {
			return [];
		}

		return [
			$this->getCreatedAtColumn(),
			$this->getUpdatedAtColumn(),
		];
	}

	/**
	 * Convert a DateTime to a storable string.
	 *
	 * @param mixed $value
	 *
	 * @return string|null
	 */
	public function fromDateTime( $value ) {
		return empty( $value ) ? $value : $this->asDateTime( $value )->format(
			$this->getDateFormat()
		);
	}


	/**
	 * Get an attribute from the $attributes array.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected function getAttributeFromArray( $key ) {
		return $this->getAttributes()[ $key ] ?? null;
	}

	/**
	 * Cast an attribute to a native PHP type.
	 *
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	protected function castAttribute( $key, $value ) {
		$castType = $this->getCastType( $key );
		if ( is_null( $value ) && in_array( $castType, static::$primitiveCastTypes ) ) {
			return null;
		}

		switch ( $castType ) {
			case 'int':
			case 'integer':
				return (int) $value;
			case 'absint':
				return absint($value);
			case 'real':
			case 'float':
			case 'double':
				return $this->fromFloat( $value );
			case 'decimal':
				return $this->asDecimal( $value, explode( ':', $castType, 2 )[1] );
			case 'string':
				return (string) $value;
			case 'bool':
			case 'boolean':
				return (bool) $value;
			case 'object':
				return $this->fromJson( $value, true );
			case 'array':
			case 'json':
				return $this->fromJson( $value );
//			case 'collection':
//				return new Collection($this->fromJson($value));
//			case 'date':
//				return $this->asDate($value);
//			case 'datetime':
//			case 'custom_datetime':
//				return $this->asDateTime($value);
//			case 'immutable_date':
//				return $this->asDate($value)->toImmutable();
//			case 'immutable_custom_datetime':
//			case 'immutable_datetime':
//				return $this->asDateTime($value)->toImmutable();
//			case 'timestamp':
//				return $this->asTimestamp($value);
		}

		return $value;
	}

	/**
	 * Normalize the response from a custom class caster.
	 *
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return array
	 */
	protected function normalizeCastClassResponse( $key, $value ) {
		return is_array( $value ) ? $value : [ $key => $value ];
	}

	/**
	 * Get all of the current attributes on the model.
	 *
	 * @return array
	 */
	public function getAttributes() {
		$this->mergeAttributesFromCachedCasts();

		return $this->attributes;
	}

	/**
	 * Merge the cast class and attribute cast attributes back into the model.
	 *
	 * @return void
	 */
	protected function mergeAttributesFromCachedCasts() {
//		$this->mergeAttributesFromClassCasts();
		//$this->mergeAttributesFromAttributeCasts();

	}

	/**
	 * Merge the cast class attributes back into the model.
	 *
	 * @return void
	 */
	protected function mergeAttributesFromAttributeCasts() {
		foreach ( $this->attributeCastCache as $key => $value ) {
			$attribute = $this->{Str::camel( $key )}();

			if ( $attribute->get && ! $attribute->set ) {
				continue;
			}

			$callback = $attribute->set ?: function ( $value ) use ( $key ) {
				$this->attributes[ $key ] = $value;
			};

			$this->attributes = array_merge(
				$this->attributes,
				$this->normalizeCastClassResponse(
					$key, call_user_func( $callback, $value, $this->attributes )
				)
			);
		}
	}

	/**
	 * Determine if a set mutator exists for an attribute.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function hasSetMutator( $key ) {
		return method_exists( $this, 'set' . Str::studly( $key ) . 'Attribute' );
	}

	/**
	 * Determine if a get mutator exists for an attribute.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function hasGetMutator( $key ) {
		return method_exists( $this, 'get' . Str::studly( $key ) . 'Attribute' );
	}


	/**
	 * Get the value of an attribute using its mutator.
	 *
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	protected function mutateAttribute( $key, $value ) {
		return $this->{'get' . Str::studly( $key ) . 'Attribute'}( $value );
	}


	/**
	 * Determine if the given key is cast using a custom class.
	 *
	 * @param string $key
	 *
	 * @return bool
	 *
	 */
	protected function isClassCastable( $key ) {
		if ( ! array_key_exists( $key, $this->getCasts() ) ) {
			return false;
		}

		$castType = $this->parseCasterClass( $this->getCasts()[ $key ] );

		if ( in_array( $castType, static::$primitiveCastTypes ) ) {
			return false;
		}

		if ( class_exists( $castType ) ) {
			return true;
		}
		$class = $this->getModel();
		throw new RuntimeException( esc_html("Call to undefined cast [{$castType}] on column [{$key}] in model [{$class}].") );
	}

	/**
	 * Set the value of an attribute using its mutator.
	 *
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	protected function setMutatedAttributeValue( $key, $value ) {
		return $this->{'set' . Str::studly( $key ) . 'Attribute'}( $value );
	}

	/**
	 * Set a given attribute on the model.
	 *
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function setAttribute( $key, $value ) {

		if ( $this->hasSetMutator( $key ) ) {
			return $this->setMutatedAttributeValue( $key, $value );
		}

		if ( ! is_null( $value ) && $this->isJsonCastable( $key ) ) {
			$value = $this->castAttributeAsJson( $key, $value );
		}

		$this->attributes[ $key ] = $value;

		return $this;
	}

	/**
	 * Determine whether a value is JSON castable for inbound manipulation.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	protected function isJsonCastable( $key ) {
		return $this->hasCast( $key, [
			'array',
			'json',
			'object'
		] );
	}


	public function getCasts() {
		return $this->casts;
	}

	/**
	 * Get the type of cast for a model attribute.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	protected function getCastType( $key ) {
//		if ( $this->isCustomDateTimeCast( $this->getCasts()[ $key ] ) ) {
//			return 'custom_datetime';
//		}
//
//		if ( $this->isImmutableCustomDateTimeCast( $this->getCasts()[ $key ] ) ) {
//			return 'immutable_custom_datetime';
//		}
//
//		if ( $this->isDecimalCast( $this->getCasts()[ $key ] ) ) {
//			return 'decimal';
//		}

		return trim( strtolower( $this->getCasts()[ $key ] ) );
	}

	/**
	 * Determine whether a value is Date / DateTime castable for inbound manipulation.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	protected function isDateCastable( $key ) {
		return $this->hasCast( $key, [ 'date', 'datetime', 'immutable_date', 'immutable_datetime' ] );
	}

	/**
	 * Determine whether an attribute should be cast to a native type.
	 *
	 * @param string $key
	 * @param array|string|null $types
	 *
	 * @return bool
	 */
	public function hasCast( $key, $types = null ) {
		if ( array_key_exists( $key, $this->getCasts() ) ) {
			return ! $types || in_array( $this->getCastType( $key ), (array) $types, true );
		}

		return false;
	}

	/**
	 * Cast the given attribute to JSON.
	 *
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return string
	 */
	protected function castAttributeAsJson( $key, $value ) {
		$value = $this->asJson( $value );

		if ( $value === false ) {
			$message = json_last_error_msg();
			throw new RuntimeException( esc_html("Unable to encode attribute [{$key}] for model [{$this}] to JSON: {$message}.") );
		}

		return $value;
	}


	/**
	 * Decode the given float.
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function fromFloat( $value ) {
		switch ( (string) $value ) {
			case 'Infinity':
				return INF;
			case '-Infinity':
				return - INF;
			case 'NaN':
				return NAN;
			default:
				return (float) $value;
		}
	}

	/**
	 * Return a decimal as string.
	 *
	 * @param float $value
	 * @param int $decimals
	 *
	 * @return string
	 */
	protected function asDecimal( $value, $decimals ) {
		return number_format( $value, $decimals, '.', '' );
	}

	/**
	 * Encode the given value as JSON.
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	protected function asJson( $value ) {
		return $this->isJson( $value ) ? $value : wp_json_encode( $value );
	}

	function isJson( $string ) {
		return is_string( $string ) &&
		       ( is_object( json_decode( $string ) ) ||
		         is_array( json_decode( $string ) ) );
	}


	/**
	 * Decode the given JSON back into an array or object.
	 *
	 * @param string $value
	 * @param bool $asObject
	 *
	 * @return mixed
	 */
	public function fromJson( $value, $asObject = false ) {
		return json_decode( $value, ! $asObject );
	}


	/**
	 * Get the attributes that have been changed since the last sync.
	 *
	 * @return array
	 */
	public function getDirty() {
		$dirty = [];

		foreach ( $this->getAttributes() as $key => $value ) {
			if ( ! $this->originalIsEquivalent( $key ) ) {
				$dirty[ $key ] = $value;
			}
		}

		return $dirty;
	}


	/**
	 * Get the attributes that were changed.
	 *
	 * @return array
	 */
	public function getChanges() {
		return $this->changes;
	}

	/**
	 * Determine if the new and old values for a given key are equivalent.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function originalIsEquivalent( $key ) {
		if ( ! array_key_exists( $key, $this->original ) ) {
			return false;
		}

		$attribute = Arr::get( $this->attributes, $key );
		$original  = Arr::get( $this->original, $key );

		if ( $attribute === $original ) {
			return true;
		} elseif ( is_null( $attribute ) ) {
			return false;
		} elseif ( $this->isDateAttribute( $key ) ) {
			return $this->fromDateTime( $attribute ) === $this->fromDateTime( $original );
		} elseif ( $this->hasCast( $key, [ 'object', 'collection' ] ) ) {
			return $this->fromJson( $attribute ) ===
			       $this->fromJson( $original );
		} elseif ( $this->hasCast( $key, [ 'real', 'float', 'double' ] ) ) {
			if ( ( $attribute === null && $original !== null ) || ( $attribute !== null && $original === null ) ) {
				return false;
			}

			return abs( $this->castAttribute( $key, $attribute ) - $this->castAttribute( $key, $original ) ) < PHP_FLOAT_EPSILON * 4;
		} elseif ( $this->hasCast( $key, static::$primitiveCastTypes ) ) {
			return $this->castAttribute( $key, $attribute ) ===
			       $this->castAttribute( $key, $original );
		}

		return is_numeric( $attribute ) && is_numeric( $original )
		       && strcmp( (string) $attribute, (string) $original ) === 0;
	}

	/**
	 * Determine if the given attribute is a date or date castable.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	protected function isDateAttribute( $key ) {
		return in_array( $key, $this->getDates(), true ) ||
		       $this->isDateCastable( $key );
	}

	/**
	 * Get the model's raw original attribute values.
	 *
	 * @param string|null $key
	 * @param mixed $default
	 *
	 * @return mixed|array
	 */
	public function getRawOriginal( $key = null, $default = null ) {
		return Arr::get( $this->original, $key, $default );
	}

	/**
	 * Get a subset of the model's attributes.
	 *
	 * @param array|mixed $attributes
	 *
	 * @return array
	 */
	public function only( $attributes ) {
		$results = [];

		foreach ( is_array( $attributes ) ? $attributes : func_get_args() as $attribute ) {
			$results[ $attribute ] = $this->getAttribute( $attribute );
		}

		return $results;
	}

	/**
	 * Sync the original attributes with the current.
	 *
	 * @return $this
	 */
	public function syncOriginal() {
		$this->original = $this->getAttributes();

		return $this;
	}

	/**
	 * Sync a single original attribute with its current value.
	 *
	 * @param string $attribute
	 *
	 * @return $this
	 */
	public function syncOriginalAttribute( $attribute ) {
		return $this->syncOriginalAttributes( $attribute );
	}

	/**
	 * Sync multiple original attribute with their current values.
	 *
	 * @param array|string $attributes
	 *
	 * @return $this
	 */
	public function syncOriginalAttributes( $attributes ) {
		$attributes = is_array( $attributes ) ? $attributes : func_get_args();

		$modelAttributes = $this->getAttributes();

		foreach ( $attributes as $attribute ) {
			$this->original[ $attribute ] = $modelAttributes[ $attribute ];
		}

		return $this;
	}

	/**
	 * Sync the changed attributes.
	 *
	 * @return $this
	 */
	public function syncChanges() {
		$this->changes = $this->getDirty();

		return $this;
	}

	/**
	 * Determine if the model or any of the given attribute(s) have been modified.
	 *
	 * @param array|string|null $attributes
	 *
	 * @return bool
	 */
	public function isDirty( $attributes = null ) {
		return $this->hasChanges(
			$this->getDirty(), is_array( $attributes ) ? $attributes : func_get_args()
		);
	}

	/**
	 * Determine if the model or all the given attribute(s) have remained the same.
	 *
	 * @param array|string|null $attributes
	 *
	 * @return bool
	 */
	public function isClean( $attributes = null ) {
		return ! $this->isDirty( ...func_get_args() );
	}

	/**
	 * Determine if the model or any of the given attribute(s) have been modified.
	 *
	 * @param array|string|null $attributes
	 *
	 * @return bool
	 */
	public function wasChanged( $attributes = null ) {
		return $this->hasChanges(
			$this->getChanges(), is_array( $attributes ) ? $attributes : func_get_args()
		);
	}


	/**
	 * Determine if any of the given attributes were changed.
	 *
	 * @param array $changes
	 * @param array|string|null $attributes
	 *
	 * @return bool
	 */
	protected function hasChanges( $changes, $attributes = null ) {
		// If no specific attributes were provided, we will just see if the dirty array
		// already contains any attributes. If it does we will just return that this
		// count is greater than zero. Else, we need to check specific attributes.
		if ( empty( $attributes ) ) {
			return count( $changes ) > 0;
		}

		// Here we will spin through every attribute and see if this is in the array of
		// dirty attributes. If it is, we will return true and if we make it through
		// all of the attributes for the entire array we will return false at end.
		foreach ( Arr::wrap( $attributes ) as $attribute ) {
			if ( array_key_exists( $attribute, $changes ) ) {
				return true;
			}
		}

		return false;
	}
}