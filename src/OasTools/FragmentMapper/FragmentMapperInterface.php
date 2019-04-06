<?php

namespace DanBallance\OasTools\FragmentMapper;

use DanBallance\OasTools\Specification\Fragment;

/**
 * Interface FragmentMapperInterface
 *
 * @package DanBallance\OasTools\FragmentMapper
 */
interface FragmentMapperInterface
{
    /**
     * @param Fragment $schemaPart
     * @return string
     */
    public function schemaToPath(Fragment $schemaPart) : string;

    /**
     * @param string $path
     * @return Fragment
     */
    public function pathToSchema(string $path) : Fragment;
}
