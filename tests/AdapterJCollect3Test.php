<?php

namespace DanBallance\OasTools\Tests;

use DanBallance\OasTools\Specification\Adapters\AdapterJCollect3;
use GuzzleHttp\Psr7;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

/**
 * Class AdapterJCollect3Test
 *
 * @package DanBallance\OasTools\Tests
 * @author  Dan Ballance <work@danballance.uk>
 */
class AdapterJCollect3Test extends \PHPUnit\Framework\TestCase
{
    protected $tester;
    protected $schemaPath;

    public function testGetExternalDocs()
    {
        $spec = $this->specFromFile('petstore-with-external-docs.yaml');
        $fragment = $spec->getExternalDocs();
        $this->assertEquals(
            [
                'description' =>'find more info here',
                'url' => 'https://swagger.io/about'
            ],
            $fragment->toArray()
        );
        $this->assertEquals(
            '#/externalDocs',
            $fragment->path()
        );
    }

    public function testGetInfo()
    {
        $spec = $this->specFromFile('petstore.yml');
        $fragment = $spec->getInfo();
        $this->assertEquals(
            [
                'version' => '1.0.0',
                'title' => 'Swagger Petstore',
                'license' => [
                    'name' => 'MIT'
                ]
            ],
            $fragment->toArray()
        );
        $this->assertEquals(
            '#/info',
            $fragment->path()
        );
    }

    public function testGetParameters()
    {
        $spec = $this->specFromFile('petstore-expanded-from-docs.yaml');
        $fragment = $spec->getParameters();
        $this->assertEquals(
            [
                'skipParam' => [
                    'name' => 'skip',
                    'in' => 'query',
                    'description' => 'number of items to skip',
                    'required' => true,
                    'schema' => [
                        'type' => 'integer',
                        'format' => 'int32'
                    ]
                ],
                'limitParam' => [
                    'name' => 'limit',
                    'in' => 'query',
                    'description' => 'max records to return',
                    'required' => true,
                    'schema' => [
                        'type' => 'integer',
                        'format' => 'int32'
                    ]
                ]
            ],
            $fragment->toArray()
        );
        $this->assertEquals(
            '#/components/parameters',
            $fragment->path()
        );
    }

    public function testGetResponses()
    {
        $spec = $this->specFromFile('petstore-expanded-from-docs.yaml');
        $fragment = $spec->getResponses();
        $this->assertEquals(
            [
                'NotFound' => [
                    'description' => 'Entity not found.'
                ],
                'IllegalInput' => [
                    'description' => 'Illegal input for operation.'
                ],
                'GeneralError' => [
                    'description' => 'General Error',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/Error'
                            ]
                        ]
                    ]
                ]
            ],
            $fragment->toArray()
        );
        $this->assertEquals(
            '#/components/responses',
            $fragment->path()
        );
    }

    public function testGetSecurity()
    {
        $spec = $this->specFromFile('petstore-expanded-from-docs.yaml');
        $fragment = $spec->getSecurity();
        $this->assertEquals(
            [
                ['api_key' => []],
                ['petstore_auth' => [
                    "write:pets",
                    "read:pets"
                ]]
            ],
            $fragment->toArray()
        );
        $this->assertEquals(
            '#/security',
            $fragment->path()
        );
    }

    public function testGetServers()
    {
        $spec = $this->specFromFile('petstore-expanded-from-docs.yaml');
        $fragment = $spec->getServers();
        $this->assertEquals(
            [
                ['url' => 'http://petstore.swagger.io/v1'],
            ],
            $fragment->toArray()
        );
        $this->assertEquals(
            '#/servers',
            $fragment->path()
        );
    }

    public function testGetTags()
    {
        $spec = $this->specFromFile('petstore-expanded-from-docs.yaml');
        $fragment = $spec->getTags();
        $this->assertEquals(
            [
                [
                    'name' => 'pets',
                    'description' => 'Pets operations'
                ]
            ],
            $fragment->toArray()
        );
        $this->assertEquals(
            '#/tags',
            $fragment->path()
        );
    }

    public function testNetworkSuccess()
    {
        $fullPath = dirname(__FILE__)  . '/fixtures/specifications/oas3/petstore.yml';
        $mockHandler = new MockHandler(
            [
                $response = new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    Psr7\stream_for(file_get_contents($fullPath))
                )
            ]
        );
        $guzzle = $this->makeGuzzle($mockHandler);
        $spec = $this->specFromNetwork(
            'https://some-domain.com/api.yaml',
            $guzzle
        );
        $fragment = $spec->getSchemas();
        $this->assertEquals(
            ['Pet', 'Pets', 'Error'],
            array_keys($fragment->toArray())
        );
        $this->assertEquals(
            '#/components/schemas',
            $fragment->path()
        );
    }

    public function testInvalidFileExtension()
    {
        $this->expectException('\Exception');
        $spec = $this->specFromFile('petstore-expanded-from-docs.xml');
        $spec->getSchemas();
    }

    public function testNetworkJsonParseError()
    {
        $this->expectException('\DanBallance\OasTools\Exceptions\JsonParseError');
        $mockHandler = new MockHandler(
            [
                $response = new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    Psr7\stream_for('"Bad": "Data"')
                )
            ]
        );
        $guzzle = $this->makeGuzzle($mockHandler);
        $spec = $this->specFromNetwork('https://some-domain.com/api.json', $guzzle);
        $spec->getSchemas();
    }

    public function testNetworkFailure()
    {
        $this->expectException('\DanBallance\OasTools\Exceptions\NetworkError');
        $mockHandler = new MockHandler(
            [
                $response = new Response(
                    500,
                    ['Content-Type' => 'application/json']
                )
            ]
        );
        $guzzle = $this->makeGuzzle($mockHandler);
        $spec = $this->specFromNetwork('https://some-domain.com/api.json', $guzzle);
        $spec->getSchemas();
    }

    public function testNetworkClientNotConfigured()
    {
        $this->expectException('\DanBallance\OasTools\Exceptions\ConfigurationError');
        $spec = $this->specFromNetwork('https://some-domain.com/api.json');
        $spec->getSchemas();
    }

    public function testInvalidJsonException()
    {
        $this->expectException('\Symfony\Component\Yaml\Exception\ParseException');
        $spec = $this->specFromFile('invalid.yaml');
        $spec->getSchemas();
    }

    public function testFileNotFound()
    {
        $this->expectException('\DanBallance\OasTools\Exceptions\FileNotFound');
        $spec = $this->specFromFile('notfound.json');
        $spec->getSchemas();
    }

    public function testGetSchemas()
    {
        $spec = $this->specFromFile('petstore-expanded-from-docs.yaml');
        $fragment = $spec->getSchemas();
        $this->assertEquals(
            ['Pet', 'Pets', 'Error'],
            array_keys($fragment->toArray())
        );
        $this->assertEquals(
            '#/components/schemas',
            $fragment->path()
        );
    }

    public function testGetSchema()
    {
        $spec = $this->specFromFile('petstore-expanded.yaml');
        $fragment = $spec->getSchema('Pet');
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
        $this->assertEquals(
            '#/components/schemas/Pet',
            $fragment->path()
        );
    }

    public function testGetSchemaAllOfReferences()
    {
        $spec = $this->specFromFile('schemas-with-references.yaml');
        // without resolving references
        $fragment = $spec->getSchema('CharacterWizard');
        $this->assertEquals(
            [
                'allOf' => [
                    [
                        '$ref' => '#/components/schemas/Character'
                    ],
                    [
                        '$ref' => '#/components/schemas/Wizard'
                    ]
                ]
            ],
            $fragment->toArray()
        );
        // with references resolved
        $fragment = $spec->getSchema('CharacterWizard', true);
        $this->assertEquals(
            [
                'type' => 'object',
                'required' => ['id', 'name', 'magic'],
                'properties' => [
                    'id' => ['type' => 'integer', 'format' => 'int64'],
                    'name' =>  ['type' => 'string'],
                    'magic' => ['type' => 'integer', 'format' => 'int64']
                ]
            ],
            $fragment->toArray()
        );
    }

    public function testGetDefinitionAllOfReferenceAndObject()
    {
        $spec = $this->specFromFile('schemas-with-references.yaml');
        // with references resolved
        $fragment = $spec->getSchema('CharacterMage', true);
        $this->assertEquals(
            [
                'type' => 'object',
                'required' => ['id', 'name', 'magic', 'sorcery'],
                'properties' => [
                    'id' => ['type' => 'integer', 'format' => 'int64'],
                    'name' =>  ['type' => 'string'],
                    'magic' => ['type' => 'integer', 'format' => 'int64'],
                    'sorcery' => ['type' => 'integer', 'format' => 'int64'],
                    'weapons' =>  [
                        'type' => 'object',
                        'properties' => [
                            'attack' => ['type' => 'integer', 'format' => 'int64'],
                            'defense' => ['type' => 'integer', 'format' => 'int64']
                        ]
                    ]
                ]
            ],
            $fragment->toArray()
        );
    }

    public function testGetDefinitionAndResolveReference()
    {
        $spec = $this->specFromFile('schemas-with-references.yaml');
        // without resolving references
        // with references resolved
        $fragment = $spec->getSchema('Warrior', true);
        $this->assertEquals(
            [
                'type' => 'object',
                'required' => ['fighting'],
                'properties' => [
                    'fighting' => ['type' => 'integer', 'format' => 'int64'],
                    'weapons' =>  [
                        'type' => 'object',
                        'properties' => [
                            'attack' => ['type' => 'integer', 'format' => 'int64'],
                            'defense' => ['type' => 'integer', 'format' => 'int64']
                        ]
                    ]
                ]
            ],
            $fragment->toArray()
        );
    }

    public function testResolve()
    {
        $spec = $this->specFromFile('schemas-with-references.yaml');
        // without resolving references
        $fragment = $spec->getSchema('CharacterWizard');
        $this->assertEquals(
            [
                'allOf' => [
                    [
                        '$ref' => '#/components/schemas/Character'
                    ],
                    [
                        '$ref' => '#/components/schemas/Wizard'
                    ]
                ]
            ],
            $fragment->toArray()
        );
        // with references resolved
        $fragment = $spec->resolve($fragment);
        $this->assertEquals(
            [
                'type' => 'object',
                'required' => ['id', 'name', 'magic'],
                'properties' => [
                    'id' => ['type' => 'integer', 'format' => 'int64'],
                    'name' =>  ['type' => 'string'],
                    'magic' => ['type' => 'integer', 'format' => 'int64']
                ]
            ],
            $fragment->toArray()
        );
    }

    public function testGetComposition()
    {
        $spec = $this->specFromFile('schemas-with-references.yaml');
        $this->assertEquals(
            [
                '#/components/schemas/Character', '#/components/schemas/Wizard'
            ],
            $spec->getComposition('CharacterWizard')
        );
        $this->assertEquals(
            [
                '#/components/schemas/CharacterWizard'
            ],
            $spec->getComposition('CharacterMage')
        );
    }

    public function testGetDefinitionNotFound()
    {
        $this->expectException('\DanBallance\OasTools\Exceptions\SchemaNotFound');
        $spec = $this->specFromFile('petstore-expanded.yaml');
        $spec->getSchema('Rubbish');
    }

    public function testGetPaths()
    {
        $spec = $this->specFromFile('petstore-expanded.yaml');
        $fragment = $spec->getPaths();
        $paths = $fragment->toArray();
        $this->assertCount(2, $paths);
        $this->assertEquals(
            ['/pets', '/pets/{petId}'],
            array_keys($paths)
        );
        $this->assertEquals(
            ['get', 'post'],
            array_keys($paths['/pets'])
        );
        $this->assertEquals(
            ['get'],
            array_keys($paths['/pets/{petId}'])
        );
        $this->assertEquals(
            '#/paths',
            $fragment->path()
        );
    }

    public function testGetOperations()
    {
        $spec = $this->specFromFile('petstore-expanded.yaml');
        $fragment =  $spec->getOperations();
        $this->assertCount(
            3,
            $fragment->toArray()
        );
        $this->assertEquals(
            ['GET /pets', 'POST /pets', 'GET /pets/{petId}'],
            array_keys($fragment->toArray())
        );
        $this->assertEquals(
            '#/operations',
            $fragment->path()
        );
    }

    public function testGetOperationIds()
    {
        $spec = $this->specFromFile('petstore-expanded.yaml');
        $fragment = $spec->getOperationIds();
        $this->assertEquals(
            ["listPets", "createPets", "showPetById"],
            $fragment->toArray()
        );
        $this->assertEquals(
            '#/operationIds',
            $fragment->path()
        );
    }

    public function testGetOperation()
    {
        $spec = $this->specFromFile('petstore-expanded.yaml');
        $fragment = $spec->getOperation('showPetById');
        $this->assertEquals(
            [
                'summary' => 'Info for a specific pet',
                'operationId' => 'showPetById',
                'tags' => ['pets'],
                'parameters' => [
                    [
                        'name' => 'petId',
                        'in' => 'path',
                        'required' => true,
                        'description' => 'The id of the pet to retrieve',
                        'schema' => [
                            'type' => 'string'
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Expected response to a valid request',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Pets'
                                ]
                            ]
                        ]
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
                'path' => '/pets/{petId}',
                'method' => 'get',
            ],
            $fragment->toArray()
        );
        $this->assertEquals(
            '#/operations/showPetById',
            $fragment->path()
        );
    }

    public function testGetOperationNotFound()
    {
        $this->expectException('\DanBallance\OasTools\Exceptions\SchemaNotFound');
        $spec = $this->specFromFile('petstore-expanded.yaml');
        $spec->getOperation('updateAllPets');
    }

    public function testGetOperationsByTag()
    {
        $spec = $this->specFromFile('petstore.yml');
        $tagOps = $spec->getOperationsByTag()->toArray();
        $this->assertEquals(
            ['pets'],
            array_keys($tagOps)
        );
        $this->assertEquals(
            ['GET /pets', 'POST /pets', 'GET /pets/{petId}'],
            array_keys($tagOps['pets'])
        );
    }

    public function testGetPathByOperationId()
    {
        $spec = $this->specFromFile('petstore-expanded.yaml');
        $this->assertEquals(
            '/pets/{petId}',
            $spec->getPathByOperationId('showPetById')
        );
    }

    public function testGetPathByOperationIdNotFound()
    {
        $this->expectException('\DanBallance\OasTools\Exceptions\SchemaNotFound');
        $spec = $this->specFromFile('petstore-expanded.yaml');
        $spec->getPathByOperationId('updatePet');
    }

    protected function specFromFile($filename)
    {
        $fullPath = dirname(__FILE__)  .'/fixtures/specifications/oas3/' . $filename;
        $this->schemaPath = $fullPath;
        return new AdapterJCollect3($this->schemaPath);
    }

    /**
     * Create the AdapterJCollect3 object from a network location.
     *
     * @param string $url  The network location of the specification.
     * @param null $guzzle  Inject a Guzzle client to use for the network request.
     * @return AdapterJCollect3
     */
    protected function specFromNetwork($url, $guzzle = null)
    {
        return new AdapterJCollect3($url, $guzzle);
    }

    /**
     * Create a Guzzle client from a MockHandler
     *
     * @param MockHandler $mockHandler
     * @return Client
     */
    protected function makeGuzzle(MockHandler $mockHandler)
    {
        $options = [
            'handler' => HandlerStack::create($mockHandler)
        ];
        return new Client($options);
    }
}
