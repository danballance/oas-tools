<?php

namespace DanBallance\OasTools\Tests;

use DanBallance\OasTools\FragmentMapper\FragmentMapper2;
use DanBallance\OasTools\Specification\SchemaParser;
use DanBallance\OasTools\Specification\Adapters\AdapterJCollect2;

class FragmentMapper2Test extends \PHPUnit\Framework\TestCase
{
    use SchemaParser;

    public function testGetId()
    {
        $schema = $this->specFromFile('petstore-expanded.json');
        $mapper = new FragmentMapper2($schema);
        $this->assertEquals(
            'Pet',
            $mapper->getId('#/definitions/Pet')
        );
    }

    public function testPathToSchemaDefinitions()
    {
        $schema = $this->specFromFile('petstore-expanded.json');
        $mapper = new FragmentMapper2($schema);
        $fragment = $mapper->pathToSchema('#/definitions');
        $this->assertEquals(
            ['Pet', 'NewPet', 'Error'],
            array_keys($fragment->toArray())
        );
    }

    public function testPathToSchemaDefinitionsJson()
    {
        $schema = $this->specFromFile('petstore-expanded.json');
        $mapper = new FragmentMapper2($schema);
        $fragment = $mapper->pathToSchema('#/definitions/Pet');
        $this->assertEquals(
            '{"type":"object","allOf":' .
            '[{"$ref":"#\/definitions\/NewPet"},{"required":["id"],"properties":' .
            '{"id":{"type":"integer","format":"int64"}}}]}',
            $fragment->toJson()
        );
    }

    public function testPathToSchemaDefinition()
    {
        $schema = $this->specFromFile('petstore-expanded.json');
        $mapper = new FragmentMapper2($schema);
        $fragment = $mapper->pathToSchema('#/definitions/Pet');
        $this->assertEquals(
            [
                'type' => 'object',
                'allOf' => [
                    [
                        '$ref' => '#/definitions/NewPet'
                    ],
                    [
                        'required' => ['id'],
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                                'format' => 'int64'
                            ]
                        ]
                    ]
                ]
            ],
            $fragment->toArray()
        );
    }

    public function testPathToSchemaOperations()
    {
        $schema = $this->specFromFile('petstore-expanded.json');
        $mapper = new FragmentMapper2($schema);
        $fragment = $mapper->pathToSchema('$/operations');
        $this->assertCount(
            4,
            $fragment->toArray()
        );
    }

    public function testPathToSchemaOperation()
    {
        $schema = $this->specFromFile('petstore-expanded.json');
        $mapper = new FragmentMapper2($schema);
        $fragment = $mapper->pathToSchema('$/operations/addPet');
        $this->assertEquals(
            [
                'description' => 'Creates a new pet in the store.  ' .
                    'Duplicates are allowed',
                'operationId' => 'addPet',
                'parameters' => [
                    [
                        'name' => 'pet',
                        'in' => 'body',
                        'description' => 'Pet to add to the store',
                        'required' => true,
                        'schema' => [
                            '$ref' => '#/definitions/NewPet'
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'pet response',
                        'schema' => [
                            '$ref' => '#/definitions/Pet'
                        ]
                    ],
                    'default' => [
                        'description' => 'unexpected error',
                        'schema' => [
                            '$ref' => '#/definitions/Error'
                        ]
                    ]
                ],
                'method' => 'post',
                'path' => '/pets',
            ],
            $fragment->toArray()
        );
    }

    public function testPathToSchemaPaths()
    {
        $schema = $this->specFromFile('petstore-expanded.json');
        $mapper = new FragmentMapper2($schema);
        $fragment = $mapper->pathToSchema('#/paths');
        $this->assertEquals(
            ['/pets', '/pets/{id}'],
            array_keys($fragment->toArray())
        );
    }

    public function testPathToSchemaPath()
    {
        $schema = $this->specFromFile('petstore-expanded.json');
        $mapper = new FragmentMapper2($schema);
        // the slashes in the OAS path have to be escaped
        $fragment = $mapper->pathToSchema('#/paths/\/pets\/{id}');
        $this->assertEquals(
            ['get', 'delete'],
            array_keys($fragment->toArray())
        );
    }

    protected function specFromFile($filename)
    {
        $fullPath = dirname(__FILE__)  .'/fixtures/specifications/oas2/' . $filename;
        $this->schemaPath = $fullPath;
        return new AdapterJCollect2($this->schemaPath);
    }
}
