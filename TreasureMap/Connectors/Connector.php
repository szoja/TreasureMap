<?php

namespace TreasureMap\Connectors;

use \PDO as PDO;
use \TreasureMap\Support\Helpers\ArrayHelper;

class Connector {

    /**
     * The default PDO connection options.
     *
     * @var array
     */
    protected $options = array(
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
    );

    /**
     * Get the PDO options based on the configuration.
     *
     * @param  array  $config
     * @return array
     */
    public function getOptions(array $config) {                
        $options = ArrayHelper::array_get($config, 'options', array());
        return array_diff_key($this->options, $options) + $options;
    }

    /**
     * Create a new PDO connection.
     *
     * @param  string  $dsn
     * @param  array   $config
     * @param  array   $options
     * @return \PDO
     */
    public function createConnection($dsn, array $config, array $options) {
        $username = ArrayHelper::array_get($config, 'username');
        $password = ArrayHelper::array_get($config, 'password');
        return new PDO($dsn, $username, $password, $options);
    }

    /**
     * Get the default PDO connection options.
     *
     * @return array
     */
    public function getDefaultOptions() {
        return $this->options;
    }

    /**
     * Set the default PDO connection options.
     *
     * @param  array  $options
     * @return void
     */
    public function setDefaultOptions(array $options) {
        $this->options = $options;
    }

}

/**
 * Get an item from an array using "dot" notation.
 *
 * @param  array   $array
 * @param  string  $key
 * @param  mixed   $default
 * @return mixed
 */

/*
if (!function_exists('array_get')) {

    function array_get($array, $key, $default = null) {
        if (is_null($key))
            return $array;

        if (isset($array[$key]))
            return $array[$key];

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return value($default);
            }

            $array = $array[$segment];
        }

        return $array;
    }

} 
*/ 
