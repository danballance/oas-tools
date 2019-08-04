<?php

namespace DanBallance\OasTools\Tests;

use DanBallance\OasTools\Specification\Adapters\AdapterJCollect3;

class FragmentOperationTest extends \PHPUnit\Framework\TestCase
{
    protected $tester;
    protected $schemaPath;

    public function testSchemaMethods()
    {
        $spec = $this->specFromFile('petstore-expanded.yaml');
        $collectionFragment = $spec->getOperations();
        $this->assertEquals(
            false,
            $collectionFragment['GET /pets']->hasRequestSchema()
        );
        $this->assertEquals(
            null,
            $collectionFragment['GET /pets']->getRequestSchemaName()
        );
        $this->assertEquals(
            true,
            $collectionFragment['GET /pets']->hasResponseSchema()
        );
        $this->assertEquals(
            'Pets',
            $collectionFragment['GET /pets']->getResponseSchemaName()
        );
        $this->assertEquals(
            true,
            $collectionFragment['POST /pets']->hasRequestSchema()
        );
        $this->assertEquals(
            'NewPet',
            $collectionFragment['POST /pets']->getRequestSchemaName()
        );
        $this->assertEquals(
            false,
            $collectionFragment['POST /pets']->hasResponseSchema()
        );
        $this->assertEquals(
            null,
            $collectionFragment['POST /pets']->getResponseSchemaName()
        );
        $this->assertEquals(
            false,
            $collectionFragment['GET /pets/{petId}']->hasRequestSchema()
        );
        $this->assertEquals(
            null,
            $collectionFragment['GET /pets/{petId}']->getRequestSchemaName()
        );
        $this->assertEquals(
            true,
            $collectionFragment['GET /pets/{petId}']->hasResponseSchema()
        );
        $this->assertEquals(
            'Pets',
            $collectionFragment['GET /pets/{petId}']->getResponseSchemaName()
        );
        $this->assertEquals(
            false,
            $collectionFragment['DELETE /pets/{petId}']->hasRequestSchema()
        );
        $this->assertEquals(
            null,
            $collectionFragment['DELETE /pets/{petId}']->getRequestSchemaName()
        );
        $this->assertEquals(
            false,
            $collectionFragment['DELETE /pets/{petId}']->hasResponseSchema()
        );
        $this->assertEquals(
            null,
            $collectionFragment['DELETE /pets/{petId}']->getResponseSchemaName()
        );
  
    }

    protected function specFromFile($filename)
    {
        $fullPath = dirname(__FILE__)  .'/fixtures/specifications/oas3/' . $filename;
        $this->schemaPath = $fullPath;
        return new AdapterJCollect3($this->schemaPath);
    }
}
