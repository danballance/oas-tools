<?php

namespace DanBallance\OasTools\FragmentMapper;

use DanBallance\OasTools\Collections\JCollect;
use DanBallance\OasTools\Collections\JCollect3;
use DanBallance\OasTools\Utils\ArrayUtil;
use DanBallance\OasTools\Exceptions\SchemaNotFound;

/**
 * Class FragmentMapper3
 *
 * @package DanBallance\OasTools\FragmentMapper
 */
class FragmentMapper3 extends FragmentMapper implements FragmentMapperInterface
{
    use ArrayUtil;

    /**
     * @param array $schema
     * @return JCollect
     */
    protected function makeCollection(array $schema) : JCollect
    {
        return new JCollect3($schema);
    }

    /**
     * @param string $path
     * @return array
     */
    protected function getPathParts(string $path) : array
    {
        $parts = preg_split('~(?<!\\\)' . preg_quote('/', '~') . '~', $path);
        $parts = array_map(
            function ($part) {
                return str_replace('\/', '/', $part);
            },
            $parts
        );
        array_shift($parts);  // we don't want the leading '#'
        $parts = $this->arrayRemove($parts, 'components');
        if (count($parts) == 1) {
            $parts[] = null;
        }
        return array_values($parts);
    }

    /**
     * @return array
     */
    protected function getSchemas() : array
    {
        return $this->collection->getSchemas()->toArray();
    }

    /**
     * @param string $id
     * @return array
     * @throws SchemaNotFound
     */
    protected function getSchema(string $id) : array
    {
        return $this->collection->getSchema($id)->toArray();
    }

    /**
     * @return array
     */
    protected function getSafeFuncs() : array
    {
        return array_merge(parent::getSafeFuncs(), ['getSchemas', 'getSchema']);
    }
}
