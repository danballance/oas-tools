<?php

namespace DanBallance\OasTools\Collections;

use DanBallance\OasTools\Collections\JsonCollect;

class JCollect3 extends JCollect
{
    protected $specification;

    public function getParameters() : JsonCollect
    {
        return $this->cast(
            $this->specification->get('components')->get('parameters')
        );
    }

    public function getResponses() : JsonCollect
    {
        return $this->cast(
            $this->specification->get('components')->get('responses')
        );
    }

    public function getSchemas() : JsonCollect
    {
        return $this->cast(
            $this->specification->get('components')->get('schemas')
        );
    }

    public function getServers() : JsonCollect
    {
        return $this->cast(
            $this->specification->get('servers')
        );
    }
}
