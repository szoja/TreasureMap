<?php

namespace TreasureMap\ModelAdapter;

class ModelAdapter implements \Interfaces\iModelAdapter {

    protected $source;
    protected $fieldMap;
    protected $localKey;
    protected $relations;
    protected $casts;

    function __construct($adapterConfig) {
        foreach ($this as $adapterKey => $adapterValue) {
            if (isset($adapterConfig[$adapterKey])) {
                $this->{$adapterKey} = $adapterConfig[$adapterKey];
            }
        }
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
        return (isset($this->casts[$variableName])) ? TRUE : FALSE;
    }

    public function existInReferenceKeys($variableName) {
        return (isset($this->relations[$variableName])) ? TRUE : FALSE;
    }

    public function addSourcePrefix($value) {
        return $this->source . "." . $value;
    }

    /*
     * get / set
     */

    function getSource() {
        return $this->source;
    }

    function getFieldMap() {
        return $this->fieldMap;
    }

    function getLocalKey() {
        return $this->localKey;
    }

    function getRelations() {
        return $this->relations;
    }

    function getCasts() {
        return $this->casts;
    }

    function setSource($source) {
        $this->source = $source;
    }

    function setFieldMap($fieldMap) {
        $this->fieldMap = $fieldMap;
    }

    function setLocalKey($localKey) {
        $this->localKey = $localKey;
    }

    function setRelations($relations) {
        $this->relations = $relations;
    }

    function setCasts($casts) {
        $this->casts = $casts;
    }

}
