<?php

namespace DanBallance\OasTools\Collections;

use DanBallance\OasTools\Collections\JsonCollect;

class JCollect2 extends JCollect
{
    protected $specification;

    public function getParameters() : JsonCollect
    {
        return $this->cast(
            $this->specification->get('parameters')
        );
    }

    public function getResponses() : JsonCollect
    {
        return $this->cast(
            $this->specification->get('responses')
        );
    }

    public function getSchemas() : JsonCollect
    {
        return $this->cast(
            $this->specification->get('definitions')
        );
    }

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
