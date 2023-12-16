<?php

namespace TestMonitor\DevOps\Support;

class Arrays
{
    /**
     * Flatten a multi-dimensional associative array.
     *
     * @param array $array
     *
     * @return array
     */
    public static function flatten(array $array): array
    {
        return array_merge(
            ...array_values($array)
        );
    }

    /**
     * Takes an associative array and returns a new array without duplicate items.
     *
     * @param array $array
     *
     * @return array
     */
    public static function unique(array $array, string $field)
    {
        return array_intersect_key(
            $array,
            array_unique(
                array_column($array, $field)
            )
        );
    }
}
