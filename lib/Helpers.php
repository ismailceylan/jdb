<?php

namespace JDB;

/**
 * Some helpers.
 */
class Helpers
{
	/**
	 * It ensures that the directory separator character in the
	 * given directory is compatible with the current operating
	 * system.
	 */
	public static function normalizePath( string $path ): string
	{
		return str_replace( '/', DIRECTORY_SEPARATOR, $path );
	}

}
