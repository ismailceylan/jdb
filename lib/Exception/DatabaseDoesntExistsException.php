<?php

namespace JDB\Exception;

/**
 * It handles when the database is not exists.
 */
class DatabaseDoesntExistsException extends \Exception
{
	public function __construct( string $dbname )
	{
		parent::__construct(
			"\"$dbname\" database doesn't exists."
		);
	}
}
