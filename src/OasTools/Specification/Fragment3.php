<?php

namespace DanBallance\OasTools\Specification;

/**
 * Class Fragment3
 *
 * @package DanBallance\OasTools\Specification
 */
class Fragment3 extends Fragment
{
    /**
     * Fragment constructor.
     * @param Specification2Interface $spec
     * @param $path
     * @param array $schema
     */
    public function __construct(Specification3Interface $spec, string $path, array $schema)
    {
        $this->spec = $spec;
        $this->path = $path;
        $this->schema = $schema;
    }


    public function getSpec() : Specification3Interface
    {
        return $this->spec;
    }
}
