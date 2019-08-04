<?php

namespace DanBallance\OasTools\Tests;

use DanBallance\OasTools\Specification\Adapters\AdapterJCollect3;
use DanBallance\OasTools\Specification\Fragments\Fragment;
use DanBallance\OasTools\Specification\Fragments\Operation;
use DanBallance\OasTools\Specification\Fragments\Collection;
use DanBallance\OasTools\Specification\Fragments\Schema;

class FragmentCollectionTest extends \PHPUnit\Framework\TestCase
{
    protected $tester;
    protected $schemaPath;

    public function testCollectionIteratesFragments()
    {
        $spec = $this->specFromFile('petstore-expanded-from-docs.yaml');
        
        $collectionFragment = $spec->getSchemas();
        $this->assertInstanceOf(
            Collection::class,
            $collectionFragment
        );
        foreach ($collectionFragment as $key => $schemaFragment) {
            $this->assertInstanceOf(
                Schema::class,
                $schemaFragment
            );
            $this->assertEquals(
                "#/components/schemas/{$key}",
                $schemaFragment->path()
            );
        }

        $collectionFragment = $spec->getOperations();
        $this->assertInstanceOf(
            Collection::class,
            $collectionFragment
        );
        foreach ($collectionFragment as $key => $schemaFragment) {
            $this->assertInstanceOf(
                Operation::class,
                $schemaFragment
            );
            $operationId = $schemaFragment->toArray()['operationId'];
            $this->assertEquals(
                "#/operations/{$operationId}",
                $schemaFragment->path()
            );
        }
      
    }

    public function testArrayAccess()
    {
        $spec = $this->specFromFile('petstore-expanded-from-docs.yaml');
        $collectionFragment = $spec->getSchemas();
        $this->assertEquals(
            "#/components/schemas/Error",
            $collectionFragment['Error']->path()
        );
        $this->assertEquals(
            "#/components/schemas/Pets",
            $collectionFragment['Pets']->path()
        );
        $this->assertEquals(
            "#/components/schemas/Pet",
            $collectionFragment['Pet']->path()
        );
    }

    protected function specFromFile($filename)
    {
        $fullPath = dirname(__FILE__)  .'/fixtures/specifications/oas3/' . $filename;
        $this->schemaPath = $fullPath;
        return new AdapterJCollect3($this->schemaPath);
    }
}
