<?php

namespace TreasureMap\ModelAdapter;

class ModelAdapter implements \Interfaces\iModelAdapter {

    protected $source;
    protected $fieldMap;
    protected $localKey;
    protected $references;
    protected $cast;

    function __construct($source, $fieldMap, $localKey, array $references = [], array $cast = []) {
        $this->source = $source;
        $this->fieldMap = $fieldMap;
        $this->localKey = $localKey;
        $this->references = $references;
        $this->cast = $cast;
    }

    public function getMapRef($variableName, $sourcePrefix = FALSE) {

        if ($this->existInMap($variableName)) {
            if ($sourcePrefix) {
                return $this->addSourcePrefix($this->fieldMap[$variableName]);
            } else {
                return $this->fieldMap[$variableName];
            }
        } else {
            throw new \InvalidArgumentException("The following variable doesn't exist in the map ! : {$variableName}");
        }
    }

    public function existInMap($variableName) {
        return (isset($this->fieldMap[$variableName])) ? TRUE : FALSE;
    }

    public function existInCast($variableName) {
        return (isset($this->cast[$variableName])) ? TRUE : FALSE;
    }

    public function existInReferenceKeys($variableName) {
        return (isset($this->references[$variableName])) ? TRUE : FALSE;
    }

    public function addSourcePrefix($value) {
        return $this->source . "." . $value;
    }

}
