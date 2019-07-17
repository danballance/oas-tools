<?php

namespace DanBallance\OasTools\Specification;

/**
 * Class Fragment
 *
 * @package DanBallance\OasTools\Specification
 */
abstract class Fragment
{
    protected $spec;
    protected $path;
    protected $schema;

    abstract public function getSpec();

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->schema;
    }

    /**
     * @return false|string
     */
    public function toJson()
    {
        return json_encode($this->schema);
    }

    /**
     * @return mixed
     */
    public function path()
    {
        return $this->path;
    }
}
