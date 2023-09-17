<?php

namespace JDB\Support;

/**
 * It makes classes array-ish.
 */
trait Arrayify
{
	/**
	 * Current iteration position.
	 */
	public $position = 0;

	/**
	 * Updates the key.
	 */
	public function offsetSet( mixed $offset, mixed $value ): void
	{
        $this->data[ $offset ] = $value;
    }

	/**
	 * Returns key exists.
	 */
    public function offsetExists( mixed $offset ): bool
	{
        return isset( $this->data[ $offset ]);
    }

	/**
	 * Removes key.
	 */
    public function offsetUnset( mixed $offset ): void
	{
        unset( $this->data[ $offset ]);
    }

	/**
	 * Returns key's value.
	 */
    public function offsetGet( mixed $offset ): mixed
	{
        return isset( $this->data[ $offset ])
			? $this->data[ $offset ]
			: null;
    }

	/**
	 * Returns current array item.
	 */
	public function current(): mixed
	{
		return $this->data[ $this->position ];
	}

	/**
	 * Moves the cursor to the next element.
	 */
	public function next(): void
	{
		$this->position++;
	}

	/**
	 * Returns the current cursor position points to
	 * something exists.
	 */
	public function valid(): bool
	{
		return isset( $this->data[ $this->position ]);
	}

	/**
	 * Returns current cursor position.
	 */
	public function key(): mixed
	{
		return $this->position;
	}

	/**
	 * Restarts the cursor.
	 */
	public function rewind(): void
	{
		$this->position = 0;
	}

}
