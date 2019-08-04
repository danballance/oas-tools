<?php

namespace DanBallance\OasTools\Collections;

use DanBallance\OasTools\Exceptions\SchemaNotFound;
use DanBallance\OasTools\Collections\JsonCollect;
use Jshannon63\JsonCollect\JsonCollect as Collection;

abstract class JCollect
{
    protected $specification;

    public function __construct(array $specification)
    {
        $this->specification = new JsonCollect($specification);
    }

    protected function cast($collection) : JsonCollect
    {
        if ($collection instanceof Collection) {
            return new JsonCollect($collection->toArray());
        }
        return $collection;
    }

    public function getExternalDocs() : JsonCollect
    {
        return $this->cast(
            $this->specification->get('externalDocs')
        );
    }

    public function getInfo() : JsonCollect
    {
        return $this->cast(
            $this->specification->get('info')
        );
    }

    public function getSecurity() : JsonCollect
    {
        return $this->cast(
            $this->specification->get('security')
        );
    }

    public function getTags() : JsonCollect
    {
        return $this->cast(
            $this->specification->get('tags')
        );
    }

    public function getSchema(
        string $id,
        $resolveReferences = false,
        $exclude = []
    ) : JsonCollect {
        $schema = $this->getSchemas()->get($id);
        if ($schema) {
            if ($resolveReferences) {
                $schema = $this->resolve($schema, $exclude);
            }
            return $this->cast($schema);
        }
        $msg = "Could not find schema '{$id}'.";
        throw new SchemaNotFound($msg);
    }

    public function resolve($schema, $exclude = []) : JsonCollect
    {
        $schema = $schema->toArray();
        $schema = $this->resolveReferences($schema, $exclude);
        // resolve 'allOf' if present
        foreach ($schema as $fieldName => $fieldValue) {
            if ($fieldName == 'allOf') {
                $composite = [
                    'type' => 'object',
                    'properties' => []
                ];
                foreach ($fieldValue as $compositeValue) {
                    $composite = $this->mergeSchema(
                        $composite,
                        $compositeValue
                    );
                }
                unset($schema['allOf']);
                $schema = $this->mergeSchema(
                    $schema,
                    $composite
                );
            }
        }
        return new JsonCollect($schema);
    }

    public function getComposition($schema) : array
    {
        $schema = $schema->toArray();
        $composition = [];
        foreach ($schema as $fieldName => $fieldValue) {
            if ($fieldName == 'allOf') {
                foreach ($fieldValue as $composite) {
                    if (isset($composite['$ref'])) {
                        $composition[] = $composite['$ref'];
                    }
                }
            }
        }
        return $composition;
    }

    /**
     * @param array $exclude array fields that we shouldn't recurse into
     */
    protected function resolveReferences(array $schema, $exclude = []) : array
    {
        foreach ($schema as $fieldName => $fieldValue) {
            if (is_array($fieldValue) && !in_array($fieldName, $exclude, true)) {  // then recurse
                $schema[$fieldName] = $this->resolveReferences($fieldValue, $exclude);
            } elseif ($fieldName === '$ref') {
                $schema = $this->mergeSchema(
                    $schema,
                    $this->resolveRef($fieldValue, $exclude)
                );
                unset($schema['$ref']);
            }
        }
        return $schema;
    }

    protected function resolveRef(string $reference, $exclude = []) : array
    {
        $parts = explode('/', $reference);
        $id = end($parts);
        $definition = $this->getSchema($id, true, $exclude);
        return $definition->toArray();
    }

    /**
     * Merges schemas. Never overwrites schema1 if there's a shared key.
     * Proerties and required arrays are merged with array_merge
     */
    protected function mergeSchema(array $schema1, array $schema2) : array
    {
        foreach ($schema2 as $fieldName => $fieldValue) {
            if (!isset($schema1[$fieldName])) {
                $schema1[$fieldName] = $fieldValue;
            } elseif ($fieldName == 'properties') {
                $schema1['properties'] = array_merge(
                    $schema1['properties'],
                    $schema2['properties']
                );
            } elseif ($fieldName == 'required') {
                $schema1['required'] = array_merge(
                    $schema1['required'],
                    $schema2['required']
                );
            }
        }
        return $schema1;
    }

    public function getPaths() : JsonCollect
    {
        return $this->cast(
            $this->specification->get('paths')
        );
    }

    public function getPath(string $path) : JsonCollect
    {
        return $this->cast(
            $this->specification->get('paths')[$path]
        );
    }

    public function getOperations() : JsonCollect
    {
        return $this->cast(
            $this->specification
                ->get('paths')
                ->map(
                    function ($collection, $path) {
                        return $collection->map(
                            function ($operation, $method) use ($path) {
                                $operation['path'] = $path;
                                $operation['method'] = $method;
                                return $operation;
                            }
                        );
                    }
                )
                ->flatten(1)
                ->keyBy(
                    function ($operation) {
                        return strtoupper($operation['method']) . " {$operation['path']}";
                    }
                )
        );
    }

    public function getOperationIds() : JsonCollect
    {
        return $this->cast(
            $this->getOperations()->pluck('operationId')
        );
    }

    /**
     * Fetch the operations grouped by their first tag
     */
    public function getOperationsByTag() : JsonCollect
    {
        return $this->cast(
            $this->getOperations()
                ->groupBy(
                    function ($item, $key) {
                        return $item['tags'][0];
                    }
                )
                ->map(
                    function ($collection) {
                        return $collection->keyBy(
                            function ($operation) {
                                return strtoupper($operation['method']) . " {$operation['path']}";
                            }
                        );
                    }
                )
        );
    }

    public function getOperation(string $id) : JsonCollect
    {
        $operations = $this->getOperations();
        $key = $operations->search(
            function ($item, $key) use ($id) {
                return $item['operationId'] == $id;
            }
        );
        if ($key) {
            return $this->cast(
                $operations->get($key)
            );
        }
        $msg = "Could find an operation for operationId '{$id}'.";
        throw new SchemaNotFound($msg);
    }

    public function getPathByOperationId(string $id): string
    {
        foreach ($this->getPaths() as $path => $operations) {
            $key = $operations->search(
                function ($item, $key) use ($id) {
                    return $item['operationId'] == $id;
                }
            );
            if ($key) {
                return $path;
            }
        }
        $msg = "Could not find a path for operationId '{$id}'.";
        throw new SchemaNotFound($msg);
    }

    public function toArray()
    {
        return $this->specification->toArray();
    }
}
