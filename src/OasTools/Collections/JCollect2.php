<?php

namespace DanBallance\OasTools\Collections;

use Jshannon63\JsonCollect\JsonCollect;

/**
 * Class JCollect2
 *
 * @package DanBallance\OasTools\Collections
 */
class JCollect2 extends JCollect
{
    protected $specification;

    /**
     * @return JsonCollect
     */
    public function getParameters() : JsonCollect
    {
        return $this->specification->get('parameters');
    }

    /**
     * @return JsonCollect
     */
    public function getResponses() : JsonCollect
    {
        return $this->specification->get('responses');
    }

    /**
     * @return JsonCollect
     */
    public function getSchemas() : JsonCollect
    {
        return $this->specification->get('definitions');
    }

    /**
     * @return JsonCollect
     */
    public function getServers() : JsonCollect
    {
        return new JsonCollect(
            [
                'host' => $this->specification->get('host'),
                'basePath' => $this->specification->get('basePath'),
                'schemes' => $this->specification->get('schemes')
            ]
        );
    }
}
