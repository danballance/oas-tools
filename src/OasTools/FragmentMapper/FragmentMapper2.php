<?php

namespace DanBallance\OasTools\FragmentMapper;

use DanBallance\OasTools\Collections\JCollect;
use DanBallance\OasTools\Collections\JCollect2;
use DanBallance\OasTools\Exceptions\SchemaNotFound;

/**
 * Class FragmentMapper2
 *
 * @package DanBallance\OasTools\FragmentMapper
 */
class FragmentMapper2 extends FragmentMapper implements FragmentMapperInterface
{
    /**
     * @param array $schema
     * @return JCollect
     */
    protected function makeCollection(array $schema) : JCollect
    {
        return new JCollect2($schema);
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
        array_shift($parts);  // we don't want the leading '#' or '$'
        if (count($parts) == 1) {
            $parts[] = null;
        }
        return $parts;
    }

    /**
     * @return array
     */
    protected function getDefinitions() : array
    {
        return $this->collection->getSchemas()->toArray();
    }

    /**
     * @param string $id
     * @return array
     * @throws SchemaNotFound
     */
    protected function getDefinition(string $id) : array
    {
        return $this->collection->getSchema($id)->toArray();
    }

    /**
     * @return array
     */
    protected function getSafeFuncs() : array
    {
        return array_merge(parent::getSafeFuncs(), ['getDefinitions', 'getDefinition']);
    }
}
