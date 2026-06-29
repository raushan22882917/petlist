<?php

namespace Rtcl\Database\Eloquent;

class Select extends Groupby {

	/**
	 * Make a distinct selection
	 *
	 * @var bool
	 */
	protected $distinct = false;

	/**
	 * Make SQL_CALC_FOUND_ROWS in selection
	 *
	 * @var bool
	 */
	protected $found_rows = false;

	/**
	 * Select parts.
	 *
	 * @var array
	 */
	protected $select = [];

	/**
	 * Reset query.
	 */
	public function reset() {
		parent::reset();
		$this->select     = [];
		$this->distinct   = false;
		$this->found_rows = false;
	}

	/**
	 * Distinct select setter.
	 *
	 * @param bool $distinct Is disticnt.
	 *
	 * @return self The current query.
	 */
	public function distinct( $distinct = true ) {
		$this->distinct = $distinct;

		return $this;
	}

	/**
	 * SQL_CALC_FOUND_ROWS select setter.
	 *
	 * @param bool $found_rows Should get found rows.
	 *
	 * @return self The current query.
	 */
	public function found_rows( $found_rows = true ) {
		$this->found_rows = $found_rows;

		return $this;
	}

	/**
	 * Get found rows.
	 *
	 * @return int
	 */
	public function get_found_rows() {
		return $this->processor->var( 'SELECT FOUND_ROWS();' );
	}

	/**
	 * Get one row.
	 *
	 * @param string $output (Optional) Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants.
	 *
	 * @return mixed
	 */
	public function one( string $output = OBJECT ) {
		$this->limit( 1 );
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
//		$row = $wpdb->get_row( $this->get_query( Query::TYPE_SELECT ), $output );
		$this->reset();
//		return $row;
	}

	/**
	 * Get one column.
	 *
	 * @return string|null
	 */
	public function var() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
//		$var = $wpdb->get_var( $this->get_query( Query::TYPE_SELECT ) ); // WPCS: unprepared SQL ok.
		$this->reset();
//		return $var;
	}

	/**
	 * Set the selected fields.
	 *
	 * @param array $fields Fields to select.
	 *
	 * @return self The current query.
	 */
	public function select( $fields = '' ) {
		if ( empty( $fields ) ) {
			return $this;
		}

		if ( is_string( $fields ) ) {
			$this->select[] = $fields;

			return $this;
		}

		foreach ( $fields as $key => $field ) {
			$this->select[] = is_string( $key )
				? $this->wrap_alias( $key, $field )
				: $field;
		}

		return $this;
	}

	/**
	 * Shortcut to add a count function.
	 *
	 *     ->selectCount('id')
	 *     ->selectCount('id', 'count')
	 *
	 * @param string $field Column name.
	 * @param string $alias (Optional) Column alias.
	 *
	 * @return self The current query.
	 */
	public function selectCount( $field = '*', $alias = null ) { // @codingStandardsIgnoreLine
		return $this->selectFunc( 'COUNT', $field, $alias );
	}

	/**
	 * Shortcut to add a sum function.
	 *
	 *     ->selectSum('id')
	 *     ->selectSum('id', 'total')
	 *
	 * @param string $field Column name.
	 * @param string $alias (Optional) Column alias.
	 *
	 * @return self The current query.
	 */
	public function selectSum( $field, $alias = null ) { // @codingStandardsIgnoreLine
		return $this->selectFunc( 'SUM', $field, $alias );
	}

	/**
	 * Shortcut to add a avg function.
	 *
	 *     ->selectAvg('id')
	 *     ->selectAvg('id', 'average')
	 *
	 * @param string $field Column name.
	 * @param string $alias (Optional) Column alias.
	 *
	 * @return self The current query.
	 */
	public function selectAvg( $field, $alias = null ) { // @codingStandardsIgnoreLine
		return $this->selectFunc( 'AVG', $field, $alias );
	}

	/**
	 * Shortcut to add a max function.
	 *
	 *     ->selectMax('id')
	 *     ->selectMax('id', 'average')
	 *
	 * @param string $field Column name.
	 * @param string $alias (Optional) Column alias.
	 *
	 * @return self The current query.
	 */
	public function selectMax( $field, $alias = null ) { // @codingStandardsIgnoreLine
		return $this->selectFunc( 'MAX', $field, $alias );
	}

	/**
	 * Shortcut to add a min function.
	 *
	 *     ->selectMin('id')
	 *     ->selectMin('id', 'average')
	 *
	 * @param string $field Column name.
	 * @param string $alias (Optional) Column alias.
	 *
	 * @return self The current query.
	 */
	public function selectMin( $field, $alias = null ) { // @codingStandardsIgnoreLine
		return $this->selectFunc( 'MIN', $field, $alias );
	}

	/**
	 * Shortcut to add a function.
	 *
	 * @param string $func  Function name.
	 * @param string $field Column name.
	 * @param string $alias (Optional) Column alias.
	 *
	 * @return self The current query.
	 */
	private function selectFunc( $func, $field, $alias = null ) { // @codingStandardsIgnoreLine
		$this->select[] = $this->wrap_alias( "$func({$field})", $alias );

		return $this;
	}
}