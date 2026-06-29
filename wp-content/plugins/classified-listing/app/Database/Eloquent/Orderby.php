<?php

namespace Rtcl\Database\Eloquent;

class Orderby extends Where {
	/**
	 * Orderby statements
	 *
	 * @var array
	 */
	protected $orders = [];

	/**
	 * Get order clause.
	 *
	 * @return string
	 */
	public function get_order_clauses() {
		$build = [];

		foreach ( $this->orders as $column => $direction ) {

			// in case a raw value is given we had to
			// put the column / raw value an direction inside another
			// array because we cannot make objects to array keys.
			if ( is_array( $direction ) ) {
				[ $column, $direction ] = $direction;
			}

			if ( ! empty( $direction ) ) {
				$column .= ' ' . $direction;
			}

			$build[] = $column;
		}

		return 'ORDER BY ' . join( ', ', $build );
	}

	/**
	 * Add an order by statement to the current query.
	 *
	 *     ->orderBy('created_at')
	 *     ->orderBy('modified_at', 'desc')
	 *
	 *     Multiple order statements
	 *     ->orderBy(['firstname', 'lastname'], 'desc')
	 *
	 *     Muliple order statements with diffrent directions
	 *     ->orderBy(['firstname' => 'asc', 'lastname' => 'desc'])
	 *
	 * @param array|string $columns Columns.
	 * @param string $direction Direction.
	 *
	 * @return Query The current query.
	 */
	public function orderBy( $columns, $direction = 'ASC' ) { // @codingStandardsIgnoreLine
		if ( is_string( $columns ) ) {
			$columns = $this->argument_to_array( $columns );
		}

		foreach ( $columns as $key => $column ) {
			if ( is_numeric( $key ) ) {
				$this->orders[ $column ] = strtoupper( (string) $direction );
			} else {
				$this->orders[ $key ] = strtoupper( $column );
			}
		}

		return $this;
	}

	/**
	 * Returns an string argument as parsed array if possible
	 *
	 * @param string $argument Argument to validate.
	 *
	 * @return array
	 */
	protected function argument_to_array( $argument ) {
		if ( false !== strpos( $argument, ',' ) ) {
			return array_map( 'trim', explode( ',', $argument ) );
		}

		return [ $argument ];
	}
}