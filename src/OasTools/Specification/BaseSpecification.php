<?php

namespace DanBallance\OasTools\Specification;

use Jshannon63\JsonCollect\JsonCollect;
use GuzzleHttp\Client;
use DanBallance\OasTools\Collections\CollectionInterface;
use DanBallance\OasTools\Specification\Fragments\FragmentInterface;
use DanBallance\OasTools\Specification\Fragments\Fragment;
use DanBallance\OasTools\Specification\Fragments\Collection;
use DanBallance\OasTools\Specification\Fragments\Operation;
use DanBallance\OasTools\Specification\Fragments\Schema;

abstract class BaseSpecification
{
    use SchemaParser;

    protected $schemaLocation;
    protected $schema;
    protected $guzzle;

    /**
     * @param array $array
     * @return CollectionInterface
     */
    abstract protected function makeSchema(array $array);

    /**
     * @param string $location Location of the schema - either a file path or an URL
     * @param Client $client   Inject a Guzzle client to use a schema on the network
     */
    public function __construct($location, Client $client = null)
    {
        $this->schemaLocation = $location;
        $this->guzzle = $client;
    }

    /**
     * @return CollectionInterface
     */
    protected function getSpec()
    {
        if (!$this->schema) {
            $array = $this->parse($this->schemaLocation);
            $this->schema = $this->makeSchema($array);
        }
        return $this->schema;
    }

    public function toArray() : array
    {
        return $this->getSpec()->toArray();
    }

    public function getExternalDocs() : FragmentInterface
    {
        $collection = $this->getSpec()->getExternalDocs();
        return new Fragment($this, '#/externalDocs', $collection->toArray());
    }

    public function getInfo() : FragmentInterface
    {
        $collection = $this->getSpec()->getInfo();
        return new Fragment($this, '#/info', $collection->toArray());
    }

    public function getSecurity() : FragmentInterface
    {
        $collection = $this->getSpec()->getSecurity();
        return new Fragment($this, '#/security', $collection->toArray());
    }

    public function getServers() : FragmentInterface
    {
        $collection = $this->getSpec()->getServers();
        return new Fragment($this, '#/servers', $collection->toArray());
    }

    public function getTags() : FragmentInterface
    {
        $collection = $this->getSpec()->getTags();
        return new Fragment($this, '#/tags', $collection->toArray());
    }

    public function getPaths() : FragmentInterface
    {
        $collection = $this->getSpec()->getPaths();
        return new Fragment($this, '#/paths', $collection->toArray());
    }

    public function getPath(string $path) : FragmentInterface
    {
        $collection = $this->getSpec()->getPath($path);
        return new Fragment($this, $path, $collection->toArray());
    }

    public function getOperations() : FragmentInterface
    {
        $collection = $this->getSpec()->getOperations();
        return new Collection($this, '#/operations', $collection->toArray());
    }

    public function getOperationIds() : FragmentInterface
    {
        $collection = $this->getSpec()->getOperationIds();
        return new Fragment($this, '#/operationIds', $collection->toArray());
    }

    public function getOperationsByTag() : FragmentInterface
    {
        $collection = $this->getSpec()->getOperationsByTag();
        return new Collection($this, '#/operations[tag]', $collection->toArray());
    }

    public function getOperation(string $id) : FragmentInterface
    {
        $collection = $this->getSpec()->getOperation($id);
        return new Operation($this, "#/operations/{$id}", $collection->toArray());
    }

    public function getPathByOperationId(string $id): string
    {
        return $this->getSpec()->getPathByOperationId($id);
    }

    public function resolve(FragmentInterface $schema) : FragmentInterface
    {
        $collection = new JsonCollect($schema->toArray());
        $collection = $this->getSpec()->resolve($collection);
        return new Fragment($this, $schema->path(), $collection->toArray());
    }

    public function getComposition(string $id) : array
    {
        $schema = $this->getSchema($id);
        $collection = new JsonCollect($schema->toArray());
        return $this->getSpec()->getComposition($collection);
    }
}
