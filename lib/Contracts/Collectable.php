<?php

namespace JDB\Contracts;

use JDB\Collection;

/**
 * Identifies classes as collectables by collection class.
 */
interface Collectable
{
	/**
	 * Should add the item into the given collection at the given position.
	 */
	public function collectedBy( Collection $collection, int $index ): void;

}
