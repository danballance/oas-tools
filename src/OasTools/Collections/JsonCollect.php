<?php

namespace DanBallance\OasTools\Collections;

use Jshannon63\JsonCollect\JsonCollect as Collection;

/**
 * This class simply extends Jshannon63\JsonCollect\JsonCollect
 * and enables the CollectionInterface to be added for type checks elsewhere
 */
class JsonCollect extends Collection implements CollectionInterface
{
    /**
     * Tightenco\Collect\Support\Collection::toArray() does not have return type
     * So have wrapped it below in order to meet requirements of CollectionInterface
     */
    public function toArray() : array
    {
        return parent::toArray();
    }
}
