<?php

namespace DanBallance\OasTools\Tests;

use DanBallance\OasTools\Specification\Adapters\AdapterJCollect2;
use GuzzleHttp\Psr7;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

/**
 * Class AdapterJCollect2Test
 *
 * @package DanBallance\OasTools\Tests
 * @author  Dan Ballance <work@danballance.uk>
 */
class AdapterJCollect2Test extends \PHPUnit\Framework\TestCase
{
    protected $tester;
    protected $schemaPath;

    public function testToArray()
    {
        $path = dirname(__FILE__)  .'/fixtures/specifications/oas2/petstore-with-external-docs.json';
        $string = file_get_contents($path);
        $array = @json_decode($string, true);
        $spec = $this->specFromFile('petstore-with-external-docs.json');
        $this->assertEquals(
            $array,
            $spec->toArray()
        );
    }

    public function testGetExternalDocs()
    {
        $spec = $this->specFromFile('petstore-with-external-docs.json');
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
        $spec = $this->specFromFile('petstore-with-external-docs.json');
        $fragment = $spec->getInfo();
        $this->assertEquals(
            [
                'version' => '1.0.0',
                'title' => 'Swagger Petstore',
                'description' => 'A sample API that uses a petstore as an example' .
                    ' to demonstrate features in the swagger-2.0 specification',
                'termsOfService' => 'http://swagger.io/terms/',
                'contact' => [
                    'name' => 'Swagger API Team',
                    'email' => 'apiteam@swagger.io',
                    'url' => 'http://swagger.io'
                ],
                'license' => [
                    'name' => 'Apache 2.0',
                    'url' => 'https://www.apache.org/licenses/LICENSE-2.0.html'
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
        $spec = $this->specFromFile('petstore-expanded-from-docs.json');
        $fragment = $spec->getParameters();
        $this->assertEquals(
            [
                'skipParam' => [
                    'name' => 'skip',
                    'in' => 'query',
                    'description' => 'number of items to skip',
                    'required' => true,
                    'type' => 'integer',
                    'format' => 'int32'
                ],
                'limitParam' => [
                    'name' => 'limit',
                    'in' => 'query',
                    'description' => 'max records to return',
                    'required' => true,
                    'type' => 'integer',
                    'format' => 'int32'
                ]
            ],
            $fragment->toArray()
        );
        $this->assertEquals(
            '#/parameters',
            $fragment->path()
        );
    }

    public function testGetResponses()
    {
        $spec = $this->specFromFile('petstore-expanded-from-docs.json');
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
                    'schema' => [
                        '$ref' => '#/definitions/GeneralError'
                    ]
                ]
            ],
            $fragment->toArray()
        );
        $this->assertEquals(
            '#/responses',
            $fragment->path()
        );
    }

    public function testGetSecurity()
    {
        $spec = $this->specFromFile('petstore-expanded-from-docs.json');
        $fragment = $spec->getSecurity();
        $this->assertEquals(
            [
                'api_key' => [],
                'petstore_auth' => [
                    "write:pets",
                    "read:pets"
                ]
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
        $spec = $this->specFromFile('petstore-expanded-from-docs.json');
        $fragment = $spec->getServers();
        $this->assertEquals(
            [
                'host' => 'petstore.swagger.io',
                'basePath' => '/api',
                'schemes' => [
                    'http'
                ],
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
        $spec = $this->specFromFile('petstore.json');
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
        $fullPath = dirname(__FILE__)  . '/fixtures/specifications/oas2/petstore.json';
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
            'https://some-domain.com/api.json',
            $guzzle
        );
        $fragment = $spec->getSchemas();
        $this->assertEquals(
            ['Pet', 'Pets', 'Error'],
            array_keys($fragment->toArray())
        );
        $this->assertEquals(
            '#/definitions',
            $fragment->path()
        );
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
        $this->expectException('\DanBallance\OasTools\Exceptions\JsonParseError');
        $spec = $this->specFromFile('invalid.json');
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
        $spec = $this->specFromFile('petstore-expanded.json');
        $fragment = $spec->getSchemas();
        $this->assertEquals(
            ['Pet', 'NewPet', 'Error'],
            array_keys($fragment->toArray())
        );
        $this->assertEquals(
            '#/definitions',
            $fragment->path()
        );
    }

    public function testGetSchema()
    {
        $spec = $this->specFromFile('petstore-expanded.json');
        $fragment = $spec->getSchema('Pet');
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
        $this->assertEquals(
            '#/definitions/Pet',
            $fragment->path()
        );
    }

    public function testGetSchemaAllOfReferences()
    {
        $spec = $this->specFromFile('schemas-with-references.json');
        // without resolving references
        $fragment = $spec->getSchema('CharacterWizard');
        $this->assertEquals(
            [
                'allOf' => [
                    [
                        '$ref' => '#/definitions/Character'
                    ],
                    [
                        '$ref' => '#/definitions/Wizard'
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
        $spec = $this->specFromFile('schemas-with-references.json');
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
        $spec = $this->specFromFile('schemas-with-references.json');
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
        $spec = $this->specFromFile('schemas-with-references.json');
        // without resolving references
        $fragment = $spec->getSchema('CharacterWizard');
        $this->assertEquals(
            [
                'allOf' => [
                    [
                        '$ref' => '#/definitions/Character'
                    ],
                    [
                        '$ref' => '#/definitions/Wizard'
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
        $spec = $this->specFromFile('schemas-with-references.json');
        $this->assertEquals(
            [
                '#/definitions/Character', '#/definitions/Wizard'
            ],
            $spec->getComposition('CharacterWizard')
        );
        $this->assertEquals(
            [
                '#/definitions/CharacterWizard'
            ],
            $spec->getComposition('CharacterMage')
        );
    }

    public function testGetDefinitionNotFound()
    {
        $this->expectException('\DanBallance\OasTools\Exceptions\SchemaNotFound');
        $spec = $this->specFromFile('petstore-expanded.json');
        $spec->getSchema('Rubbish');
    }

    public function testGetPaths()
    {
        $spec = $this->specFromFile('petstore-expanded.json');
        $fragment = $spec->getPaths();
        $paths = $fragment->toArray();
        $this->assertCount(2, $paths);
        $this->assertEquals(
            ['/pets', '/pets/{id}'],
            array_keys($paths)
        );
        $this->assertEquals(
            ['get', 'post'],
            array_keys($paths['/pets'])
        );
        $this->assertEquals(
            ['get', 'delete'],
            array_keys($paths['/pets/{id}'])
        );
        $this->assertEquals(
            '#/paths',
            $fragment->path()
        );
    }

    public function testGetOperations()
    {
        $spec = $this->specFromFile('petstore-expanded.json');
        $fragment =  $spec->getOperations();
        $this->assertCount(
            4,
            $fragment->toArray()
        );
        $this->assertEquals(
            ['GET /pets', 'POST /pets', 'GET /pets/{id}', 'DELETE /pets/{id}'],
            array_keys($fragment->toArray())
        );
        $this->assertEquals(
            '#/operations',
            $fragment->path()
        );
    }

    public function testGetOperationIds()
    {
        $spec = $this->specFromFile('petstore-expanded.json');
        $fragment = $spec->getOperationIds();
        $this->assertEquals(
            ["findPets", "addPet", "find pet by id", "deletePet"],
            $fragment->toArray()
        );
        $this->assertEquals(
            '#/operationIds',
            $fragment->path()
        );
    }

    public function testGetOperation()
    {
        $spec = $this->specFromFile('petstore-expanded.json');
        $fragment = $spec->getOperation('addPet');
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
                'path' => '/pets',
                'method' => 'post',
            ],
            $fragment->toArray()
        );
        $this->assertEquals(
            '#/operations/addPet',
            $fragment->path()
        );
    }

    public function testGetOperationNotFound()
    {
        $this->expectException('DanBallance\OasTools\Exceptions\SchemaNotFound');
        $spec = $this->specFromFile('petstore-expanded.json');
        $spec->getOperation('updateAllPets');
    }

    public function testGetOperationsByTag()
    {
        $spec = $this->specFromFile('petstore.json');
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
        $spec = $this->specFromFile('petstore-expanded.json');
        $this->assertEquals(
            '/pets/{id}',
            $spec->getPathByOperationId('deletePet')
        );
    }

    public function testGetPathByOperationIdNotFound()
    {
        $this->expectException('DanBallance\OasTools\Exceptions\SchemaNotFound');
        $spec = $this->specFromFile('petstore-expanded.json');
        $spec->getPathByOperationId('updatePet');
    }

    protected function specFromFile($filename)
    {
        $fullPath = dirname(__FILE__)  .'/fixtures/specifications/oas2/' . $filename;
        $this->schemaPath = $fullPath;
        return new AdapterJCollect2($this->schemaPath);
    }

    /**
     * Create the AdapterJCollect object from a network location.
     *
     * @param string $url  The network location of the specification.
     * @param null $guzzle  Inject a Guzzle client to use for the network request.
     * @return AdapterJCollect2
     */
    protected function specFromNetwork($url, $guzzle = null)
    {
        return new AdapterJCollect2($url, $guzzle);
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
