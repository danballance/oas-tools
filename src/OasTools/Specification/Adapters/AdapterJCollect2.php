<?php

namespace DanBallance\OasTools\Specification\Adapters;

use DanBallance\OasTools\Collections\JCollect;
use DanBallance\OasTools\Collections\JCollect2;
use DanBallance\OasTools\Exceptions\JsonParseError;
use DanBallance\OasTools\Exceptions\SchemaNotFound;
use DanBallance\OasTools\Specification\Specification2Interface;
use DanBallance\OasTools\Specification\Fragment;
use GuzzleHttp\Client;

/**
 * Class AdapterJCollect2
 *
 * @package DanBallance\OasTools\Specification
 */
class AdapterJCollect2 extends AdapterJCollect implements Specification2Interface
{
    protected $schemaLocation;
    protected $schema;
    protected $guzzle;

    /**
     * AdapterOas2 constructor.
     *
     * @param string $location Location of the schema - either a file path or an URL
     * @param Client $client   Inject a Guzzle client to use a schema on the network
     */
    public function __construct($location, Client $client = null)
    {
        $this->schemaLocation = $location;
        $this->guzzle = $client;
    }

    /**
     * @return Fragment
     * @throws JsonParseError
     */
    public function getParameters() : Fragment
    {
        $collection = $this->getSpec()->getParameters();
        $fragment = new Fragment('#/parameters', $collection->toArray());
        return $fragment;
    }

    /**
     * @return Fragment
     * @throws JsonParseError
     */
    public function getResponses() : Fragment
    {
        $collection = $this->getSpec()->getResponses();
        $fragment = new Fragment('#/responses', $collection->toArray());
        return $fragment;
    }

    /**
     * Returns the defined schemas in the specification
     *
     * @return Fragment
     * @throws JsonParseError

     */
    public function getSchemas() : Fragment
    {
        $collection = $this->getSpec()->getSchemas();
        $fragment = new Fragment('#/definitions', $collection->toArray());
        return $fragment;
    }

    /**
     * Obtain a particular schema by its ID
     *
     * @param string $id ID of the definition/schema
     * @param bool $resolveReferences If true then JSON references in the definition resolved
     * @param array $exclude
     * @return Fragment
     * @throws SchemaNotFound
     * @throws JsonParseError
     */
    public function getSchema(string $id, $resolveReferences = false, array $exclude = []) : Fragment
    {
        $collection = $this->getSpec()->getSchema($id, $resolveReferences, $exclude);
        $fragment = new Fragment("#/definitions/{$id}", $collection->toArray());
        return $fragment;
    }

    /**
     * @param array $array
     * @return JCollect
     */
    protected function makeSchema(array $array) : JCollect
    {
        return new JCollect2($array);
    }
}
