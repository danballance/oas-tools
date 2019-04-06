<?php

namespace DanBallance\OasTools\Collections;

use Jshannon63\JsonCollect\JsonCollect;

/**
 * Class JCollect3
 *
 * @package DanBallance\OasTools\Collections
 */
class JCollect3 extends JCollect
{
    protected $specification;

    /**
     * @return JsonCollect
     */
    public function getParameters() : JsonCollect
    {
        return $this->specification->get('components')->get('parameters');
    }

    /**
     * @return JsonCollect
     */
    public function getResponses() : JsonCollect
    {
        return $this->specification->get('components')->get('responses');
    }

    /**
     * @return JsonCollect
     */
    public function getSchemas() : JsonCollect
    {
        return $this->specification->get('components')->get('schemas');
    }

    /**
     * @return JsonCollect
     */
    public function getServers() : JsonCollect
    {
        return $this->specification->get('servers');
    }
}
