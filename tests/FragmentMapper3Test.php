<?php

namespace DanBallance\OasTools\Tests;

use DanBallance\OasTools\FragmentMapper\FragmentMapper3;
use DanBallance\OasTools\Specification\SchemaParser;

class FragmentMapper3Test extends \PHPUnit\Framework\TestCase
{
    use SchemaParser;

    public function testPathToSchemaDefinitions()
    {
        $schema = $this->specFromFile('petstore-expanded.yaml');
        $mapper = new FragmentMapper3($schema);
        $fragment = $mapper->pathToSchema('#/components/schemas');
        $this->assertEquals(
            ['Pet', 'NewPet', 'Pets', 'Error'],
            array_keys($fragment->toArray())
        );
    }

    public function testPathToSchemaDefinition()
    {
        $schema = $this->specFromFile('petstore-expanded.yaml');
        $mapper = new FragmentMapper3($schema);
        $fragment = $mapper->pathToSchema('#/components/schemas/Pet');
        $this->assertEquals(
            [
                'type' => 'object',
                'allOf' => [
                    [
                        '$ref' => '#/components/schemas/NewPet'
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
        $schema = $this->specFromFile('petstore-expanded.yaml');
        $mapper = new FragmentMapper3($schema);
        $fragment = $mapper->pathToSchema('$/operations');
        $this->assertCount(
            3,
            $fragment->toArray()
        );
        $this->assertEquals(
            ['GET /pets', 'POST /pets', 'GET /pets/{petId}'],
            array_keys($fragment->toArray())
        );
    }

    public function testPathToSchemaOperation()
    {
        $schema = $this->specFromFile('petstore-expanded.yaml');
        $mapper = new FragmentMapper3($schema);
        $fragment = $mapper->pathToSchema('$/operations/createPets');
        $this->assertEquals(
            [
                'summary' => 'Create a pet',
                'operationId' => 'createPets',
                'tags' => ['pets'],
                'responses' => [
                    '201' => [
                        'description' => 'Null response'
                    ],
                    'default' => [
                        'description' => 'unexpected error',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Error'
                                ]
                            ]
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
        $schema = $this->specFromFile('petstore-expanded.yaml');
        $mapper = new FragmentMapper3($schema);
        $fragment = $mapper->pathToSchema('#/paths');
        $this->assertEquals(
            ['/pets', '/pets/{petId}'],
            array_keys($fragment->toArray())
        );
    }

    public function testPathToSchemaPath()
    {
        $schema = $this->specFromFile('petstore-expanded.yaml');
        $mapper = new FragmentMapper3($schema);
        $fragment = $mapper->pathToSchema('#/paths/\/pets\/{petId}');
        $this->assertEquals(
            ['get'],
            array_keys($fragment->toArray())
        );
    }

    public function testSchemaToPath()
    {
        $schema = $this->specFromFile('petstore-expanded.yaml');
        $mapper = new FragmentMapper3($schema);
        $fragment = $mapper->pathToSchema('#/paths/\/pets\/{petId}');
        $this->assertEquals(
            '#/paths/\/pets\/{petId}',
            $mapper->schemaToPath($fragment)
        );
    }

    protected function specFromFile($filename)
    {
        $fullPath = dirname(__FILE__)  .'/fixtures/specifications/oas3/' . $filename;
        return $this->parse($fullPath);
    }
}
