<?php

namespace JDB;

/**
 * Handles pagination works for collections.
 */
class PaginatedCollection
{
	/**
	 * Pagination properties.
	 */
	public array $data = [];

	/**
	 * PaginatedCollection constructor.
	 */
	public function __construct(
		public Collection $collection,
		public int $perPage = 10
	)
	{
		$this->data[ 'current_page' ] = $this->page();
		$this->data[ 'data' ] = $this->data();
		$this->data[ 'from' ] = $this->from();
		$this->data[ 'total' ] = $this->total();
		$this->data[ 'last_page' ] = $this->lastPage();
		$this->data[ 'per_page' ] = $perPage;
		$this->data[ 'to' ] = $this->to();
	}

	/**
	 * It's a short way for extend method.
	 */
	public function __set( string $key, mixed $val )
	{
		$this->extend( $key, $val );
	}

	/**
	 * Adds a new property among the pagination properties.
	 */
	public function extend( string $key, mixed $val ): static
	{
		$this->data[ $key ] = $val;
		return $this;
	}

	/**
	 * Returns current page number.
	 */
	public function page(): int
	{
		return max( 1, ((int) $_GET[ 'page' ]) ?? 1 );
	}

	/**
	 * Returns the items within the current page number and
	 * limit settings.
	 */
	public function data(): array
	{
		return $this
			->collection
			->slice(
				$this->from() - 1,
				$this->to() - $this->from() + 1
			)
			->toArray();
	}

	/**
	 * Calculates the first item's real position according to
	 * the current page number and limit settings.
	 */
	public function from(): int
	{
		return $this->perPage * ( $this->page() - 1 ) + 1;
	}

	/**
	 * Returns the count of all items.
	 */
	public function total(): int
	{
		return $this->collection->length;
	}

	/**
	 * Calculates how many pages should be total.
	 */
	public function lastPage(): int
	{
		return max( 1, ceil( $this->collection->length / $this->perPage ))	;
	}

	/**
	 * Calculates the last item's real position according to
	 * the current page number and limit settings.
	 */
	public function to(): int
	{
		return min( $this->total(), $this->from() + $this->perPage - 1 );
	}

	/**
	 * Tells if there are items in the collection for the
	 * current page number and limit settings.
	 */
	public function isEmpty(): bool
	{
		return count( $this->data[ 'data' ]) === 0;
	}

	/**
	 * Encodes pagination properties into json string.
	 */
	public function toJson(): string
	{
		return json_encode( $this->data );
	}

	/**
	 * Returns pagination properties as array.
	 */
	public function toArray(): array
	{
		return (array) $this->data;
	}

}
