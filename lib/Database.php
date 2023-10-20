<?php

namespace JDB;

use JDB\Exception\DatabaseDoesntExistsException;
use JDB\Exception\DatabaseExistsException;
use JDB\Exception\FileSystemException;
use JDB\Exception\TableExistsException;
use JDB\Exception\NameAlreadyUsedException;
use JDB\Exception\TableDoesntExistsException;

/**
 * Represents databases and handle tables works.
 */
class Database
{
	/**
	 * Database's meta data manager.
	 */
	private Meta $meta;
	
	/**
	 * Database full path.
	 */
	public string $path;

	/**
	 * The parent directory that holds the database.
	 */
	public string $dir;

	/**
	 * Database constructor.
	 */
	public function __construct(
		/**
		 * Database full path. The latest segment will be parsed as database name.
		 */
		public string $name
	)
	{
		$pinfo = pathinfo( $this->name );

		$this->meta = new Meta;
		$this->name = $pinfo[ 'basename' ];
		$this->dir = Helpers::normalizePath( $pinfo[ 'dirname' ]) . DIRECTORY_SEPARATOR;;
		$this->path = Helpers::normalizePath( $name ) . DIRECTORY_SEPARATOR;
	}

	/**
	 * Creates a database.
	 * 
	 * @throws FileSystemException when filesystem related issues occured
	 * @throws DatabaseExistsException when database already exists
	 */
	public static function create( string $name ): Database
	{
		if( self::exists( $name ))
		{
			throw new DatabaseExistsException( $name );
		}

		if( ! mkdir( $name, 0777, true ))
		{
			throw new FileSystemException( "\"$name\" database can not created!" );
		}
		
		return self::connect( $name );
	}

	/**
	 * Returns whether the database exists. 
	 */
	public static function exists( string $name ): bool
	{
		return is_dir( $name );
	}

	/**
	 * Returns database object.
	 *
	 * @throws DatabaseDoesntExistsException when database doesn't exists
	 */
	public static function connect( string $name ): Database
	{
		if( ! self::exists( $name ))
		{
			throw new DatabaseDoesntExistsException( $name );
		}

		return new self( $name );
	}

	/**
	 * Returns whether the given table is exists or not.
	 */
	public function tableExists( string $tableName ): bool
	{
		return file_exists(
			$this->path . $tableName . '.' . JDB::DBEXT
		);
	}
	
	/**
	 * Creates a table.
	 *
	 * @throws TableExistsException when the table already exists
	 */
	public function createTable( string $tableName ): void
	{
		$dataFile = $this->path . $tableName . '.' . JDB::DBEXT;
		$metaFile = $this->path . $tableName . '.' . JDB::METAEXT;

		if( file_exists( $dataFile ))
		{
			throw new TableExistsException( "$this->name.$tableName" );
		}
		
		file_put_contents( $dataFile, '[]' );
		file_put_contents( $metaFile, '[]' );

		$this->meta->rows = 0;
		$this->meta->current_id = 0;
		$this->meta->save( $metaFile );
	}

	/**
	 * Returns table object.
	 *
	 * @throws TableDoesntExistsException when table doesn't exists
	 */
	public function table( string $tableName ): Table
	{
		$path = $this->path . $tableName . '.' . JDB::DBEXT;

		if( ! file_exists( $path ))
		{
			throw new TableDoesntExistsException( "$this->name.$tableName" );
		}

		return new Table( $this, $tableName );
	}

	/**
	 * Returns database's tables.
	 */
	public function tables(): array
	{
		foreach( glob( $this->path . '*' . JDB::METAEXT ) as $item )
		{
			$stack[] = explode( '.', pathinfo( $item )[ 'basename' ])[ 0 ];
		}

		return $stack ?? [];
	}

	/**
	 * Returns database time infos.
	 */
	public function times(): array
	{
		return [
			'created' => filectime( $this->path ),
			'accessed' => fileatime( $this->path ),
			'modified' => filemtime( $this->path )
		];
	}

	/**
	 * Returns total size of the db.
	 */
	public function size(): int
	{
		clearstatcache( false, $this->path );
		return filesize( $this->path );
	}

	/**
	 * Renames the database.
	 *
	 * @throws NameAlreadyUsedException when the given name is already in use
	 * @throws FileSystemException when native rename method fails
	 */
	public function rename( string $newName ): bool
	{
		$newPath = $this->dir . $newName;

		if( is_dir( $newPath ))
		{
			throw new NameAlreadyUsedException(
				"Because of the $newName already in use, " .
				"$this->name database can not renamed to $newName."
			);
		}

		if( ! ( @rename( $this->path, $newPath )))
		{
			throw new FileSystemException( 'Database can not renamed.' );
		}

		return true;
	}

}
