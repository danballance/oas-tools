<?php

namespace DanBallance\OasTools\Specification;

/**
 * Interface Specification2Interface
 *
 * @package DanBallance\OasTools\Specification
 */
interface Specification2Interface
{
    /**
     * @param string $id
     * @return array
     */
    public function getComposition(string $id) : array;

    /**
     * @return Fragment
     */
    public function getExternalDocs() : Fragment;

    /**
     * @return Fragment
     */
    public function getInfo() : Fragment;

    /**
     * @param string $id
     * @return Fragment
     */
    public function getOperation(string $id) : Fragment;

    /**
     * @return Fragment
     */
    public function getOperationIds() : Fragment;

    /**
     * @return Fragment
     */
    public function getOperations() : Fragment;

    /**
     * @return Fragment
     */
    public function getOperationsByTag() : Fragment;

    /**
     * @return Fragment
     */
    public function getParameters() : Fragment;

    /**
     * @param string $id
     * @return string
     */
    public function getPathByOperationId(string $id): string;

    /**
     * @return Fragment
     */
    public function getPaths() : Fragment;

    /**
     * @return Fragment
     */
    public function getResponses() : Fragment;

    /**
     * @return Fragment
     */
    public function getSchemas() : Fragment;

    /**
     * @param string $id
     * @param bool $resolveReferences
     * @param array $exclude
     * @return Fragment
     */
    public function getSchema(
        string $id,
        $resolveReferences = false,
        array $exclude = []
    ) : Fragment;

    /**
     * @return Fragment
     */
    public function getSecurity() : Fragment;

    /**
     * @return Fragment
     */
    public function getServers() : Fragment;

    /**
     * @return Fragment
     */
    public function getTags() : Fragment;

    /**
     * @param Fragment $fragment
     * @return Fragment
     */
    public function resolve(Fragment $fragment): Fragment;

    /**
     * @return array
     */
    public function toArray() : array;
}
