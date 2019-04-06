<?php

namespace DanBallance\OasTools\FragmentMapper;

use DanBallance\OasTools\Exceptions\SchemaParseError;
use DanBallance\OasTools\Exceptions\SchemaNotFound;
use DanBallance\OasTools\Specification\Fragment;
use DanBallance\OasTools\Collections\JCollect;
use Exception;

/**
 * Class FragmentMapper
 *
 * @package DanBallance\OasTools\FragmentMapper
 */
abstract class FragmentMapper
{
    abstract protected function getPathParts(string $path) : array;
    abstract protected function makeCollection(array $schema) : JCollect;

    protected $schema;
    protected $collection;

    /**
     * FragmentMapper constructor.
     * @param array $schema
     */
    public function __construct(array $schema)
    {
        $this->schema = $schema;
        $this->collection = $this->makeCollection($schema);
    }

    /**
     * @param string $path
     * @return mixed
     * @throws SchemaParseError
     */
    public function getId(string $path)
    {
        $matches = array();
        preg_match_all('!#/.*/(.*)$!', $path, $matches);
        if ($matches) {
            return $matches[1][0];
        }
        throw new SchemaParseError("Path '{$path}' was not found in this schema");
    }

    /**
     * @param Fragment $fragment
     * @return string
     */
    public function schemaToPath(Fragment $fragment) : string
    {
        return $fragment->path();
    }

    /**
     * @param string $path
     * @return Fragment
     * @throws Exception
     */
    public function pathToSchema(string $path) : Fragment
    {
        $schemaPart = $this->getSchemaPart($path);
        return new Fragment($path, $schemaPart);
    }

    /**
     * @param string $path
     * @return array
     * @throws Exception
     */
    protected function getSchemaPart(string $path) : array
    {
        list($type, $id) = $this->getPathParts($path);
        if ($id) {
            $func = "get" . ucfirst(substr($type, 0, -1));
            if (!in_array($func, $this->getSafeFuncs())) {
                throw new Exception('Illegal functional call');
            }
            return $this->$func($id);
        } else {
            $func = "get" . ucfirst($type);
            if (!in_array($func, $this->getSafeFuncs())) {
                throw new Exception('Illegal functional call');
            }
            return $this->$func();
        }
    }

    /**
     * @return array
     */
    protected function getPaths() : array
    {
        return $this->collection->getPaths()->toArray();
    }

    /**
     * @return array
     */
    protected function getOperations() : array
    {
        return $this->collection->getOperations()->toArray();
    }

    /**
     * @param string $id
     * @return array
     */
    protected function getPath(string $id) : array
    {
        return $this->collection->getPath($id)->toArray();
    }

    /**
     * @param string $id
     * @return array
     * @throws SchemaNotFound
     */
    protected function getOperation(string $id) : array
    {
        return $this->collection->getOperation($id)->toArray();
    }

    /**
     * @return array
     */
    protected function getSafeFuncs() : array
    {
        return ['getPaths', 'getOperations', 'getPath', 'getOperation', 'getId'];
    }
}
