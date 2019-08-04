<?php

namespace DanBallance\OasTools\Specification\Adapters;

use DanBallance\OasTools\Specification\BaseSpecification3;
use DanBallance\OasTools\Collections\JCollect3;

class AdapterJCollect3 extends BaseSpecification3
{
    protected function makeSchema(array $array)
    {
        return new JCollect3($array);
    }
}
