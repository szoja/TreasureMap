<?php

namespace TreasureMap;

class ConnectionManager {

    /** @var ModelAdapter[] */
    protected static $instances;
    protected static $default;
    protected static $configFilePath = "";
    protected static $fetch;
    protected static $fetchOptions;

    /**
     * Initialize connections from configuration file
     * 
     * @param string $pathToConfigFile
     * @return ConnectionManager
     * @throws \Exception
     */
    public static function init($pathToConfigFile) {
        if (!file_exists("{$pathToConfigFile}")) {
            throw new \Exception("The config file doesn't exist !");
        }


        $configArray = require_once "{$pathToConfigFile}";

        self::$default = $configArray["default"];

        return self::createManyFromArray($configArray["connections"]);
    }

    /**
     * @return Connection
     */
    public static function getDefault() {
        return self::$instances[self::$default];
    }

    /**
     * @return Connection
     */
    public static function get($connectorName) {
        if (isset(self::$instances[$connectorName])) {
            return self::$instances[$connectorName];
        } else {
            throw new \InvalidArgumentException("The connection not isset in the manager Instances !");
        }
    }

    /**
     * Creates new ModelAdapter instance, and register to the manager by the given name.
     * Returns the created instance.
     * 
     * @param string $name
     * @param array $config
     * @return ModelAdapter
     */
    public static function createNewFromArray($name, array $config) {

        self::$instances[$name] = self::createSingleConnection($config);
        return self::$instances[$name];
    }

    /**
     * Creates new ModelAdapter instances, by the configuration array.
     * Returns the AdapterManager instance.
     * 
     * @param array $configArray
     * @return ModelAdapterManager
     */
    public static function createManyFromArray(array $configArray) {
        foreach ($configArray as $name => $config) {
            self::createNewFromArray($name, $config);
        }

        return __CLASS__;
    }

    protected static function createConnector(array $config) {

        switch ($config["driver"]) {
            case "mysql":
                return new Connectors\MySqlConnector();
                break;

            default:
                break;
        }
    }

    protected static function createConnection($driver, \PDO $connection, $database, $prefix = '', array $config = array()) {

        switch ($driver) {
            case "mysql":
                return new Connection($connection, $database, $prefix, $config);
                break;
            default:
                break;
        }
    }

    /**
     * Create a single database connection instance.
     *
     * @param  array  $config
     * @return \Illuminate\Database\Connection
     */
    protected static function createSingleConnection(array $config) {
        $pdo = self::createConnector($config)->connect($config);
        return self::createConnection($config['driver'], $pdo, $config['database'], $config['prefix'], $config);
    }

    /*
     * 
     */

    static function getFetch() {
        return self::$fetch;
    }

    static function getFetchOptions() {
        return self::$fetchOptions;
    }

    static function setFetch($fetch) {
        self::$fetch = $fetch;
        return self;
    }

    static function setFetchOptions($fetchOptions) {
        self::$fetchOptions = $fetchOptions;
        return self;
    }

}
