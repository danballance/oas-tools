<?php

namespace DanBallance\OasTools\FragmentMapper;

use DanBallance\OasTools\Utils\ArrayUtil;
use DanBallance\OasTools\Exceptions\SchemaNotFound;
use DanBallance\OasTools\Specification\Specification3Interface;
use DanBallance\OasTools\Specification\Fragment3;

/**
 * Class FragmentMapper3
 *
 * @package DanBallance\OasTools\FragmentMapper
 */
class FragmentMapper3 extends FragmentMapper implements FragmentMapperInterface
{
    use ArrayUtil;


    /**
     * FragmentMapper3 constructor.
     * @param Specification3Interface $schema
     */
    public function __construct(Specification3Interface $schema)
    {
        $this->schema = $schema;
    }


    protected function makeFragment(string $path, array $schema) : Fragment3
    {
        return new Fragment3($this->schema, $path, $schema);
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
        return $this->schema->getSchemas()->toArray();
    }

    /**
     * @param string $id
     * @return array
     * @throws SchemaNotFound
     */
    protected function getSchema(string $id) : array
    {
        return $this->schema->getSchema($id)->toArray();
    }

    /**
     * @return array
     */
    protected function getSafeFuncs() : array
    {
        return array_merge(parent::getSafeFuncs(), ['getSchemas', 'getSchema']);
    }
}
