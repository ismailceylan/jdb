<?php

namespace JDB\Exception;

/**
 * It handles table is already exists.
 */
class TableExistsException extends \Exception
{
	public function __construct( string $tableName )
	{
		parent::__construct(
			"$tableName table already exists."
		);
	}
}
