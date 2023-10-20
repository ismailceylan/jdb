<?php

namespace JDB\Exception;

/**
 * It handles when the table is not exists.
 */
class TableDoesntExistsException extends \Exception
{
	public function __construct( string $tableName )
	{
		parent::__construct(
			"$tableName table doesn't exists."
		);
	}
}
