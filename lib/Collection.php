<?php

namespace JDB;

use JDB\Contracts\Collectable;
use JDB\Support\Arrayify;

/**
 * Handles arrays as collections.
 */
class Collection implements \Iterator, \ArrayAccess
{
	use Arrayify;

	/**
	 * Collection length.
	 */
	public int $length = 0;

	/**
	 * Items.
	 */
	public array $data = [];

	/**
	 * Collection constructor.
	 */
	public function __construct( array $data = [])
	{
		$this->push( ...$data );
	}

	/**
	 * Adds a new item to the collection.
	 */
	public function push( ...$items ): static
	{
		foreach( $items as $item )
		{
			$this->data[] = $item;
			
			if( $item instanceof Collectable )
			{
				$item->collectedBy( $this, $this->length );
			}

			$this->length++;
		}

		return $this;
	}

	/**
	 * Returns a new collection starting from the first item in
	 * the current collection and containing the given number
	 * of items.
	 */
	public function take( int $items ): Collection
	{
		return new static(
			array_slice( $this->data, 0, max( 1, $items ))
		);
	}

	/**
	 * Skips the given number of items. It returns a new
	 * collection.
	 */
	public function skip( int $items ): Collection
	{
		return $this->slice( $items );
	}

	/**
	 * Cuts items from the given position, takes given
	 * number of items and returns a new collection.
	 */
	public function slice( int $from, int $length = null ): Collection
	{
		return new static(
			array_slice( $this->data, $from, $length, true )
		);
	}

	/**
	 * Returns true if the collection is empty.
	 */
	public function isEmpty(): bool
	{
		return count( $this->data ) === 0;
	}

	/**
	 * Encodes collection to json object.
	 */
	public function toJson(): string
	{
		return json_encode( $this->toArray());
	}

	/**
	 * Converts collection to plain array.
	 */
	public function toArray(): array
	{
		$stack = [];

		foreach( $this->data as $item )
		{
			if( method_exists( $item, 'toArray' ))
			{
				$stack[] = $item->toArray();
			}
			else
			{
				$stack[] = $item;
			}
		}

		return $stack;
	}

	/**
	 * Passes elements as arguments to a given callable
	 * method. Returns a new collection of elements that
	 * return a true result from Callable.
	 */
	public function filter( callable $cb ): Collection
	{
		$stack = new static;

		foreach( $this->data as $index => $item )
		{
			if( $cb( $item, $index, $stack ) === true )
			{
				$stack->push( $item );
			}
		}

		return $stack;
	}

	/**
	 * Passes all items in the collection one by one to
	 * the given callable method.
	 */
	public function each( callable $cb ): static
	{
		foreach( $this->data as $index => $item )
		{
			$cb( $item, $index, $this );
		}

		return $this;
	}

	/**
	 * Passes all items in the collection to the given callable. The
	 * return value from this callable is placed same place in a new
	 * collection and the old collection stay as not mutated.
	 */
	public function map( callable $cb ): static
	{
		$stack = new static;

		foreach( $this->data as $index => $item )
		{
			$stack->push( $cb( $item, $index, $this ));
		}

		return $stack;
	}

	/**
	 * Removes the item that living in the given position.
	 */
	public function forget( int $index ): static
	{
		unset( $this->data[ $index ]);

		$this->length--;

		return $this;
	}

	/**
	 * Returns the item with the given id value. Returns null
	 * if the item does not exist.
	 */
	public function find( string|int $id ): null|Row
	{
		$result = $this->filter( fn( Row $item ) =>
			$item->id === (int) $id
		);

		return $result->first();
	}

	/**
	 * Returns first row in the collection. If the
	 * collection is empty then null will be returned.
	 */
	public function first(): null|Row
	{
		return $this->isEmpty()
			? null
			: $this->data[ 0 ];
	}

	/**
	 * Paginates the current collection.
	 */
	public function paginate( int $limit = 10 ): PaginatedCollection
	{
		return new PaginatedCollection( $this, $limit );
	}

}
