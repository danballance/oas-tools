<?php

namespace DanBallance\OasTools\FragmentMapper;

use DanBallance\OasTools\Exceptions\SchemaNotFound;
use DanBallance\OasTools\Specification\Specification2Interface;
use DanBallance\OasTools\Specification\Fragment2;

/**
 * Class FragmentMapper2
 *
 * @package DanBallance\OasTools\FragmentMapper
 */
class FragmentMapper2 extends FragmentMapper implements FragmentMapperInterface
{
    /**
     * FragmentMapper constructor.
     * @param Specification2Interface $schema
     */
    public function __construct(Specification2Interface $schema)
    {
        $this->schema = $schema;
    }

    protected function makeFragment(string $path, array $schema) : Fragment2
    {
        return new Fragment2($this->schema, $path, $schema);
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
        return $this->schema->getSchemas()->toArray();
    }

    /**
     * @param string $id
     * @return array
     * @throws SchemaNotFound
     */
    protected function getDefinition(string $id) : array
    {
        return $this->schema->getSchema($id)->toArray();
    }

    /**
     * @return array
     */
    protected function getSafeFuncs() : array
    {
        return array_merge(parent::getSafeFuncs(), ['getDefinitions', 'getDefinition']);
    }
}
