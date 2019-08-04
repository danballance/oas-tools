<?php

namespace DanBallance\OasTools\Specification\Fragments;

class Schema extends Fragment
{
    public function getProperties() : array
    {
        if (!isset($this->schema['properties'])) {
            return [];
        }
        return $this->schema['properties'];
    }

    public function isRequired(string $propertyName) : bool
    {
        if (!isset($this->schema['required'])) {
            return false;
        }
        return in_array($propertyName, $this->schema['required']);
    }
}
