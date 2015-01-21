<?php

namespace TreasureMap\Query\Builders;

use TreasureMap\Support\Helpers\ArrayHelper;

class TreasurePath {

    /** @var \TreasureMap\Connection */
    protected $connection;

    /**
     * The columns that should be returned.
     *
     * @var array
     */
    public $columns;

    /**
     * Indicates if the query returns distinct results.
     *
     * @var bool
     */
    public $distinct = false;

    /**
     * The table which the query is targeting.
     *
     * @var string
     */
    public $from;

    /**
     * The table joins for the query.
     *
     * @var array
     */
    public $joins;

    /**
     * The where constraints for the query.
     *
     * @var array
     */
    public $wheres;

    /**
     * The groupings for the query.
     *
     * @var array
     */
    public $groups;

    /**
     * The having constraints for the query.
     *
     * @var array
     */
    public $havings;

    /**
     * The orderings for the query.
     *
     * @var array
     */
    public $orders;

    /**
     * The maximum number of records to return.
     *
     * @var int
     */
    public $limit;

    /**
     * The current query value bindings.
     *
     * @var array
     */
    protected $bindings = array(
        'select' => [],
        'join' => [],
        'where' => [],
        'having' => [],
        'order' => [],
    );

    public function __construct(\TreasureMap\Connection $connection) {
        $this->connection = $connection;
    }

    /*
     * 
     */

    /**
     * Set the columns to be selected.
     *
     * @param  array  $columns
     * @return $this
     */
    public function select($columns = array('*')) {

        if ($this->columns == array('*')) {
            $this->columns = NULL;
        } else {
            $this->columns = is_array($columns) ? $columns : func_get_args();
        }

        return $this;
    }

    /**
     * Add a new select column to the query.
     *
     * @param  mixed  $column
     * @return $this
     */
    public function addSelect($column) {
        $column = is_array($column) ? $column : func_get_args();

        $this->columns = array_merge((array) $this->columns, $column);

        return $this;
    }

    /**
     * Force the query to only return distinct results.
     *
     * @return $this
     */
    public function distinct() {
        $this->distinct = true;

        return $this;
    }

    /**
     * Set the table which the query is targeting.
     *
     * @param  string  $table
     * @return $this
     */
    public function from($table) {
        $this->from = $table;

        return $this;
    }

    /*
     * =========================================================================
     * WHERE
     * =========================================================================
     */

    /*
     * RAW WHERES --------------------------------------------------------------
     */

    public function whereRaw($sql, array $bindings = array(), $boolean = 'and') {

        $type = "raw";

        $this->wheres[] = compact('type', 'sql', 'boolean');

        $this->addBinding($bindings);

        return $this;
    }

    /**
     * Add a raw or where clause to the query.
     *
     * @param  string  $sql
     * @param  array   $bindings
     * @return TreasurePath|static
     */
    public function orWhereRaw($sql, array $bindings = array()) {
        return $this->whereRaw($sql, $bindings, 'or');
    }

    /*
     * NORMAL WHERES -----------------------------------------------------------
     */

    public function where($column, $operator = null, $value = null, $boolean = 'and') {

        // If the value is "null", we will just assume the developer wants to add a
        // where null clause to the query. So, we will allow a short-cut here to
        // that method for convenience so the developer doesn't have to check.
        if (is_null($value)) {
            return $this->whereNull($column, $boolean, $operator != '=');
        }

        $type = "normal";

        $this->wheres[] = compact("type", "column", "operator", "value", "boolean");

        return $this;
    }

    /**
     * Add an "or where" clause to the query.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  mixed   $value
     * @return TreasurePath|static
     */
    public function orWhere($column, $operator = null, $value = null) {
        return $this->where($column, $operator, $value, 'OR');
    }

    // check IN (containings)

    /**
     * Add a "where in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed   $values
     * @param  string  $boolean
     * @param  bool    $not
     * @return $this
     */
    public function whereIn($column, $values, $boolean = 'AND', $not = false) {
        $type = $not ? 'NOT IN' : 'IN';

        $bindArray = [];

        foreach ($values as $key => $value) {
            $bindArray["{$column}_bind_{$key}"] = $value;
        }

        $whereValue = implode(", :", array_keys($bindArray));

        $this->whereRaw("{$column} {$type} (:{$whereValue})", $bindArray, $boolean);

        return $this;
    }

    /**
     * Add an "or where in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed   $values
     * @return TreasurePath|static
     */
    public function orWhereIn($column, $values) {
        return $this->whereIn($column, $values, 'or');
    }

    /**
     * Add a "where not in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed   $values
     * @param  string  $boolean
     * @return TreasurePath|static
     */
    public function whereNotIn($column, $values, $boolean = 'and') {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Add an "or where not in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed   $values
     * @return TreasurePath|static
     */
    public function orWhereNotIn($column, $values) {
        return $this->whereNotIn($column, $values, 'or');
    }

    // check NULLS     

    /**
     * Add a "where null" clause to the query.
     *
     * @param  string  $column
     * @param  string  $boolean
     * @param  bool    $not
     * @return $this
     */
    public function whereNull($column, $boolean = 'and', $not = false) {
        $type = $not ? 'NOT' : 'IS';

        $this->wheres["normal"][] = [
            "column" => $column,
            "operator" => $type,
            "value" => "NULL",
            "bool" => $boolean,
        ];

        return $this;
    }

    /**
     * Add an "or where null" clause to the query.
     *
     * @param  string  $column
     * @return TreasurePath|static
     */
    public function orWhereNull($column) {
        return $this->whereNull($column, 'or');
    }

    /**
     * Add a "where not null" clause to the query.
     *
     * @param  string  $column
     * @param  string  $boolean
     * @return TreasurePath|static
     */
    public function whereNotNull($column, $boolean = 'and') {
        return $this->whereNull($column, $boolean, true);
    }

    /**
     * Add an "or where not null" clause to the query.
     *
     * @param  string  $column
     * @return TreasurePath|static
     */
    public function orWhereNotNull($column) {
        return $this->whereNotNull($column, 'or');
    }

    /*
     * =========================================================================
     * HAVINGS
     * =========================================================================
     */

    public function havingRaw($havingConditon) {
        $this->havings["raw"][] = [
            "condition" => $havingConditon
        ];

        return $this;
    }

    public function orderRaw($orderConditon) {
        $this->orders["raw"][] = [
            "condition" => $orderConditon
        ];

        return $this;
    }

    /*
     * =========================================================================
     * JOINS
     * =========================================================================
     */

    /**
     * Add a join to the query.
     * 
     * @param string $table
     * @param string $one
     * @param string $operator
     * @param string $two
     * @param string $type
     * @param string|boolean $where
     * @return \TreasureMap\Query\Builders\TreasurePath|static
     */
    public function join($table, $one, $operator = null, $two = null, $type = 'inner', $where = false) {

        $this->query->joinRaw($table, "{$one} {$operator} {$two}", $type, $where);

        return $this;
    }

    /**
     * Add a left join to the query.
     *
     * @param  string  $table
     * @param  string  $first
     * @param  string  $operator
     * @param  string  $second
     * @return \TreasureMap\Query\Builders\TreasurePath|static
     */
    public function leftJoin($table, $first, $operator = null, $second = null) {
        return $this->join($table, $first, $operator, $second, 'left');
    }

    /**
     * Add a left join with Where :  [AND {condition}] to the query
     *
     * @param  string  $table
     * @param  string  $one
     * @param  string  $operator
     * @param  string  $two
     * @param string|boolean $where
     * @return \TreasureMap\Query\Builders\TreasurePath|static
     */
    public function leftJoinWhere($table, $one, $operator, $two, $where) {
        return $this->join($table, $one, $operator, $two, 'left', $where);
    }

    /**
     * Add a right join to the query.
     *
     * @param  string  $table
     * @param  string  $first
     * @param  string  $operator
     * @param  string  $second
     * @return \TreasureMap\Query\Builders\TreasurePath|static
     */
    public function rightJoin($table, $first, $operator = null, $second = null) {
        return $this->join($table, $first, $operator, $second, 'right');
    }

    /**
     * Add a right join with Where :  [AND {condition}] to the query
     *
     * @param  string  $table
     * @param  string  $one
     * @param  string  $operator
     * @param  string  $two
     * @param string|boolean $where
     * @return \TreasureMap\Query\Builders\TreasurePath|static
     */
    public function rightJoinWhere($table, $one, $operator, $two, $where) {
        return $this->join($table, $one, $operator, $two, 'right', $where);
    }

    /**
     * 
     * @param string $table
     * @param string $condition
     * @param string $type
     * @param string|boolean $where
     * @return \TreasureMap\Query\Builders\TreasurePath|static
     */
    public function joinRaw($table, $condition, $type = 'inner', $where = false) {

        $join = [
            "table" => $table,
            "condition" => $condition,
            "type" => $type,
            "where" => $where,
        ];

        $join = "{$type} JOIN {$table} ON {$condition} ";

        if ($where) {
            $join .= "AND {$where} ";
        }

        $this->joins["raw"][] = $join;

        return $this;
    }

    /*
     * =========================================================================
     * GROUPS
     * =========================================================================
     */

    public function groupRaw($groupByString) {
        $this->groups["raw"][] = [
            "group" => $groupByString
        ];
    }

    /*
     * =========================================================================
     * BINDINGS
     * =========================================================================
     */

    /**
     * Add a binding to the query.
     *
     * @param  array   $value
     * @param  string  $type
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function addBinding(array $value, $type = 'where') {
        if (!array_key_exists($type, $this->bindings)) {
            throw new \InvalidArgumentException("Invalid binding type: {$type}.");
        }

        if (is_array($value)) {
            $this->bindings[$type] = array_merge($this->bindings[$type], $value);
        }

        return $this;
    }

    /**
     * Get the current query value bindings in a flattened array.
     *
     * @return array
     */
    public function getBindingsWithKeys() {
        return ArrayHelper::array_flatten_with_keys($this->bindings);
    }

    /*
     * =========================================================================
     * JOINS
     * =========================================================================
     */

    /**
     * Retrieve the "count" result of the query.
     *
     * @param  string  $columns
     * @return int
     */
    public function count($columns = '*') {
        if (!is_array($columns)) {
            $columns = array($columns);
        }

        return (int) $this->aggregate(__FUNCTION__, $columns);
    }

    /**
     * Retrieve the minimum value of a given column.
     *
     * @param  string  $column
     * @return mixed
     */
    public function min($column) {
        return $this->aggregate(__FUNCTION__, array($column));
    }

    /**
     * Retrieve the maximum value of a given column.
     *
     * @param  string  $column
     * @return mixed
     */
    public function max($column) {
        return $this->aggregate(__FUNCTION__, array($column));
    }

    /**
     * Retrieve the sum of the values of a given column.
     *
     * @param  string  $column
     * @return mixed
     */
    public function sum($column) {
        $result = $this->aggregate(__FUNCTION__, array($column));

        return $result ? : 0;
    }

    /**
     * Retrieve the average of the values of a given column.
     *
     * @param  string  $column
     * @return mixed
     */
    public function avg($column) {
        return $this->aggregate(__FUNCTION__, array($column));
    }

    /**
     * Execute an aggregate function on the database.
     *
     * @param  string  $function
     * @param  array   $columns
     * @return mixed
     */
    public function aggregate($function, $columns = array('*')) {

        $this->aggregate[] = compact('function', 'columns');

        return $this;
    }

    public function addRawQueryParams(array $queryParams = []) {
        foreach ($this->getAvaliableQueryParams() as $avalKey => $avalValue) {
            if (isset($queryParams[$avalValue])) {
                switch ($avalValue) {
                    case "select":
                        $this->select($queryParams[$avalValue]);
                        break;
                    case "join":
                        $joinParams = $queryParams[$avalValue];
                        $this->joinRaw($joinParams["table"], $joinParams["copndition"], $joinParams["type"], $joinParams["where"]);
                        break;

                    default:
                        $method = "{$avalValue}Raw";
                        $this->{$method}($queryParams[$avalValue]);
                        break;
                }
            }
        }
    }

    protected function getAvaliableQueryParams() {
        return [
            "select",
            "from",
            "where",
            "join",
            "group",
            "order",
            "having",
            "distinct",
        ];
    }

    public function toSql() {

        /*
         * Select start
         */
        $query = "SELECT ";

        /*
         * Distinct
         */

        if ($this->distinct) {
            $query .= "DISTINCT ";
        }

        /*
         * Columns
         */

        $columnsString = "";
        if ($this->columns) {

            foreach ($this->columns as $columnKey => $columnValue) {
                if (is_array($columnValue)) {
                    $columnsString .= $columnKey . ", ";
                } else {
                    $columnsString .= $columnValue . ", ";
                }
            }
            $columnsString = substr($columnsString, 0, -2);
            $columnsString .= " ";
            $query .= $columnsString;
        } else {
            $query .= "* ";
        }

        /*
         * from -> table
         */

        $query .= "FROM {$this->from} ";

        /*
         * JOIN
         */

        if ($this->joins) {
            foreach ($this->joins as $joinKey => $joinValue) {

                switch ($whereKey) {
                    case "raw":

                        if (count($joinValue)) {
                            $query .= implode(" ", $joinValue) . " ";
                        }

                        break;

                    default:
                        break;
                }
            }
        }

        /*
         * WHERE
         */

        if ($this->wheres) {

            $query .= "WHERE ";

            $whereCount = 0;

            foreach ($this->wheres as $whereKey => $whereValue) {

                switch ($whereKey) {
                    case "raw":

                        $query .= ($whereCount > 0) ? "AND " : "";
                        $whereCount += $rawCount = count($whereValue);

                        if ($rawCount) {
                            foreach ($whereValue as $key => $value) {
                                $query .= $value["condition"] . " ";
                                $query .= ($rawCount > 1 && $rawCount != ($key + 1)) ? "{$value["bool"]} " : "";
                            }
                        }

                        break;
                    case "normal":

                        $query .= ($whereCount > 0) ? "AND " : "";
                        $whereCount += $normalCount = count($whereValue);

                        foreach ($whereValue as $key => $value) {

                            // create query from normal where
                            $query .= "{$value["column"]} {$value["operator"]} :{$value["column"]} ";
                            $query .= ($normalCount > 1 && $normalCount != ($key + 1)) ? "{$value["bool"]} " : "";

                            // add automatic binding
                            $this->addBinding(["{$value["column"]}" => $value["value"]]);
                        }

                        break;

                    default:
                        break;
                }
            }
        }

        /*
         * GROUP
         */

        if ($this->groups) {

            $query .= "GROUP BY ";

            foreach ($this->groups as $groupKey => $groupValue) {
                switch ($groupKey) {
                    case "raw":

                        foreach ($groupValue as $key => $value) {
                            $query .= implode(" ", $value) . " ";
                        }

                        break;

                    default:
                        break;
                }
            }
        }

        /*
         * HAVINGS
         */

        if ($this->havings) {

            $query .= "HAVING ";

            foreach ($this->havings as $havingKey => $havingValue) {

                switch ($havingKey) {
                    case "raw":

                        foreach ($havingValue as $key => $value) {
                            $query .= implode(" ", $value) . " ";
                        }

                        break;

                    default:
                        break;
                }
            }
        }


        /*
         * ORDERS
         */

        if ($this->orders) {

            $query .= "ORDER BY ";

            foreach ($this->orders as $orderKey => $orderValue) {

                switch ($orderKey) {
                    case "raw":

                        foreach ($orderValue as $key => $value) {
                            $query .= implode(" ", $value) . " ";
                        }

                        break;

                    default:
                        break;
                }
            }
        }

        /*
         * LIMIT
         */

        if ($this->limit) {
            if ($this->limit !== NULL) {
                if (is_int($this->limit)) {
                    $query .=" LIMIT {$this->limit}";
                } else {
                    $query .= " LIMIT {$this->limit[0]}" . ", " . $this->limit[1];
                }
            }
        }

        echo "QUERY : " . $query . "<br><br>";

        return $query;
    }

    public function runQuery() {
        return $this->connection->select($this->toSql(), $this->getBindingsWithKeys());
    }

}
