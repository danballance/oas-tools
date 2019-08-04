<?php

namespace DanBallance\OasTools\FragmentMapper;

use DanBallance\OasTools\Specification\Fragments\FragmentInterface;

interface FragmentMapperInterface
{
    public function schemaToPath(FragmentInterface $schemaPart) : string;
    public function pathToSchema(string $path) : FragmentInterface;
}
