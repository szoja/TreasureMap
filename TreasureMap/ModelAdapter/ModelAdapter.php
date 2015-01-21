<?php

namespace TreasureMap\ModelAdapter;

use TreasureMap\Interfaces;

class ModelAdapter implements Interfaces\iModelAdapter {

    protected $source;
    protected $fieldMap;
    protected $localKey;
    protected $relations;
    protected $casts;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array();

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = array('*');

    function __construct($adapterConfig) {

        $this->initConfiguration($adapterConfig);
    }

    public function initConfiguration($adapterConfig) {

        // Set the configuration values if they exist
        foreach ($this as $adapterKey => $adapterValue) {
            if (isset($adapterConfig[$adapterKey])) {
                $this->{$adapterKey} = $adapterConfig[$adapterKey];
            }
        }

        // By Default all fields are fillable.
        if (!isset($adapterConfig["fillableFields"])) {
            $this->fillable = array_keys($adapterConfig["fieldMap"]);
        }

        if (!isset($adapterConfig["relations"])) {
            $this->relations = $adapterConfig["relations"];
        }
    }

    public function getFieldMapRef($variableName, $sourcePrefix = FALSE) {

        if ($this->existInFieldMap($variableName)) {
            if ($sourcePrefix) {
                return $this->addSourcePrefix($this->fieldMap[$variableName]);
            } else {
                return $this->fieldMap[$variableName];
            }
        } else {
            throw new \InvalidArgumentException("The following variable doesn't exist in the map ! : {$variableName}");
        }
    }

    public function existInFieldMap($variableName) {
        return (isset($this->fieldMap[$variableName])) ? TRUE : FALSE;
    }

    public function existInCasts($variableName) {
        return (isset($this->casts[$variableName])) ? TRUE : FALSE;
    }

    public function existInRelations($variableName) {
        return (isset($this->relations[$variableName])) ? TRUE : FALSE;
    }

    public function existInFillable($variableName) {
        return (in_array($variableName, $this->fillable)) ? TRUE : FALSE;
    }

    public function addSourcePrefix($value) {
        return $this->source . "." . $value;
    }

    /*
     * relations
     */

    public function getRelationManager() {
        return new ModelAdapterRelationManager($this);
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

    function getFillableFields() {
        return $this->fillable;
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

    function setFillableFields($fillableFields) {
        $this->fillable = $fillableFields;
    }

}
