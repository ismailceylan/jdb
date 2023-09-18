<?php

namespace JDB;

use stdClass;
use JDB\Contracts\Collectable;

/**
 * Handles database rows.
 */
class Row implements Collectable
{
	public array $collectionStack = [];

	/**
	 * Row constructor.
	 */
	public function __construct(
		public Table $table,
		public Collection $collection,
		public stdClass $original,
		public int $index
	)
	{
		$this->collectedBy( $collection, $index );
	}

	/**
	 * Returns the value of a field. Returns null if the field
	 * is not defined.
	 */
	public function __get( string $key ): mixed
	{
		return $this->original->{ $key } ?? null;
	}

	/**
	 * Creates a cell or overwrites its value.
	 */
	public function __set( string $key, mixed $val ): void
	{
		$this->original->{ $key } = $val;
		$this->table->isDirty = true;
	}

	/**
	 * It's a trap for the isset method and returns
	 * whether a cell is defined or not.
	 * 
	 * ```php
	 * isset( $row->foo );
	 * ```
	 */
	public function __isset( string $name ): bool
	{
		return isset( $this->original->{ $name });
	}

	/**
	 * It is a trap for the unset method and deletes a
	 * cell from the row.
	 *
	 * ```php
	 * unset( $row->foo );
	 * ```
	 */
	public function __unset( string $fieldName ): void
	{
		unset( $this->original->{ $fieldName });
		$this->table->isDirty = true;
	}

	/**
	 * Adds to the collection list the row is in.
	 */
	public function collectedBy( Collection $collection, int $index ): void
	{
		$this->collectionStack[] = [ $collection, $index ];
	}

	/**
	 * Returns row as key => value array.
	 */
	public function toArray(): array
	{
		return (array) $this->original;
	}

	/**
	 * Returns row as stdClass object.
	 */
	public function toObject(): stdClass
	{
		return $this->original;
	}

	/**
	 * Encodes row as json string.
	 */
	public function toJson(): string
	{
		return json_encode( $this->original );
	}

	/**
	 * Removes given field from the row.
	 */
	public function remove( string $field ): self
	{
		unset( $this->{ $field });
		return $this;
	}

	/**
	 * Renames given field to another name.
	 */
	public function rename( string $targetField, string $newName ): self
	{
		if( property_exists( $this->original, $targetField ))
		{
			$this->{ $newName } = $this->{ $targetField };
			$this->remove( $targetField );
		}

		return $this;
	}

	/**
	 * Deletes the row from the table it belongs to and
	 * any collections it is in.
	 */
	public function delete(): self
	{
		foreach( $this->collectionStack as $item )
		{
			[ $collection, $index ] = $item;
			$collection->forget( $index );
		}

		$this->table->meta->rows--;
		$this->table->isDirty = true;

		return $this;
	}

}
