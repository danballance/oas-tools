<?php

namespace DanBallance\OasTools\Collections;

use DanBallance\OasTools\Exceptions\SchemaNotFound;
use Jshannon63\JsonCollect\JsonCollect;

abstract class JCollect
{
    protected $specification;

    /**
     * JCollect constructor.
     * @param array $specification
     */
    public function __construct(array $specification)
    {
        $this->specification = new JsonCollect($specification);
    }

    /**
     * @return JsonCollect
     */
    public function getExternalDocs() : JsonCollect
    {
        return $this->specification->get('externalDocs');
    }

    /**
     * @return JsonCollect
     */
    public function getInfo() : JsonCollect
    {
        return $this->specification->get('info');
    }

    /**
     * @return JsonCollect
     */
    public function getSecurity() : JsonCollect
    {
        return $this->specification->get('security');
    }

    /**
     * @return JsonCollect
     */
    public function getTags() : JsonCollect
    {
        return $this->specification->get('tags');
    }

    /**
     * @param string $id
     * @param bool $resolveReferences
     * @param array $exclude
     * @return JsonCollect
     * @throws SchemaNotFound
     */
    public function getSchema(string $id, $resolveReferences = false, $exclude = []) : JsonCollect
    {
        $schema = $this->getSchemas()->get($id);
        if ($schema) {
            if ($resolveReferences) {
                $schema = $this->resolve($schema, $exclude);
            }
            return $schema;
        }
        $msg = "Could not find schema '{$id}'.";
        throw new SchemaNotFound($msg);
    }

    /**
     * @param JsonCollect $schema
     * @param array $exclude
     * @return JsonCollect
     */
    public function resolve(JsonCollect $schema, $exclude = []) : JsonCollect
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

    /**
     * @param JsonCollect $schema
     * @return array
     */
    public function getComposition(JsonCollect $schema) : array
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
     * @param array $schema
     * @param array $exclude array fields that we shouldn't recurse into
     * @return array
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

    /**
     * @param string $reference
     * @param array $exclude
     * @return array
     * @throws SchemaNotFound
     */
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
     * @param array $schema1
     * @param array $schema2
     * @return array
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

    /**
     * @return JsonCollect
     */
    public function getPaths() : JsonCollect
    {
        return $this->specification->get('paths');
    }

    /**
     * @param string $path
     * @return JsonCollect
     */
    public function getPath(string $path) : JsonCollect
    {
        return $this->specification->get('paths')[$path];
    }

    /**
     * @return JsonCollect
     */
    public function getOperations() : JsonCollect
    {
        return $this->specification
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
            );
    }

    /**
     * @return JsonCollect
     */
    public function getOperationIds() : JsonCollect
    {
        return $this->getOperations()->pluck('operationId');
    }

    /**
     * Fetch the operations grouped by their first tag
     *
     * @return JsonCollect
     */
    public function getOperationsByTag() : JsonCollect
    {
        return $this->getOperations()
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
            );
    }

    /**
     * @param string $id
     * @return JsonCollect
     * @throws SchemaNotFound
     */
    public function getOperation(string $id) : JsonCollect
    {
        $operations = $this->getOperations();
        $key = $operations->search(
            function ($item, $key) use ($id) {
                return $item['operationId'] == $id;
            }
        );
        if ($key) {
            return $operations->get($key);
        }
        $msg = "Could find an operation for operationId '{$id}'.";
        throw new SchemaNotFound($msg);
    }

    /**
     * @param string $id
     * @return string
     * @throws SchemaNotFound
     */
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

    /**
     * @return mixed
     */
    public function toArray()
    {
        return $this->specification->toArray();
    }
}
