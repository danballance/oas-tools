<?php

namespace DanBallance\OasTools\Specification;

/**
 * Class Fragment
 *
 * @package DanBallance\OasTools\Specification
 */
class Fragment
{
    protected $path;
    protected $schema;

    /**
     * Fragment constructor.
     * @param $path
     * @param array $schema
     */
    public function __construct($path, array $schema)
    {
        $this->path = $path;
        $this->schema = $schema;
    }

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
