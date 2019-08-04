<?php

namespace DanBallance\OasTools\Specification\Fragments;

class Fragment implements FragmentInterface
{
    protected $spec;
    protected $path;
    protected $schema;

    /**
     * @param Specification2Interface|Specification3Interface $spec 
     */
    public function __construct($spec, string $path, array $schema)
    {
        $this->spec = $spec;
        $this->path = $path;
        $this->schema = $schema;
    }

    /**
     * @return Specification2Interface|Specification3Interface
     */
    public function getSpec()
    {
        return $this->spec;
    }

    public function toArray() : array
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

    public function path() : string
    {
        return $this->path;
    }
}
