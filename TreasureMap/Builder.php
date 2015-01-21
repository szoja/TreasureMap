<?php

namespace TreasureMap;

class Builder {

    /**
     * Model being queried
     * 
     * @var Model 
     */
    protected $model;

    /**
     * Trasure Map query builder
     * 
     * @var Query\Builders\TreasurePath 
     */
    protected $query;

    /**
     * Eager loadings,
     * 
     * - array containts the relation names
     * 
     * @var array
     */
    protected $eagers;

    /**
     * The methods that should be returned from query builder.
     *
     * @var array
     */
    protected $passthru = array(
        'toSql', 'lists', 'insert', 'insertGetId', 'pluck', 'count',
        'min', 'max', 'avg', 'sum', 'exists', 'getBindings',
    );

    /**
     * Create a new TreasureMap query builder instance.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return void
     */
    public function __construct(Query\Builders\TreasurePath $query) {
        $this->query = $query;
    }

    /*
     * =========================================================================
     * MAGIC METHODS
     * =========================================================================
     */

    /**
     * Dynamically handle calls into the query instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters) {
//        if (isset($this->macros[$method])) {
//            array_unshift($parameters, $this);
//
//            return call_user_func_array($this->macros[$method], $parameters);
//        } elseif (method_exists($this->model, $scope = 'scope' . ucfirst($method))) {
//            return $this->callScope($scope, $parameters);
//        }

        $result = call_user_func_array(array($this->query, $method), $parameters);

        return in_array($method, $this->passthru) ? $result : $this;
    }

    /**
     * 
     * @return Model
     */
    function getModel() {
        return $this->model;
    }

    /**
     * 
     * @param \TreasureMap\Model $model
     * @return \TreasureMap\Builder
     */
    function setModel(Model $model) {
        $this->model = $model;
        return $this;
    }

    /**
     * Find a model by its primary key.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return Model|static|null
     */
    public function find($id, $columns = array('*')) {

        $this->query->from($this->model->getInstanceAdapter()->getSource());

        $localKeyName = $this->model->getInstanceAdapter()->getLocalKey();

        $this->query->whereRaw("{$localKeyName} = :{$localKeyName}", ["{$localKeyName}" => $id]);


        if (is_array($id)) {
            return $this->findMany($id, $columns);
        } else {
            return $this->get($columns)->first();
        }
    }

    /**
     * Find a model by its primary key.
     *
     * @param  array  $id
     * @param  array  $columns
     * @return Model|Collection|static
     */
    public function findMany($id, $columns = array('*')) {

        $localKeyName = $this->model->getInstanceAdapter()->getLocalKey();

        if (is_array($id)) {
            $idList = implode(", ", $id);
            $this->query->whereRaw("{$localKeyName} IN (:{$localKeyName})", ["{$localKeyName}" => $idList]);
        } else {
            $this->query->whereRaw("{$localKeyName} IN (:{$localKeyName})", ["{$localKeyName}" => $id]);
        }

        return $this->getAll($columns);
    }

    /**
     * 
     * @param mixed $id
     * @param array $columns
     * @return Model|static
     * 
     * @throws \Exception
     */
    public function findOrFail($id, $columns = array('*')) {
        if (!is_null($model = $this->find($id, $columns))) {
            return $model;
        }

        throw new Exception("Model Not found !");
    }

    /**
     * Execute the query and get the first result.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    public function first($columns = array('*')) {
        return $this->get($columns)->first();
    }

    /**
     * Execute the query and get the first result or throw an exception.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|static
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function firstOrFail($columns = array('*')) {
        if (!is_null($model = $this->first($columns)))
            return $model;

        throw new \Exception("Model Not found !");
    }

    /*
     * =========================================================================
     * WHERES
     * =========================================================================
     */

    /**
     * Add a basic where clause to the query.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  mixed   $value
     * @param  string  $boolean
     * @return $this
     */
//    public function where($column, $operator = null, $value = null, $boolean = 'and') {
//
//
//        $this->query->where($column, $operator, $value, $boolean);
////        $this->query->addBinding(["{$column}" => $value]);
//
//        return $this;
//    }

    /**
     * Add an "or where" clause to the query.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  mixed   $value
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function orWhere($column, $operator = null, $value = null) {
        return $this->where($column, $operator, $value, 'or');
    }

    public function whereRaw($sql, array $bindings = array(), $boolean = 'and') {

        $this->query->whereRaw($sql, $bindings,$boolean);

        return $this;
    }

    /*
     * Joins
     */


    /*
     * 
     */

    /**
     * 
     * @param array $queryParams
     * @return \TreasureMap\Builder
     */
    public function rawParamQuery($queryParams) {
        $this->query->from($this->model->getInstanceAdapter()->getSource());
        $this->query->addRawQueryParams($queryParams);

        return $this;
    }

    /*
     * 
     */

    public function get($columns = array('*')) {
        $this->query->from($this->model->getInstanceAdapter()->getSource());
        $this->query->select($columns);

        return new Support\Collection($this->query->runQuery());
    }

    public function getAll($columns = array('*')) {
        return $this->get($columns)->all();
    }

    public function checkColumns(array $columns) {
        if ($columns != array('*')) {
            foreach ($columns as $colKey => $colValue) {
                if (!in_array($colValue, $this->model->getInstanceAdapter()->getFillableFields())) {
                    throw new \Exception("Invalid column value !");
                }
            }
        } else {
            return TRUE;
        }
    }

}
