<?php

namespace TreasureMap\ModelAdapter;

class ConnectorsManager {

    /** @var ModelAdapter[] */
    protected static $instances;

    /**
     * Returns the ModelAdapter instance by it's name.
     * 
     * @param string $adapterName
     * @return ModelAdapter
     * @throws \InvalidArgumentException
     */
    public static function getAdapter($adapterName) {
        if (isset(self::$instances[$adapterName])) {
            return self::$instances[$adapterName];
        } else {
            throw new \InvalidArgumentException("The adapter not isset in the manager Instances !");
        }
    }

    /**
     * Creates new ModelAdapter instance, and register to the manager by the given name.
     * Returns the created instance.
     * 
     * @param string $name
     * @param array $adapterConfig
     * @return ModelAdapter
     */
    public static function createNewFromArray($name, array $adapterConfig) {
        self::$instances[$name] = new ModelAdapter($adapterConfig);
        return self::$instances[$name];
    }

    /**
     * Creates new ModelAdapter instances, by the configuration array.
     * Returns the AdapterManager instance.
     * 
     * @param array $adapterConfigArray
     * @return ModelAdapterManager
     */
    public static function createManyFromArray(array $adapterConfigArray) {
        foreach ($adapterConfigArray as $adapterName => $adapterConfig) {
            self::createNewFromArray($adapterName, $adapterConfig);
        }

        return self;
    }

    /**
     * Creates new ModelAdapter instance from config file, and register to the manager by the given name.
     * Returns the created instance.
     * 
     * @param string $name
     * @param string $pathToConfigFile
     * @return ModelAdapter
     * @throws \Exception
     */
    public function createNewFromFile($name, $pathToConfigFile) {
        if (!file_exists("{$pathToConfigFile}")) {
            throw new \Exception("The config file doesn't exist !");
        }

        $adapterConfig = require_once "{$pathToConfigFile}";
        return $this->createNewFromArray($name, $adapterConfig);
    }

    /**
     * Creates new ModelAdapter instances, by the configuration file.
     * Returns the AdapterManager instance.     
     * 
     * @param string $pathToConfigFile
     * @return ModelAdapterManager
     * @throws \Exception
     */
    public static function createManyFromFile($pathToConfigFile) {
        if (!file_exists("{$pathToConfigFile}")) {
            throw new \Exception("The config file doesn't exist !");
        }

        $adapterConfigArray = require_once "{$pathToConfigFile}";

        return self::createManyFromArray($adapterConfigArray);
    }

}
