<?php

namespace Rtcl\Database\Eloquent;

use Rtcl\Helpers\Arr;

class Paginator {


	/**
	 * All of the items being paginated.
	 *
	 * @var Collection
	 */
	protected $items;

	/**
	 * The number of items to be shown per page.
	 *
	 * @var int
	 */
	protected $perPage;

	/**
	 * The current page being "viewed".
	 *
	 * @var int
	 */
	protected $currentPage;

	/**
	 * The paginator options.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * The current path resolver callback.
	 *
	 * @var \Closure
	 */
	protected static $currentPathResolver;


	/**
	 * The total number of items before slicing.
	 *
	 * @var int
	 */
	protected $total;

	/**
	 * The last available page.
	 *
	 * @var int
	 */
	protected $lastPage;


	/**
	 * The query parameters to add to all URLs.
	 *
	 * @var array
	 */
	protected $query = [];

	/**
	 * The URL fragment to add to all URLs.
	 *
	 * @var string|null
	 */
	protected $fragment;

	/**
	 * The base path to assign to all URLs.
	 *
	 * @var string
	 */
	protected $path = '/';

	/**
	 * The query string variable used to store the page.
	 *
	 * @var string
	 */
	protected $pageName = 'page';

	/**
	 * Create a new paginator instance.
	 *
	 * @param mixed $items
	 * @param int $total
	 * @param int $perPage
	 * @param int|null $currentPage
	 * @param array $options (path, query, fragment, pageName)
	 *
	 * @return void
	 */
	public function __construct( $items, $total, $perPage, $currentPage = null, array $options = [] ) {
		$this->options = $options;

		foreach ( $options as $key => $value ) {
			$this->{$key} = $value;
		}

		$this->total       = $total;
		$this->perPage     = (int) $perPage;
		$this->lastPage    = max( (int) ceil( $total / $perPage ), 1 );
		$this->path        = $this->path !== '/' ? rtrim( $this->path, '/' ) : $this->path;
		$this->currentPage = $this->setCurrentPage( $currentPage, $this->pageName );
		$this->items       = $items instanceof Collection ? $items : Collection::make( $items );
	}

	/**
	 * Add a set of query string values to the paginator.
	 *
	 * @param array|string|null $key
	 * @param string|null $value
	 *
	 * @return $this
	 */
	public function appends( $key, $value = null ) {
		if ( is_null( $key ) ) {
			return $this;
		}

		if ( is_array( $key ) ) {
			return $this->appendArray( $key );
		}

		return $this->addQuery( $key, $value );
	}

	/**
	 * Add an array of query string values.
	 *
	 * @param array $keys
	 *
	 * @return $this
	 */
	protected function appendArray( array $keys ) {
		foreach ( $keys as $key => $value ) {
			$this->addQuery( $key, $value );
		}

		return $this;
	}

	/**
	 * Add all current query string values to the paginator.
	 *
	 * @return $this
	 */
	public function withQueryString() {
		if ( isset( static::$queryStringResolver ) ) {
			return $this->appends( call_user_func( static::$queryStringResolver ) );
		}

		return $this;
	}

	/**
	 * Add a query string value to the paginator.
	 *
	 * @param string $key
	 * @param string $value
	 *
	 * @return $this
	 */
	protected function addQuery( $key, $value ) {
		if ( $key !== $this->pageName ) {
			$this->query[ $key ] = $value;
		}

		return $this;
	}


	/**
	 * Build the full fragment portion of a URL.
	 *
	 * @return string
	 */
	protected function buildFragment() {
		return $this->fragment ? '#' . $this->fragment : '';
	}

	public static function resolveCurrentPath( $default = '/' ) {
		if ( isset( static::$currentPathResolver ) ) {
			return call_user_func( static::$currentPathResolver );
		}

		return $default;
	}

	/**
	 * Get the current page for the request.
	 *
	 * @param int $currentPage
	 * @param string $pageName
	 *
	 * @return int
	 */
	protected function setCurrentPage( $currentPage, $pageName ) {
		$currentPage = $currentPage ?: static::resolveCurrentPage( $pageName );

		return $this->isValidPageNumber( $currentPage ) ? (int) $currentPage : 1;
	}

	/**
	 * Determine if the given value is a valid page number.
	 *
	 * @param int $page
	 *
	 * @return bool
	 */
	protected function isValidPageNumber( $page ) {
		return $page >= 1 && filter_var( $page, FILTER_VALIDATE_INT ) !== false;
	}


	/**
	 * Get the total number of items being paginated.
	 *
	 * @return int
	 */
	public function total() {
		return $this->total;
	}

	/**
	 * Determine if there are more items in the data source.
	 *
	 * @return bool
	 */
	public function hasMorePages() {
		return $this->currentPage() < $this->lastPage();
	}

	/**
	 * Get the URL for the next page.
	 *
	 * @return string|null
	 */
	public function nextPageUrl() {
		if ( $this->hasMorePages() ) {
			return $this->url( $this->currentPage() + 1 );
		}
	}

	/**
	 * Get the last page.
	 *
	 * @return int
	 */
	public function lastPage() {
		return $this->lastPage;
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray() {
		return [
			'items'      => $this->items->toArray(),
			'pagination' => [
				'current_page' => $this->currentPage(),
				'first_page_url' => $this->url( 1 ),
				'from'           => $this->firstItem(),
				'last_page'      => $this->lastPage(),
				'last_page_url'  => $this->url( $this->lastPage() ),
				'next_page_url'  => $this->nextPageUrl(),
				'path'           => $this->path(),
				'per_page'       => $this->perPage(),
				'prev_page_url'  => $this->previousPageUrl(),
				'to'             => $this->lastItem(),
				'total'          => $this->total(),
			]
		];
	}

	/**
	 * Get the base path for paginator generated URLs.
	 *
	 * @return string|null
	 */
	public function path() {
		return $this->path;
	}

	/**
	 * Get the URL for a given page number.
	 *
	 * @param int $page
	 *
	 * @return string
	 */
	public function url( $page ) {
		if ( $page <= 0 ) {
			$page = 1;
		}

		// If we have any extra query string key / value pairs that need to be added
		// onto the URL, we will put them in query string form and then attach it
		// to the URL. This allows for extra information like sortings storage.
		$parameters = [ $this->pageName => $page ];

		if ( count( $this->query ) > 0 ) {
			$parameters = array_merge( $this->query, $parameters );
		}

		return $this->path()
		       . ( str_contains( $this->path(), '?' ) ? '&' : '?' )
		       . Arr::query( $parameters )
		       . $this->buildFragment();
	}

	/**
	 * Get the number of items for the current page.
	 *
	 * @return int
	 */
	public function count(): int {
		return $this->items->count();
	}

	/**
	 * Get the slice of items being paginated.
	 *
	 * @return array
	 */
	public function items() {
		return $this->items->all();
	}

	/**
	 * Get the number of the first item in the slice.
	 *
	 * @return int
	 */
	public function firstItem() {
		return count( $this->items() ) > 0 ? ( $this->currentPage - 1 ) * $this->perPage + 1 : null;
	}

	/**
	 * Get the number of the last item in the slice.
	 *
	 * @return int
	 */
	public function lastItem() {
		return count( $this->items() ) > 0 ? $this->firstItem() + $this->count() - 1 : null;
	}

	/**
	 * Transform each item in the slice of items using a callback.
	 *
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function through( callable $callback ) {
		$this->items->transform( $callback );

		return $this;
	}

	/**
	 * Get the number of items shown per page.
	 *
	 * @return int
	 */
	public function perPage() {
		return $this->perPage;
	}

	/**
	 * Determine if there are enough items to split into multiple pages.
	 *
	 * @return bool
	 */
	public function hasPages() {
		return $this->currentPage() != 1 || $this->hasMorePages();
	}

	/**
	 * Determine if the paginator is on the first page.
	 *
	 * @return bool
	 */
	public function onFirstPage() {
		return $this->currentPage() <= 1;
	}

	/**
	 * Determine if the paginator is on the last page.
	 *
	 * @return bool
	 */
	public function onLastPage() {
		return ! $this->hasMorePages();
	}

	/**
	 * Get the current page.
	 *
	 * @return int
	 */
	public function currentPage() {
		return $this->currentPage;
	}

	/**
	 * Get the query string variable used to store the page.
	 *
	 * @return string
	 */
	public function getPageName() {
		return $this->pageName;
	}

	/**
	 * Get the URL for the previous page.
	 *
	 * @return string|null
	 */
	public function previousPageUrl() {
		if ( $this->currentPage() > 1 ) {
			return $this->url( $this->currentPage() - 1 );
		}
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
	 * Convert the object to its JSON representation.
	 *
	 * @param int $options
	 *
	 * @return string
	 */
	public function toJson( $options = 0 ) {
		return wp_json_encode( $this->jsonSerialize(), $options );
	}
}