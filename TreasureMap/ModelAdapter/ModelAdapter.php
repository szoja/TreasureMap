<?php

namespace TreasureMap\ModelAdapter;

class ModelAdapter {

    protected $source;
    protected $map;
    protected $localKey;
    protected $referenceKeys;
    protected $cast;

    public function getMapRef($variableName, $sourcePrefix = FALSE) {

        if ($this->existInMap($variableName)) {
            if ($sourcePrefix) {
                return $this->addSourcePrefix($this->map[$variableName]);
            } else {
                return $this->map[$variableName];
            }
        } else {
            throw new \InvalidArgumentException("The following variable doesn't exist in the map ! : {$variableName}");
        }
    }

    public function existInMap($variableName) {
        return (isset($this->map[$variableName])) ? TRUE : FALSE;
    }

    public function existInCast($variableName) {
        return (isset($this->cast[$variableName])) ? TRUE : FALSE;
    }

    public function existInReferenceKeys($variableName) {
        return (isset($this->referenceKeys[$variableName])) ? TRUE : FALSE;
    }

    public function addSourcePrefix($value) {
        return $this->source . "." . $value;
    }

}
