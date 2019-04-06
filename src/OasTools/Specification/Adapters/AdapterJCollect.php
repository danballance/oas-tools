<?php

namespace DanBallance\OasTools\Specification\Adapters;

use DanBallance\OasTools\Collections\JCollect;
use DanBallance\OasTools\Exceptions\JsonParseError;
use DanBallance\OasTools\Exceptions\SchemaNotFound;
use DanBallance\OasTools\Specification\Fragment;
use Jshannon63\JsonCollect\JsonCollect;
use DanBallance\OasTools\Specification\SchemaParser;

/**
 * Class AdapterJCollect
 *
 * @package DanBallance\OasTools\Specification
 */
abstract class AdapterJCollect
{
    use SchemaParser;

    protected $schemaLocation;
    protected $schema;

    /**
     * @param array $array
     * @return JCollect
     */
    abstract protected function makeSchema(array $array) : JCollect;

    /**
     * @return array
     * @throws JsonParseError
     */
    public function toArray() : array
    {
        return $this->getSpec()->toArray();
    }

    /**
     * @return Fragment
     * @throws JsonParseError
     */
    public function getExternalDocs() : Fragment
    {
        $collection = $this->getSpec()->getExternalDocs();
        $fragment = new Fragment('#/externalDocs', $collection->toArray());
        return $fragment;
    }

    /**
     * @return Fragment
     * @throws JsonParseError
     */
    public function getInfo() : Fragment
    {
        $collection = $this->getSpec()->getInfo();
        $fragment = new Fragment('#/info', $collection->toArray());
        return $fragment;
    }

    /**
     * @return Fragment
     * @throws JsonParseError
     */
    public function getSecurity() : Fragment
    {
        $collection = $this->getSpec()->getSecurity();
        $fragment = new Fragment('#/security', $collection->toArray());
        return $fragment;
    }

    /**
     * @return Fragment
     * @throws JsonParseError
     */
    public function getServers() : Fragment
    {
        $collection = $this->getSpec()->getServers();
        $fragment = new Fragment('#/servers', $collection->toArray());
        return $fragment;
    }

    /**
     * @return Fragment
     * @throws JsonParseError
     */
    public function getTags() : Fragment
    {
        $collection = $this->getSpec()->getTags();
        $fragment = new Fragment('#/tags', $collection->toArray());
        return $fragment;
    }

    /**
     * @return Fragment
     * @throws JsonParseError
     */
    public function getPaths() : Fragment
    {
        $collection = $this->getSpec()->getPaths();
        $fragment = new Fragment('#/paths', $collection->toArray());
        return $fragment;
    }

    /**
     * @return Fragment
     * @throws JsonParseError
     */
    public function getOperations() : Fragment
    {
        $collection = $this->getSpec()->getOperations();
        $fragment = new Fragment('#/operations', $collection->toArray());
        return $fragment;
    }

    /**
     * @return Fragment
     * @throws JsonParseError
     */
    public function getOperationIds() : Fragment
    {
        $collection = $this->getSpec()->getOperationIds();
        $fragment = new Fragment('#/operationIds', $collection->toArray());
        return $fragment;
    }

    /**
     * @return Fragment
     * @throws JsonParseError
     */
    public function getOperationsByTag() : Fragment
    {
        $collection = $this->getSpec()->getOperationsByTag();
        $fragment = new Fragment('#/operations[tag]', $collection->toArray());
        return $fragment;
    }

    /**
     * @param string $id
     * @return Fragment
     * @throws JsonParseError
     * @throws SchemaNotFound
     */
    public function getOperation(string $id) : Fragment
    {
        $collection = $this->getSpec()->getOperation($id);
        $fragment = new Fragment(
            "#/operations/{$id}",
            $collection->toArray()
        );
        return $fragment;
    }

    /**
     * @param string $id
     * @return string
     * @throws JsonParseError
     * @throws SchemaNotFound
     */
    public function getPathByOperationId(string $id): string
    {
        return $this->getSpec()->getPathByOperationId($id);
    }

    /**
     * @param Fragment $schema
     * @return Fragment
     * @throws JsonParseError
     */
    public function resolve(Fragment $schema) : Fragment
    {
        $collection = new JsonCollect($schema->toArray());
        $collection = $this->getSpec()->resolve($collection);
        return new Fragment(
            $schema->path(),
            $collection->toArray()
        );
    }

    /**
     * @param string $id
     * @return array
     * @throws JsonParseError
     */
    public function getComposition(string $id) : array
    {
        $schema = $this->getSchema($id);
        $collection = new JsonCollect($schema->toArray());
        return $this->getSpec()->getComposition($collection);
    }

    /**
     * @return JCollect
     * @throws JsonParseError
     */
    protected function getSpec()
    {
        if (!$this->schema) {
            $array = $this->parse($this->schemaLocation);
            $this->schema = $this->makeSchema($array);
        }
        return $this->schema;
    }
}
