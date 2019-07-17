<?php

namespace DanBallance\OasTools\Specification;

/**
 * Class Fragment2
 *
 * @package DanBallance\OasTools\Specification
 */
class Fragment2 extends Fragment
{
    /**
     * Fragment constructor.
     * @param Specification2Interface $spec
     * @param $path
     * @param array $schema
     */
    public function __construct(Specification2Interface $spec, string $path, array $schema)
    {
        $this->spec = $spec;
        $this->path = $path;
        $this->schema = $schema;
    }


    public function getSpec() : Specification2Interface
    {
        return $this->spec;
    }
}
