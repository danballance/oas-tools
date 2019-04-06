<?php

namespace DanBallance\OasTools\Utils;

trait ArrayUtil
{
    /**
     * Find item in array and replace it with another value
     * @param array $array
     * @param $find
     * @param $replace
     * @return array
     */
    protected function arraySwap(array $array, $find, $replace) : array
    {
        if (in_array($find, $array)) {
            $key = array_search($find, $array);
            $array[$key] = $replace;
        }
        return $array;
    }

    /**
     * Find item in array and remove it
     * @param array $array
     * @param $find
     * @return array
     */
    protected function arrayRemove(array $array, $find) : array
    {
        if (in_array($find, $array)) {
            $key = array_search($find, $array);
            unset($array[$key]);
        }
        return array_values($array);
    }
}
