<?php

namespace JDB;

/**
 * Manages json based metadata files for third parties.
 */
class Meta
{
	/**
	 * Current values.
	 */
	public array $currents = [];

	/**
	 * Overwrite stack.
	 */
	public array $overwrites = [];

	/**
	 * Creates new meta interface.
	 */
	public function __construct(
		public ?string $file = null
	)
	{
		if( $file )
		{
			$this->load( $file );
		}
	}

	/**
	 * Sets a key.
	 */
	public function __set( string $key, string|int $val )
	{
		$this->overwrites[ $key ] = $val;
	}

	/**
	 * Attempts to return the value of a key via overwrites. If the
	 * key has not yet been overwritten, its current value in the
	 * file is returned.
	 */
	public function __get( string $key ): string|int
	{
		return $this->overwrites[ $key ] ?? $this->currents[ $key ];
	}

	/**
	 * Loads the meta file.
	 */
	public function load( string $file ): void
	{
		$this->currents = (array) json_decode(
			file_get_contents( $file )
		);
	}

	/**
	 * Tells if any metadata has been changed.
	 */
	public function isDirty(): bool
	{
		return count( $this->overwrites ) > 0;
	}
	
	/**
	 * Puts the final version of the metadata into the meta file.
	 * 
	 * If there is no change in the metafile, the save is not
	 * performed and returns null.
	 * 
	 * Returns true when the metafile is successfully saved and
	 * false if an error occurs during the save.
	 */
	public function save( string $file = null ): null|bool
	{
		if( ! $this->isDirty())
		{
			return null;
		}
		
		$file = $file ?? $this->file;

		$this->load( $file );
		
		foreach( $this->overwrites as $key => $val )
		{
			$this->currents[ $key ] = $val;
		}

		$this->overwrites = [];

		$put = file_put_contents(
			$file,
			json_encode( $this->currents )
		);

		return $put === false
			? false
			: true;
	}

	/**
	 * Undoes all changes that have not been saved so far.
	 */
	public function rollback(): void
	{
		$this->overwrites = [];
	}

}
