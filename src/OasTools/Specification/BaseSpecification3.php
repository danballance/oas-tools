<?php

namespace DanBallance\OasTools\Specification;

use GuzzleHttp\Client;
use DanBallance\OasTools\Specification\Fragments\FragmentInterface;
use DanBallance\OasTools\Specification\Fragments\Fragment;
use DanBallance\OasTools\Specification\Fragments\Collection;
use DanBallance\OasTools\Specification\Fragments\Operation;
use DanBallance\OasTools\Specification\Fragments\Schema;

abstract class BaseSpecification3 extends BaseSpecification implements Specification3Interface
{
    public function getParameters() : FragmentInterface
    {
        $collection = $this->getSpec()->getParameters();
        return new Fragment($this, '#/components/parameters', $collection->toArray());
    }

    public function getResponses() : FragmentInterface
    {
        $collection = $this->getSpec()->getResponses();
        return new Fragment($this, '#/components/responses', $collection->toArray());
    }

    public function getSchemas() : FragmentInterface
    {
        $collection = $this->getSpec()->getSchemas();
        return new Collection($this, '#/components/schemas', $collection->toArray());
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
        $collection = $this->getSpec()->getSchema(
            $id,
            $resolveReferences,
            $exclude
        );
        return new Schema($this, "#/components/schemas/{$id}", $collection->toArray());
    }
}
