<?php

namespace TreasureMap\Support\Helpers;

class ArrayHelper {

    public static function array_get($array, $key, $default = null) {
        if (is_null($key))
            return $array;

        if (isset($array[$key]))
            return $array[$key];

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default instanceof Closure ? $default() : $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param  array  $array
     * @return array
     */
    public static function array_flatten($array) {
        $return = array();

        array_walk_recursive($array, function($x) use (&$return) {
            $return[] = $x;
        });

        return $return;
    }

    /**
     * Flatten a multi-dimensional array into a single level, merge the keys.
     *
     * @param  array  $array
     * @return array
     */
    public static function array_flatten_with_keys($array) {

        $return = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $return = array_merge($return, self::array_flatten_with_keys($value));
            } else {
                $return[$key] = $value;
            }
        }
        return $return;
    }

}
