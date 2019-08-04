<?php

namespace DanBallance\OasTools\FragmentMapper;

use DanBallance\OasTools\Exceptions\SchemaNotFound;
use DanBallance\OasTools\Specification\Specification2Interface;

class FragmentMapper2 extends FragmentMapper implements FragmentMapperInterface
{
    public function __construct(Specification2Interface $schema)
    {
        $this->schema = $schema;
    }

    protected function getPathParts(string $path) : array
    {
        $parts = preg_split('~(?<!\\\)' . preg_quote('/', '~') . '~', $path);
        $parts = array_map(
            function ($part) {
                return str_replace('\/', '/', $part);
            },
            $parts
        );
        array_shift($parts);  // we don't want the leading '#' or '$'
        if (count($parts) == 1) {
            $parts[] = null;
        }
        return $parts;
    }

    protected function getDefinitions() : array
    {
        return $this->schema->getSchemas()->toArray();
    }

    protected function getDefinition(string $id) : array
    {
        return $this->schema->getSchema($id)->toArray();
    }

    protected function getSafeFuncs() : array
    {
        return array_merge(parent::getSafeFuncs(), ['getDefinitions', 'getDefinition']);
    }
}
