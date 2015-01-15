<?php

namespace TreasureMap;

class Model {

    public static $connector;

    /** @var ModelAdapter\ModelAdapter */
    public static $modelAdapter;
    public $instanceAdapter;

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key) {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value) {
        $this->setAttribute($key, $value);
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key) {
        $inAttributes = array_key_exists($key, static::$modelAdapter->getFieldMap());
        // If the key references an attribute, we can just go ahead and return the
        // plain attribute value from the model. This allows every attribute to
        // be dynamically accessed through the _get method without accessors.
        if ($inAttributes || $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }
        // If the key already exists in the relationships array, it just means the
        // relationship has already been loaded, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if (array_key_exists($key, static::$modelAdapter->getRelations())) {
            return static::$modelAdapter->getRelations()[$key];
        }
        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }
    }

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param  string  $key
     * @return mixed
     */
    protected function getAttributeValue($key) {
        $value = $this->getAttributeFromArray($key);
        // If the attribute has a get mutator, we will call that then return what
        // it returns as the value, which is useful for transforming values on
        // retrieval from the model to a form that is more useful for usage.
        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }
        // If the attribute exists within the cast array, we will convert it to
        // an appropriate native PHP type dependant upon the associated value
        // given with the key in the pair. Dayle made this comment line up.
        if ($this->hasCast($key)) {
            $value = $this->castAttribute($key, $value);
        }
        // If the attribute is listed as a date, we will convert it to a DateTime
        // instance on retrieval, which makes it quite convenient to work with
        // date fields without having to create a mutator for each property.
        elseif (in_array($key, $this->getDates())) {
            if ($value)
                return $this->asDateTime($value);
        }
        return $value;
    }

    /**
     * Get an attribute from the $attributes array.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function getAttributeFromArray($key) {
        if (array_key_exists($key, static::$modelAdapter->getFieldMap())) {
            return $this->{$key};
        }
    }

    /**
     * Get a relationship value from a method.
     *
     * @param  string  $method
     * @return mixed
     *
     * @throws \LogicException
     */
    protected function getRelationshipFromMethod($method) {
        $relations = $this->$method();
        if (!$relations instanceof Relation) {
            throw new LogicException('Relationship method must return an object of type '
            . 'Illuminate\Database\Eloquent\Relations\Relation');
        }
        return $this->relations[$method] = $relations->getResults();
    }

    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasGetMutator($key) {
        return method_exists($this, 'get' . ucfirst($key));
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return mixed
     */
    protected function mutateAttribute($key, $value) {
        return $this->{'get' . ucfirst($key)}($value);
    }

    /**
     * Determine whether an attribute should be casted to a native type.
     *
     * @param  string  $key
     * @return bool
     */
    protected function hasCast($key) {
        return array_key_exists($key, static::$modelAdapter->getCasts());
    }

    /**
     * Determine whether a value is JSON castable for inbound manipulation.
     *
     * @param  string  $key
     * @return bool
     */
    protected function isJsonCastable($key) {
        if ($this->hasCast($key)) {
            $type = $this->getCastType($key);
            return $type === 'array' || $type === 'json' || $type === 'object';
        }
        return false;
    }

    /**
     * Get the type of cast for a model attribute.
     *
     * @param  string  $key
     * @return string
     */
    protected function getCastType($key) {
        return trim(strtolower(static::$modelAdapter->getCasts()[$key]));
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return mixed
     */
    protected function castAttribute($key, $value) {
        switch ($this->getCastType($key)) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'object':
                return json_decode($value);
            case 'array':
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function setAttribute($key, $value) {
        // First we will check for the presence of a mutator for the set operation
        // which simply lets the developers tweak the attribute as it is set on
        // the model, such as "json_encoding" an listing of data for storage.
        if ($this->hasSetMutator($key)) {
            $method = 'set' . ucfirst($key);
            return $this->{$method}($value);
        }

        if ($this->isJsonCastable($key)) {
            $value = json_encode($value);
        }
        $this->attributes[$key] = $value;
    }

    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasSetMutator($key) {
        return method_exists($this, 'set' . ucfirst($key));
    }

    /*
     * db functions
     */

    public function find() {
        
    }

    public function save() {
        
    }

    /*
     * relations
     */

    public function hasOne($referenceModel, $foreignKey, $localKey = 'id') {
        
    }

    public function belongsTo($referenceModel, $localKey, $parentKey = 'id') {
        
    }

    public function hasMany($referenceModel, $foreignKey, $localKey = 'id') {
        
    }

    public function hasManyToMany($localKey = 'id', $connectorModel, $connectorField, $referenceModel, $referencedField, array $options = NULL) {
        
    }

    /*
     * helpers
     */

    public function toArray() {
        
    }

    public function toJSON() {
        
    }

    public function dump() {
        
    }

}
