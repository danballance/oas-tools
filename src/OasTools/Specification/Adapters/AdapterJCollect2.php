<?php

namespace DanBallance\OasTools\Specification\Adapters;

use DanBallance\OasTools\Specification\BaseSpecification2;
use DanBallance\OasTools\Collections\CollectionInterface;
use DanBallance\OasTools\Collections\JCollect2;

class AdapterJCollect2 extends BaseSpecification2
{
    protected function makeSchema(array $array)
    {
        return new JCollect2($array);
    }
}
