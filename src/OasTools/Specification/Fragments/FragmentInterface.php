<?php

namespace DanBallance\OasTools\Specification\Fragments;

interface FragmentInterface
{
    public function getSpec();
    public function toArray() : array;
    /**
     * @return false|string
     */
    public function toJson();
    public function path() : string;
}
