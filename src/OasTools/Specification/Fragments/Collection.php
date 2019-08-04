<?php

namespace DanBallance\OasTools\Specification\Fragments;

use Iterator;
use ArrayAccess;

class Collection extends Fragment implements Iterator, ArrayAccess
{
    protected $position = 0;
    /**
     * since $this->spec may be an assiciative array,
     * we'll use keyMap to provide integer index access to the data
     */
    protected $keyMap = []; 

    public function __construct($spec, string $path, array $schema)
    {
        parent::__construct($spec, $path, $schema);
        $this->keyMap = array_keys($this->schema);
    }

    /** Iterator */

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->make($this->key());
    }

    public function key()
    {
        return $this->keyMap[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->keyMap[$this->position]);
    }

    /** ArrayAccess */

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->schema[] = $value;
        } else {
            $this->schema[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->schema[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->schema[$offset]);
    }

    public function offsetGet($offset)
    {
        if (isset($this->schema[$offset])) {
            return $this->make($offset);
        }
    }

    /** other methods */

    protected function make(string $key)
    {
        $schema = $this->schema[$key];
        switch ($this->path()) {
            case '#/components/schemas':  // OAS3
                return new Schema(
                    $this->spec,
                    "#/components/schemas/{$key}",
                    $schema
                );
            case '#/definitions':  // OAS2
                return new Schema(
                    $this->spec,
                    "#/definitions/{$key}",
                    $schema
                );
            case '#/operations':
                return new Operation(
                    $this->spec,
                    "#/operations/{$schema['operationId']}",
                    $schema
                );
            default:
                return $schema;
        }
    }
}
