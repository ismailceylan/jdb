<?php

namespace JDB\Exception;

/**
 * It handles when the database already exists.
 */
class DatabaseExistsException extends \Exception
{
	public function __construct( string $dbname )
	{
		parent::__construct(
			"\"$dbname\" database already exists."
		);
	}
}
