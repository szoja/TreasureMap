<?php

namespace TreasureMap;

use \TreasureMap\Support\Helpers\ArrayHelper;

abstract class Model {
    /*
     * -------------------------------------------------------------------------
     * class variables
     * -------------------------------------------------------------------------     
     */

    /** @var Connection */
    public static $classConnection;

    /** @var string */
    public static $classAdapter;

    /** @var array */
    public static $classOptions;

    /*
     * -------------------------------------------------------------------------
     * instance variables
     * -------------------------------------------------------------------------     
     */

    /**
     * @var ModelAdapter\ModelAdapter
     */
    public $instanceAdapter;

    /**
     *
     * @var array
     */
    public $instanceOptions;

    /**
     * @var Connection
     */
    public $instanceConnection;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * The model attribute's original state.
     *
     * @var array
     */
    protected $original = array();

    /**
     * The model relation instances
     * 
     * @var Relations\Relation[] 
     */
    public $relations = [];

    /**
     * Indicates if the model exists.
     *
     * @var bool
     */
    public $exists = false;

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = array()) {

        /*
         * IF THE LOADED FROM DB, set the MODEL exists
         */

        if ($this->getAttribute($this->getInstanceAdapter()->getLocalKey()) == NULL) {
            $this->exists = FALSE;
        } else {
            $this->exists = TRUE;
        }


        $this->fill($attributes);

//        $this->instanceAdapter = $this->getModelAdapter($this);
//        $this->setInstanceConnection($this->getInstanceConnection());
    }

    /*
     * =========================================================================
     * MAGiC METHODS
     * =========================================================================
     */

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
     * Handle dynamic method calls into the method.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return Builder|mixed
     */
    public function __call($method, $parameters) {

        if (in_array($method, array('increment', 'decrement'))) {
            return call_user_func_array(array($this, $method), $parameters);
        }

        $query = $this->newQuery();

        return call_user_func_array(array($query, $method), $parameters);
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return Builder|mixed
     */
    public static function __callStatic($method, $parameters) {
        $instance = new static;

        return call_user_func_array(array($instance, $method), $parameters);
    }

    /*
     * 
     */

    public function checkExist() {
        /*
         * see in database !!!!
         */
    }

    /*
     * =========================================================================
     * STATIC GET/SET
     * =========================================================================
     */

    public static function getConnection() {
        if (empty(static::$classConnection)) {

            $connection = ConnectionManager::getDefault();
            $connection->setFetchMode(\PDO::FETCH_CLASS, ["class" => get_class(new static)]);
            return $connection;
        } else {
            $connection = ConnectionManager::get(static::$classConnection);
            $connection->setFetchMode(\PDO::FETCH_CLASS, ["class" => get_class(new static)]);

            return $connection;
        }
    }

    /*
     * 
     */

    public static function getClassAdapterName(Model $instance = NULL) {

        if ($instance !== NULL) {
            $reflect = new \ReflectionClass($instance);
        } else {
            $reflect = new \ReflectionClass(new static);
        }

        if ($reflect->getParentClass()) {
            if (static::$classAdapter == self::$classAdapter) {
                return strtolower($reflect->getShortName());
            } else {
                if (empty(static::$classAdapter)) {
                    return strtolower($reflect->getShortName());
                } else {
                    return static::$classAdapter;
                }
            }
        } else {
            if (empty(static::$classAdapter)) {
                return strtolower($reflect->getShortName());
            } else {
                return static::$classAdapter;
            }
        }
    }

    public static function setClassAdapterName($adapterName) {
        static::$classAdapter = $adapterName;
    }

    /**
     * 
     *  @return ModelAdapter\ModelAdapter 
     */
    public static function getClassAdapter(Model $instance = NULL) {
        try {
            return ModelAdapter\ModelAdapterManager::getAdapter(static::getClassAdapterName($instance));
        } catch (\InvalidArgumentException $exc) {
            throw new \InvalidArgumentException($exc->getMessage());
        }
    }

    /*
     * =========================================================================
     * INSTANCE GET/SET
     * =========================================================================
     */

    /*
     * Instance Connection
     */

    public function getInstanceConnection() {

        if ($this->instanceConnection instanceof Connection) {
            return $this->instanceConnection;
        } else {
            if (empty(static::$classConnection)) {

                $connection = ConnectionManager::getDefault();
                $connection->setFetchMode(\PDO::FETCH_CLASS, ["class" => get_class($this)]);
                return $connection;
            } else {
                $connection = ConnectionManager::get(static::$classConnection);
                $connection->setFetchMode(\PDO::FETCH_CLASS, ["class" => get_class($this)]);

                return $connection;
            }
        }
    }

    public function setInstanceConnection(Connection $instanceConnection) {
        $this->instanceConnection = $instanceConnection;
        return $this;
    }

    public function resetToClassConnection() {
        $this->instanceConnection = NULL;
        return $this;
    }

    /*
     * Instance Adapter
     */

    public function getInstanceAdapter() {

        if ($this->instanceAdapter instanceof Connection) {
            return $this->instanceAdapter;
        } else {
            return $this->getClassAdapter($this);
        }
    }

    public function setInstanceAdapter(ModelAdapter\ModelAdapter $instanceAdapter) {
        $this->instanceAdapter = $instanceAdapter;
        return $this;
    }

    public function resetToClassAdapter() {
        $this->instanceAdapter = NULL;
        return $this;
    }

    /*
     * =========================================================================
     * get / set
     * =========================================================================
     */

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key) {

        $inAttributes = array_key_exists($key, $this->getInstanceAdapter()->getFieldMap());
        // If the key references an attribute, we can just go ahead and return the
        // plain attribute value from the model. This allows every attribute to
        // be dynamically accessed through the _get method without accessors.
        if ($inAttributes || $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }
        // If the key already exists in the relationships array, it just means the
        // relationship has already been loaded, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if (method_exists($this, $key)) {
            return $this->relations[$key] = $this->getRelationshipFromMethod($key);
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if (array_key_exists($key, $this->getInstanceAdapter()->getRelations())) {
            return $this->getRelationShipFromAdapterConfig($key);
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

        if (!array_key_exists($key, $this->getInstanceAdapter()->getFieldMap())) {
            throw new \InvalidArgumentException("Bad parameter name: {$key} !");
        }

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
//        elseif (in_array($key, $this->getDates())) {
//            if ($value)
//                return $this->asDateTime($value);
//        }
        return $value;
    }

    /**
     * Get an attribute from the $attributes array.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function getAttributeFromArray($key) {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
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

        $relation = $this->$method();
        if (!$relation instanceof Relation) {
            throw new \LogicException('Relationship method must return an object of type '
            . 'Illuminate\Database\Eloquent\Relations\Relation');
        }
        return $this->relations[$method] = $relation;
    }

    /**
     * 
     * @param type $method
     * @return type
     * @throws \LogicException
     */
    protected function getRelationShipFromAdapterConfig($method) {

        $relationType = $this->getInstanceAdapter()->getRelationManager()->getRelationType($method);

        switch ($relationType) {
            case "belongsTo":

                $relation = new Relations\BelongsTo();

                break;
            case "hasOne":

                $relation = new Relations\HasOne();

                break;
            case "hasMany":

                $relation = new Relations\HasMany();

                break;
            case "hasManyToMany":

                $relation = new Relations\HasManyToMany();

                break;

            default:
                break;
        }


        return $this->relations[$method] = $relation;
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
     * Determine if a set mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasSetMutator($key) {
        return method_exists($this, 'set' . ucfirst($key));
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
        return array_key_exists($key, $this->getInstanceAdapter()->getCasts());
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
        return trim(strtolower($this->getInstanceAdapter()->getCasts()[$key]));
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

    /*
     * =========================================================================
     * relations
     * =========================================================================
     */

    public function hasOne($relatedModel, $foreignKey = null, $localKey = null) {

        $this->relations[strtolower(get_class($relatedModel))] = "instance";
    }

    public function belongsTo($relatedModel, $foreignKey = null, $localKey = null) {
        
    }

    public function hasMany($relatedModel, $foreignKey = null, $localKey = null) {
        
    }

    public function hasManyToMany($relatedModel, $connectorModel, $modelLocalKey = NULL, $relatedLocalKey = NULL, $modelForeignKey = NULL, $relatedForeignKey = NULL) {
        
    }

    /*
     * =========================================================================
     * database functions
     * =========================================================================
     */

    /*
     * =========================================================================
     * FIND METHODS
     * =========================================================================
     */

    /**
     * Get all of the models from the database.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function all($columns = array('*')) {
        $instance = new static;

        return $instance->newQuery()->get($columns);
    }

    /**
     * 
     * @param mixed $id
     * @param array $columns
     * @return Support\Collection|static
     */
    public static function find($id, $columns = array('*')) {

        $instance = new static;

        return $instance->newQuery()->find($id, $columns);
    }

    /**
     * Find a model by its primary key or return new static.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return Support\Collection|static
     */
    public static function findOrNew($id, $columns = array('*')) {
        if (!is_null($model = static::find($id, $columns)))
            return $model;

        return new static;
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return Support\Collection|static
     *
     * @throws \Exception
     */
    public static function findOrFail($id, $columns = array('*')) {
        if (!is_null($model = static::find($id, $columns)))
            return $model;

        throw new \Exception("Model Not found !");
    }

    /*
     * =========================================================================
     * CREATIONAL METHODS
     * =========================================================================
     */

    /**
     * Save a new model and return the instance.
     *
     * @param  array  $attributes
     * @return static
     */
    public static function create(array $attributes) {
        $model = new static($attributes);

        // @TODO saving
//        $model->save();

        return $model;
    }

    /**
     * Get the first model for the given attributes.
     *
     * @param  array  $attributes
     * @return static|null
     */
    protected static function firstByAttributes($attributes) {

        $instance = new static;

        $builder = $instance->newQuery();

        foreach ($attributes as $key => $value) {
            $builder->where($key, "=", $value, "and");
        }

        return $builder->get();
    }

    /**
     * Get the first record matching the attributes or create it.
     *
     * @param  array  $attributes
     * @return static
     */
    public static function firstOrCreate(array $attributes) {

        if (!is_null($instance = static::firstByAttributes($attributes)->first())) {
            return $instance;
        }

        return static::create($attributes);
    }

    /**
     * Get the first record matching the attributes or instantiate it.
     *
     * @param  array  $attributes
     * @return static
     */
    public static function firstOrNew(array $attributes) {

        if (!is_null($instance = static::firstByAttributes($attributes)->first())) {
            return $instance;
        }

        return new static($attributes);
    }

    /**
     * Create or update a record matching the attributes, and fill it with values.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return static
     */
    public static function updateOrCreate(array $attributes, array $values = array()) {

        $instance = static::firstOrNew($attributes);

        $instance->fill($values)->save();

        return $instance;
    }

    public static function delete($param) {
        
    }

    /*
     * =========================================================================
     * BUILDER STUFF
     * =========================================================================
     */

    public function newQuery() {
        $builder = $this->newBuilder();
        $builder->setModel($this);
        return $builder;
    }

    /**
     * 
     * @return \TreasureMap\Builder
     */
    protected function newBuilder() {
        return new Builder($this->newBaseQueryBuilder());
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return Query\Builders\TreasurePath
     */
    protected function newBaseQueryBuilder() {
        return new Query\Builders\TreasurePath($this->getInstanceConnection());
    }

    /*
     * =========================================================================
     * helpers
     * =========================================================================
     */

    /*
     * --- fill ----------------------------------------------------------------
     */

    /**
     * Fills the model by the given array.
     * 
     * @param array $attributes
     * @param bool $forceFillableValues         TRUE -> Selects only the fillable values and sets to the model.
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function fill(array $attributes, $forceFillableValues = FALSE) {

        if ($forceFillableValues) {
            $attributes = $this->fillableFromArray($attributes);
        }

        try {
            $this->checkFillableFromArray($attributes);
        } catch (\InvalidArgumentException $exc) {
            throw new \InvalidArgumentException($exc->getMessage());
        }

        return $this->fillFromArray($attributes);
    }

    /**
     * 
     * @param array $attributes
     * @param bool $ignoreExistance
     * @return bool
     */
    public function forceFill(array $attributes, $ignoreExistance = TRUE) {
        if (!$ignoreExistance) {
            $this->checkFillableFromArray($attributes);
        }

        return $this->fillFromArray($attributes);
    }

    /**
     * Get the fillable attributes of a given array.
     *
     * @param  array  $attributes
     * @return array     
     */
    protected function getFillableFromArray(array $attributes) {

        $fillableFields = $this->getInstanceAdapter()->getFillableFields();

        if (count($fillableFields) > 0) {
            return array_intersect_key($attributes, array_flip($fillableFields));
        }

        return $attributes;
    }

    /**
     * Checks the array of attributes are valid for filling the model.
     * 
     * @param array $attributes
     * @return boolean
     * @throws \InvalidArgumentException
     */
    protected function checkFillableFromArray(array $attributes) {
        foreach ($attributes as $key => $value) {

            if (!$this->getInstanceAdapter()->existInFillable($key)) {
                throw new \InvalidArgumentException("The following value isn't fillable ! {$key}");
                return FALSE;
            }
        }

        return TRUE;
    }

    protected function fillFromArray(array $attributes) {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return TRUE;
    }

    /*
     * =========================================================================
     * incrementation / attributes
     * =========================================================================
     */

    /**
     * Increment a column's value by a given amount.
     *
     * @param  string  $column
     * @param  int     $amount
     * @return int
     */
    protected function increment($column, $amount = 1) {
        return $this->incrementOrDecrement($column, $amount, 'increment');
    }

    /**
     * Decrement a column's value by a given amount.
     *
     * @param  string  $column
     * @param  int     $amount
     * @return int
     */
    protected function decrement($column, $amount = 1) {
        return $this->incrementOrDecrement($column, $amount, 'decrement');
    }

    /**
     * Run the increment or decrement method on the model.
     *
     * @param  string  $column
     * @param  int     $amount
     * @param  string  $method
     * @return int
     */
    protected function incrementOrDecrement($column, $amount, $method) {
        $query = $this->newQuery();

        if (!$this->exists) {
            return $query->{$method}($column, $amount);
        }

        $this->incrementOrDecrementAttributeValue($column, $amount, $method);

        return $query->where($this->getKeyName(), $this->getKey())->{$method}($column, $amount);
    }

    /**
     * Increment the underlying attribute value and sync with original.
     *
     * @param  string  $column
     * @param  int     $amount
     * @param  string  $method
     * @return void
     */
    protected function incrementOrDecrementAttributeValue($column, $amount, $method) {
        $this->{$column} = $this->{$column} + ($method == 'increment' ? $amount : $amount * -1);

        $this->syncOriginalAttribute($column);
    }

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param  array  $attributes
     * @param  bool   $sync
     * @return void
     */
    public function setRawAttributes(array $attributes, $sync = false) {
        $this->attributes = $attributes;

        if ($sync)
            $this->syncOriginal();
    }

    /**
     * Get the model's original attribute values.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return array
     */
    public function getOriginal($key = null, $default = null) {
        return ArrayHelper::array_get($this->original, $key, $default);
    }

    /**
     * Sync the original attributes with the current.
     *
     * @return $this
     */
    public function syncOriginal() {
        $this->original = $this->attributes;

        return $this;
    }

    /**
     * Sync a single original attribute with its current value.
     *
     * @param  string  $attribute
     * @return $this
     */
    public function syncOriginalAttribute($attribute) {
        $this->original[$attribute] = $this->attributes[$attribute];

        return $this;
    }

    /**
     * Determine if the model or given attribute(s) have been modified.
     *
     * @param  array|string|null  $attributes
     * @return bool
     */
    public function isDirty($attributes = null) {
        $dirty = $this->getDirty();

        if (is_null($attributes))
            return count($dirty) > 0;

        if (!is_array($attributes))
            $attributes = func_get_args();

        foreach ($attributes as $attribute) {
            if (array_key_exists($attribute, $dirty))
                return true;
        }

        return false;
    }

    /**
     * Get the attributes that have been changed since last sync.
     *
     * @return array
     */
    public function getDirty() {
        $dirty = array();

        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original)) {
                $dirty[$key] = $value;
            } elseif ($value !== $this->original[$key] &&
                    !$this->originalIsNumericallyEquivalent($key)) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Determine if the new and old values for a given key are numerically equivalent.
     *
     * @param  string  $key
     * @return bool
     */
    protected function originalIsNumericallyEquivalent($key) {
        $current = $this->attributes[$key];

        $original = $this->original[$key];

        return is_numeric($current) && is_numeric($original) && strcmp((string) $current, (string) $original) === 0;
    }

    /*
     * TODO setKeysForSaveQuery
     */

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(Builder $query) {
        $query->where($this->getKeyName(), '=', $this->getKeyForSaveQuery());

        return $query;
    }

    /**
     * Get the primary key value for a save query.
     *
     * @return mixed
     */
    protected function getKeyForSaveQuery() {
        if (isset($this->original[$this->getKeyName()])) {
            return $this->original[$this->getKeyName()];
        }

        return $this->getAttribute($this->getKeyName());
    }

    /*
     * 
     */

    /**
     * Perform a model update operation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $options
     * @return bool|null
     */
    protected function performUpdate(Builder $query, array $options = []) {
        $dirty = $this->getDirty();

        if (count($dirty) > 0) {
            // If the updating event returns false, we will cancel the update operation so
            // developers can hook Validation systems into their models and cancel this
            // operation if the model does not pass validation. Otherwise, we update.
            if ($this->fireModelEvent('updating') === false) {
                return false;
            }

            // @TODO model timestamps from model adapter
            // First we need to create a fresh query instance and touch the creation and
            // update timestamp on the model which are maintained by us for developer
            // convenience. Then we will just continue saving the model instances.
            if ($this->timestamps && ArrayHelper::array_get($options, 'timestamps', true)) {
                $this->updateTimestamps();
            }


            // Once we have run the update operation, we will fire the "updated" event for
            // this model instance. This will allow developers to hook into these after
            // models are updated, giving them a chance to do any special processing.
            $dirty = $this->getDirty();

            if (count($dirty) > 0) {
                $this->setKeysForSaveQuery($query)->update($dirty);

                // @TODO model EVENT

                $this->fireModelEvent('updated', false);
            }
        }

        return true;
    }

    /**
     * Perform a model insert operation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $options
     * @return bool
     */
    protected function performInsert(Builder $query, array $options = []) {
        if ($this->fireModelEvent('creating') === false)
            return false;

        // First we'll need to create a fresh query instance and touch the creation and
        // update timestamps on this model, which are maintained by us for developer
        // convenience. After, we will just continue saving these model instances.
        if ($this->timestamps && ArrayHelper::array_get($options, 'timestamps', true)) {
            $this->updateTimestamps();
        }

        // If the model has an incrementing key, we can use the "insertGetId" method on
        // the query builder, which will give us back the final inserted ID for this
        // table from the database. Not all tables have to be incrementing though.
        $attributes = $this->attributes;

        if ($this->incrementing) {
            $this->insertAndSetId($query, $attributes);
        }

        // If the table is not incrementing we'll simply insert this attributes as they
        // are, as this attributes arrays must contain an "id" column already placed
        // there by the developer as the manually determined key for these models.
        else {
            $query->insert($attributes);
        }

        // We will go ahead and set the exists property to true, so that it is set when
        // the created event is fired, just in case the developer tries to update it
        // during the event. This will allow them to do so and run an update here.
        $this->exists = true;

        $this->fireModelEvent('created', false);

        return true;
    }

    /**
     * Insert the given attributes and set the ID on the model.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $attributes
     * @return void
     */
    protected function insertAndSetId(Builder $query, $attributes) {
        $id = $query->insertGetId($attributes, $keyName = $this->getKeyName());

        $this->setAttribute($keyName, $id);
    }

    /*
     * --- basic conversion ----------------------------------------------------
     */

    public function toArray() {
        
    }

    public function toJson() {
        
    }

    public function dump() {
        
    }

}
