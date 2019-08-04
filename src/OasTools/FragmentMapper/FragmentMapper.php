<?php

namespace DanBallance\OasTools\FragmentMapper;

use DanBallance\OasTools\Exceptions\SchemaParseError;
use DanBallance\OasTools\Exceptions\SchemaNotFound;
use DanBallance\OasTools\Specification\Fragments\Fragment;
use DanBallance\OasTools\Specification\Fragments\FragmentInterface;
use DanBallance\OasTools\Collections\JCollect;
use Exception;

abstract class FragmentMapper
{
    abstract protected function getPathParts(
        string $path
    ) : array;

    protected $schema;

    public function getId(string $path) : string
    {
        $matches = array();
        preg_match_all('!#/.*/(.*)$!', $path, $matches);
        if ($matches) {
            return $matches[1][0];
        }
        throw new SchemaParseError("Path '{$path}' was not found in this schema");
    }

    public function schemaToPath(FragmentInterface $fragment) : string
    {
        return $fragment->path();
    }

    public function pathToSchema(string $path) : FragmentInterface
    {
        $schemaPart = $this->getSchemaPart($path);
        return $this->makeFragment($path, $schemaPart);
    }

    protected function makeFragment(
        string $path,
        array $schema
    ) : FragmentInterface {
        return new Fragment($this->schema, $path, $schema);
    }

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

    protected function getPaths() : array
    {
        return $this->schema->getPaths()->toArray();
    }

    protected function getOperations() : array
    {
        return $this->schema->getOperations()->toArray();
    }

    protected function getPath(string $id) : array
    {
        return $this->schema->getPath($id)->toArray();
    }

    protected function getOperation(string $id) : array
    {
        return $this->schema->getOperation($id)->toArray();
    }

    protected function getSafeFuncs() : array
    {
        return ['getPaths', 'getOperations', 'getPath', 'getOperation', 'getId'];
    }
}
