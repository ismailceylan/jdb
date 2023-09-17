<?php

namespace JDB;

class JDB
{
	/**
	 * Table extensions.
	 */
	public const DBEXT = 'json';

	/**
	 * Table meta extensions.
	 */
	public const METAEXT = 'meta.json';

	/**
	 * An alias to Database::create method.
	 */
	public static function createDatabase( string $name ): Database
	{
		return Database::create( $name );
	}

	/**
	 * An alias to Database::connect method.
	 */
	public static function connect( string $name ): Database
	{
		return Database::connect( $name );
	}

	/**
	 * Returns folder names in given path.
	 */
	public static function databases( string $path ): array
	{
		foreach( glob( Helpers::normalizePath( $path . '/*' )) as $item )
		{
			$stack[] = pathinfo( $item )[ 'basename' ];
		}

		return $stack ?? [];
	}

}
