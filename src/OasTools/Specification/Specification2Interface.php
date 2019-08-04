<?php

namespace DanBallance\OasTools\Specification;

use DanBallance\OasTools\Specification\Fragments\FragmentInterface;

interface Specification2Interface
{
    public function getComposition(string $id) : array;

    public function getExternalDocs() : FragmentInterface;

    public function getInfo() : FragmentInterface;

    public function getOperation(string $id) : FragmentInterface;

    public function getOperationIds() : FragmentInterface;

    public function getOperations() : FragmentInterface;

    public function getOperationsByTag() : FragmentInterface;

    public function getParameters() : FragmentInterface;

    public function getPathByOperationId(string $id): string;

    public function getPaths() : FragmentInterface;

    public function getPath(string $path) : FragmentInterface;

    public function getResponses() : FragmentInterface;

    public function getSchemas() : FragmentInterface;

    public function getSchema(
        string $id,
        $resolveReferences = false,
        array $exclude = []
    ) : FragmentInterface;

    public function getSecurity() : FragmentInterface;

    public function getServers() : FragmentInterface;

    public function getTags() : FragmentInterface;

    public function resolve(FragmentInterface $fragment): FragmentInterface;

    public function toArray() : array;
}
