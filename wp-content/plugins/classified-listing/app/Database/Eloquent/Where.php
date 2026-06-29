<?php

namespace Rtcl\Database\Eloquent;

use Closure;

class Where {

	/**
	 * Where parts.
	 *
	 * @var array
	 */
	protected array $wheres = [];

	/**
	 * Get where clause.
	 *
	 * @param boolean $raw Raw context.
	 *
	 * @return string
	 */
	public function getWhereClauses( bool $raw = false ): string {
		if ( $raw ) {
			return join( ' ', $this->wheres );
		}

		return 'WHERE ' . trim( join( ' ', $this->wheres ) );
	}

	/**
	 * Reset query.
	 */
	public function reset() {
		$this->wheres = [];
	}

	/**
	 * Add a basic where clause to the query.
	 *
	 * @param Closure|string|array $column The SQL column.
	 * @param mixed $operator Operator or value depending if $value is not set.
	 * @param mixed $value The value if $operator is set.
	 * @param string $type The where type ( and, or ).
	 *
	 * @return self The current query.
	 */
	public function where( $column, $operator = null, $value = null, $type = 'AND' ) {
		// If the column is an array, we will assume it is an array of key-value pairs
		// and can add them each as a where clause. We will maintain the boolean we
		// received when the method was called and pass it into the nested where.
		if ( is_array( $column ) ) {
			return $this->add_array_of_wheres( $column, $type );
		}

		// Here we will make some assumptions about the operator. If only 2 values are
		// passed to the method, we will assume that the operator is an equals sign
		// and keep going. Otherwise, we'll require the operator to be passed in.
		[ $value, $operator ] = $this->prepare_value_and_operator( $value, $operator );

		// If the columns is actually a Closure instance, we will assume the developer
		// wants to begin a nested where statement which is wrapped in parenthesis.
		// We'll add that Closure to the query then return back out immediately.
		if ( $column instanceof Closure ) {
			return $this->where_nested( $column, $type );
		}

		// If the value is a Closure, it means the developer is performing an entire
		// sub-select within the query and we will need to compile the sub-select
		// within the where clause to get the appropriate query record results.
		if ( $value instanceof Closure ) {
			return $this->where_sub( $column, $operator, $value, $type );
		}

		// Now that we are working with just a simple query we can put the elements
		// in our array and add the query binding to our array of bindings that
		// will be bound to each SQL statements when it is finally executed.
		$this->wheres[] = $this->generate_where( $column, $operator, $value, empty( $this->wheres ) ? '' : $type );

		return $this;
	}

	/**
	 * Add an array of where clauses to the query.
	 *
	 * @param array $column The columns.
	 * @param string $type The where type ( and, or ).
	 *
	 * @return self The current query.
	 */
	private function add_array_of_wheres( $column, $type ) {
		return $this->where_nested(
			function ( $query ) use ( $column ) {
				foreach ( $column as $key => $value ) {
					if ( is_numeric( $key ) && is_array( $value ) ) {
						$query->where( ...array_values( $value ) );
					} else {
						$query->where( $key, '=', $value );
					}
				}
			},
			$type
		);
	}

	/**
	 * Add a nested where statement to the query.
	 *
	 * @param Closure $callback Callback.
	 * @param string $type Type.
	 *
	 * @return self The current query.
	 */
	private function where_nested( Closure $callback, $type = 'and' ) {
		$query = new Where( uniqid( 'nested-' ), $this->table, $this->alias );
		call_user_func( $callback, $query );

		if ( ! empty( $this->wheres ) ) {
			$this->wheres[] = $type;
		}

		$this->wheres[] = '(' . $query->getWhereClauses( true ) . ' )';

		return $this;
	}

	/**
	 * Generate where sub-query.
	 *
	 * @param string $column The SQL column.
	 * @param string $operator Operator or value depending if $value is not set.
	 * @param Closure $callback Callback.
	 * @param string $type The where type ( and, or ).
	 *
	 * @return self The current query.
	 */
	private function where_sub( $column, $operator, Closure $callback, $type = 'AND' ) {
		$query = new Select( uniqid( 'nested-' ), '', '' );
		call_user_func( $callback, $query );

		if ( ! empty( $this->wheres ) ) {
			$this->wheres[] = $type;
		}

		$this->wheres[] = join( ' ', [ $column, $operator, '(' . $query->get_query() . ')' ] );

		return $this;
	}

	/**
	 * Create an or where statement.
	 *
	 * @param string $column The SQL column.
	 * @param mixed $operator Operator or value depending if $value is not set.
	 * @param mixed $value The value if $operator is set.
	 *
	 * @return self The current query.
	 */
	public function orWhere( $column, $operator = null, $value = null ) { // @codingStandardsIgnoreLine
		return $this->where( $column, $operator, $value, 'OR' );
	}

	/**
	 * Creates a where in statement.
	 *
	 *     ->whereIn('id', [42, 38, 12])
	 *
	 * @param string $column The SQL column.
	 * @param array $options Array of values for in statement.
	 *
	 * @return self The current query.
	 */
	public function whereIn( $column, $options = [] ) { // @codingStandardsIgnoreLine
		if ( empty( $options ) ) {
			return $this;
		}

		return $this->where( $column, 'IN', $options );
	}

	/**
	 * Creates a where in statement.
	 *
	 *     ->orWhereIn('id', [42, 38, 12])
	 *
	 * @param string $column The SQL column.
	 * @param array $options Array of values for in statement.
	 *
	 * @return self The current query.
	 */
	public function orWhereIn( $column, $options = [] ) { // @codingStandardsIgnoreLine
		if ( empty( $options ) ) {
			return $this;
		}

		return $this->where( $column, 'IN', $options, 'OR' );
	}

	/**
	 * Creates a where not in statement.
	 *
	 *     ->whereNotIn('id', [42, 38, 12])
	 *
	 * @param string $column The SQL column.
	 * @param array $options Array of values for in statement.
	 *
	 * @return Query The current query.
	 */
	public function whereNotIn( $column, $options = [] ) { // @codingStandardsIgnoreLine
		if ( empty( $options ) ) {
			return $this;
		}

		return $this->where( $column, 'NOT IN', $options );
	}

	/**
	 * Creates a where not in statement.
	 *
	 *     ->orWhereNotIn('id', [42, 38, 12])
	 *
	 * @param string $column The SQL column.
	 * @param array $options Array of values for in statement.
	 *
	 * @return Query The current query.
	 */
	public function orWhereNotIn( $column, $options = [] ) { // @codingStandardsIgnoreLine
		if ( empty( $options ) ) {
			return $this;
		}

		return $this->where( $column, 'NOT IN', $options, 'OR' );
	}

	/**
	 * Creates a where between statement.
	 *
	 *     ->whereBetween('id', [10, 100])
	 *
	 * @param string $column The SQL column.
	 * @param array $options Array of values for in statement.
	 *
	 * @return Query The current query.
	 */
	public function whereBetween( $column, $options = [] ) { // @codingStandardsIgnoreLine
		if ( empty( $options ) ) {
			return $this;
		}

		return $this->where( $column, 'BETWEEN', $options );
	}

	/**
	 * Creates a where between statement.
	 *
	 *     ->orWhereBetween('id', [10, 100])
	 *
	 * @param string $column The SQL column.
	 * @param array $options Array of values for in statement.
	 *
	 * @return Query The current query.
	 */
	public function orWhereBetween( $column, $options = [] ) { // @codingStandardsIgnoreLine
		if ( empty( $options ) ) {
			return $this;
		}

		return $this->where( $column, 'BETWEEN', $options, 'OR' );
	}

	/**
	 * Creates a where not between statement.
	 *
	 *     ->whereNotBetween('id', [10, 100])
	 *
	 * @param string $column The SQL column.
	 * @param array $options Array of values for in statement.
	 *
	 * @return Query The current query.
	 */
	public function whereNotBetween( $column, $options = [] ) { // @codingStandardsIgnoreLine
		if ( empty( $options ) ) {
			return $this;
		}

		return $this->where( $column, 'NOT BETWEEN', $options );
	}

	/**
	 * Creates a where not between statement.
	 *
	 *     ->orWhereNotBetween('id', [10, 100])
	 *
	 * @param string $column The SQL column.
	 * @param array $options Array of values for in statement.
	 *
	 * @return Query The current query.
	 */
	public function orWhereNotBetween( $column, $options = [] ) { // @codingStandardsIgnoreLine
		if ( empty( $options ) ) {
			return $this;
		}

		return $this->where( $column, 'NOT BETWEEN', $options, 'OR' );
	}

	/**
	 * Creates a where like statement.
	 *
	 *     ->whereLike('id', 'value')
	 *
	 * @param string $column The SQL column.
	 * @param string $value Value for like statement.
	 * @param string $start (Optional) The start of like query.
	 * @param string $end (Optional) The end of like query.
	 *
	 * @return Query The current query.
	 */
	public function whereLike( $column, $value, $start = '%', $end = '%' ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'like', $this->esc_like( $value, $start, $end ) );
	}

	/**
	 * Creates a where like statement.
	 *
	 *     ->orWhereLike('id', 'value')
	 *
	 * @param string $column The SQL column.
	 * @param string $value Value for like statement.
	 * @param string $start (Optional) The start of like query.
	 * @param string $end (Optional) The end of like query.
	 *
	 * @return Query The current query.
	 */
	public function orWhereLike( $column, $value, $start = '%', $end = '%' ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'like', $this->esc_like( $value, $start, $end ), 'or' );
	}

	/**
	 * Creates a where not like statement.
	 *
	 *     ->whereNotLike('id', 'value' )
	 *
	 * @param string $column The SQL column.
	 * @param mixed $value Value for like statement.
	 * @param string $start (Optional) The start of like query.
	 * @param string $end (Optional) The end of like query.
	 *
	 * @return Query The current query.
	 */
	public function whereNotLike( $column, $value, $start = '%', $end = '%' ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'not like', $this->esc_like( $value, $start, $end ) );
	}

	/**
	 * Creates a where not like statement.
	 *
	 *     ->orWhereNotLike('id', 'value' )
	 *
	 * @param string $column The SQL column.
	 * @param mixed $value Value for like statement.
	 * @param string $start (Optional) The start of like query.
	 * @param string $end (Optional) The end of like query.
	 *
	 * @return Query The current query.
	 */
	public function orWhereNotLike( $column, $value, $start = '%', $end = '%' ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'not like', $this->esc_like( $value, $start, $end ), 'or' );
	}

	/**
	 * Creates a where is null statement.
	 *
	 *     ->whereNull( 'name' )
	 *
	 * @param string $column The SQL column.
	 *
	 * @return Query The current query.
	 */
	public function whereNull( $column ) { // @codingStandardsIgnoreLine
		if ( ! empty( $this->wheres ) ) {
			$this->wheres[] = 'AND';
		}

		$this->wheres[] = "{$column} IS NULL";

		return $this;
	}

	/**
	 * Creates a where is null statement.
	 *
	 *     ->orWhereNull( 'name' )
	 *
	 * @param string $column The SQL column.
	 *
	 * @return Query The current query.
	 */
	public function orWhereNull( $column ) { // @codingStandardsIgnoreLine
		if ( ! empty( $this->wheres ) ) {
			$this->wheres[] = 'OR';
		}

		$this->wheres[] = "{$column} IS NULL";

		return $this;
	}

	/**
	 * Creates a where is not null statement.
	 *
	 *     ->whereNotNull( 'name' )
	 *
	 * @param string $column The SQL column.
	 *
	 * @return Query The current query.
	 */
	public function whereNotNull( $column ) { // @codingStandardsIgnoreLine
		if ( ! empty( $this->wheres ) ) {
			$this->wheres[] = 'AND';
		}

		$this->wheres[] = "{$column} IS NOT NULL";

		return $this;
	}

	/**
	 * Creates a where is not null statement.
	 *
	 *     ->orWhereNotNull( 'name' )
	 *
	 * @param string $column The SQL column.
	 *
	 * @return Query The current query.
	 */
	public function orWhereNotNull( $column ) { // @codingStandardsIgnoreLine
		if ( ! empty( $this->wheres ) ) {
			$this->wheres[] = 'OR';
		}

		$this->wheres[] = "{$column} IS NOT NULL";

		return $this;
	}

	/**
	 * Prepare the value and operator for a where clause.
	 *
	 * @param string $value Value.
	 * @param string $operator Operator.
	 *
	 * @return array
	 */
	private function prepare_value_and_operator( $value, $operator ) {
		if ( is_null( $value ) ) {
			return [ $operator, '=' ];
		}

		return [ $value, $operator ];
	}

	/**
	 * Generate Where clause.
	 *
	 * @param string $column The SQL column.
	 * @param string $operator Operator or value depending if $value is not set.
	 * @param mixed $value The value if $operator is set.
	 * @param string $type The where type ( and, or ).
	 *
	 * @return string
	 */
	protected function generate_where( $column, $operator, $value, $type = 'AND' ) {
		if ( is_array( $value ) ) {
			$value = $this->esc_array( array_unique( $value ) );
			$value = in_array( $operator, [ 'BETWEEN', 'NOT BETWEEN' ], true )
				? join( ' AND ', $value )
				: '(' . join( ', ', $value ) . ')';
		} elseif ( is_scalar( $value ) ) {
			$value = $this->esc_value( $value );
		}

		return join( ' ', [ $type, $column, $operator, $value ] );
	}
}