<?php

namespace TreasureMap\ModelAdapter;

class ModelAdapterRelationManager {

    /** @var ModelAdapter */
    protected $modelAdapter;

    function __construct($modelAdapter) {
        $this->modelAdapter = $modelAdapter;
    }

    /*
     * -------------------------------------------------------------------------
     * relation checks
     * -------------------------------------------------------------------------
     */

    public function hasRelations() {
        return (count($this->modelAdapter->getRelations())) ? TRUE : FALSE;
    }

    public function hasRelationType($relationType) {

        if ($this->hasRelations()) {

            foreach ($this->modelAdapter->getRelations() as $relationField => $relationData) {
                if ($relationData["type"] == $relationType) {
                    return TRUE;
                }
            }

            return FALSE;
        } else {
            return FALSE;
        }
    }

    public function checkHasBelongsTo() {
        return $this->hasRelationType("belongsTo");
    }

    public function checkHasOne() {
        return $this->hasRelationType("hasOne");
    }

    public function checkHasMany() {
        return $this->hasRelationType("hasMany");
    }

    public function checkHasManyToMany() {
        return $this->hasRelationType("manyToMany");
    }

    /*
     * 
     */

    public function getRelationVariables() {
        return array_keys($this->modelAdapter->getRelations());
    }

    public function getRelationConfig($variableName) {
        if ($this->modelAdapter->existInRelations($variableName)) {
            return $this->modelAdapter->getRelations()[$variableName];
        } else {
            throw new \InvalidArgumentException("The following variable doesn't exist in relations ! : {$variableName}");
        }
    }

    public function getRelationType($variableName) {
        if (!$this->modelAdapter->existInRelations($variableName)) {
            throw new \InvalidArgumentException("The following variable doesn't exist in relations ! : {$variableName}");
        }

        if (isset($this->modelAdapter->getRelations()[$variableName]["type"])) {
            return $this->modelAdapter->getRelations()[$variableName]["type"];
        } else {
            throw new \InvalidArgumentException("The following relation doesn't have type ! : {$variableName}");
        }
    }

    public function getRelationOptions($variableName) {

        if (!$this->modelAdapter->existInRelations($variableName)) {
            throw new \InvalidArgumentException("The following variable doesn't exist in relations ! : {$variableName}");
        }

        if (!isset($this->modelAdapter->relations[$variableName]["type"])) {
            throw new \InvalidArgumentException("The following relation doesn't have type ! : {$variableName}");
        }

        if (!isset($this->modelAdapter->relations[$variableName]["options"])) {
            throw new \InvalidArgumentException("The following relation doesn't have options ! : {$variableName}");
        }

        /*
         * public function hasOne($referenceModel, $foreignKey, $localKey = 'id') {

          }

          public function belongsTo($referenceModel, $localKey, $parentKey = 'id') {

          }

          public function hasMany($referenceModel, $foreignKey, $localKey = 'id') {

          }

          public function hasManyToMany($localKey = 'id', $connectorModel, $connectorField, $referenceModel, $referencedField, array $options = NULL) {

          }
         */


        $options = $this->relations[$variableName]["options"];

        // set the local key

        if (!isset($options["localKey"])) {
            $options["localKey"] = $this->localKey;
        }

        switch ($this->relations[$variableName]["type"]) {
            case "belongsTo":


                $return = [
                ];

                break;
            case "hasOne":

                break;
            case "hasMany":

                break;
            case "hasManyToMany":

                break;

            default:
                break;
        }
    }

}
