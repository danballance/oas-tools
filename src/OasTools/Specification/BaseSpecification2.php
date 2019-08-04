<?php

namespace DanBallance\OasTools\Specification;

use DanBallance\OasTools\Specification\Fragments\FragmentInterface;
use DanBallance\OasTools\Specification\Fragments\Fragment;
use DanBallance\OasTools\Specification\Fragments\Collection;
use DanBallance\OasTools\Specification\Fragments\Operation;
use DanBallance\OasTools\Specification\Fragments\Schema;

abstract class BaseSpecification2 extends BaseSpecification implements Specification2Interface
{
    public function getParameters() : FragmentInterface
    {
        $collection = $this->getSpec()->getParameters();
        return new Fragment($this, '#/parameters', $collection->toArray());
    }

    public function getResponses() : FragmentInterface
    {
        $collection = $this->getSpec()->getResponses();
        return new Fragment($this, '#/responses', $collection->toArray());
    }

    /**
     * Returns the defined schemas in the specification.
     * For OAS2 this means looking at the definitions.
     */
    public function getSchemas() : FragmentInterface
    {
        $collection = $this->getSpec()->getSchemas();
        return new Collection($this, '#/definitions', $collection->toArray());
    }

    /**
     * Obtain a particular schema by its ID
     *
     * @param string $id ID of the definition/schema
     * @param bool $resolveReferences If true then JSON references in the definition resolved
     * @param array $exclude
     */
    public function getSchema(
        string $id,
        $resolveReferences = false,
        array $exclude = []
    ) : FragmentInterface {
        $collection = $this->getSpec()->getSchema($id, $resolveReferences, $exclude);
        return new Schema($this, "#/definitions/{$id}", $collection->toArray());
    }
}
