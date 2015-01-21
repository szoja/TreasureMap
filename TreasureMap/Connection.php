<?php

namespace TreasureMap;

class Connection {

    /**
     * The active PDO connection.
     *
     * @var \PDO
     */
    protected $pdo;
    //
    protected $database;
    protected $tablePrefix;
    protected $config;
    protected $errorLog;

    /**
     * The default fetch mode of the connection.
     *
     * @var int
     */
    protected $fetchMode = \PDO::FETCH_ASSOC;
    protected $fetchOptions = [];

    /**
     * Ez a változó tartja számon az aktuális futó tranzakciók számát.
     * Egymásba ágyazott tranzakcióknál szükséges.
     * 
     * 
     * @var integer
     */
    protected $transactionCounter = 0;

    /**
     * Ez a változó mutatja meg ha a beágyazott tranzakcióban volt rollback, a végén ne legyen commit.
     * 
     * @var boolean
     */
    protected $nestedRollback = FALSE;

    /**
     * Create a new database connection instance.
     *
     * @param  \PDO     $pdo
     * @param  string   $database
     * @param  string   $tablePrefix
     * @param  array    $config
     * @return void
     */
    public function __construct(\PDO $pdo, $database = '', $tablePrefix = '', array $config = array()) {
        $this->pdo = $pdo;

        // First we will setup the default properties. We keep track of the DB
        // name we are connected to since it is needed when some reflective
        // type commands are run such as checking whether a table exists.
        $this->database = $database;

        $this->tablePrefix = $tablePrefix;

        $this->config = $config;
    }

    function getFetchMode() {
        return $this->fetchMode;
    }

    function setFetchMode($fetchMode, array $options = NULL) {
        $this->fetchMode = $fetchMode;
        if ($options) {
            $this->fetchOptions = $options;
        }
    }

    /**
     * Get the current PDO connection.
     *
     * @return \PDO
     */
    public function getPdo() {
        return $this->pdo;
    }

    /**
     * Run a select statement and return a single result.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return mixed
     */
    public function selectOne($query, $bindings = array()) {
        $records = $this->select($query, $bindings);

        return count($records) > 0 ? reset($records) : null;
    }

    /**
     * Run a select statement against the database.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return array
     */
    public function select($query, $bindings = array()) {

        $sth = $this->getPdo()->prepare($query);

        if (!($bindings === NULL || count($bindings) == 0)) {
            foreach ($bindings as $key => $value) {
                $sth->bindValue("{$key}", $value);
            }
        }

        $sth->execute();

        switch ($this->getFetchMode()) {
            case \PDO::FETCH_CLASS:
                return $sth->fetchAll($this->getFetchMode(), $this->fetchOptions["class"]);
                break;
            case \PDO::FETCH_INTO:
                return $sth->fetchAll($this->getFetchMode(), $this->fetchOptions["instance"]);
                break;
            default:
                return $sth->fetchAll($this->getFetchMode());
                break;
        }
    }

    /**
     * Insert
     *      
     * @param string $table             A name of table to insert into
     * @param array $data               An associative array
     */
    public function insert($table, array $data) {
        // ha volt belső rollback, nem futtatja le a lekérdezést
        if ($this->nestedRollback) {
            return $this;
        }

        ksort($data);

        $fieldNames = implode('`, `', array_keys($data));
        $fieldValues = ':' . implode(', :', array_keys($data));

        $sth = $this->getPdo()->prepare("INSERT INTO $table (`$fieldNames`) VALUES ($fieldValues)");

        foreach ($data as $key => $value) {
            $sth->bindValue(":$key", $value);
        }

        $sth->execute();

        return $this;
    }

    /**
     * Update
     * 
     * @param string $table         A name of table to insert into
     * @param array $data           An associative array
     * @param string $where the     WHERE query part
     */
    public function update($table, array $data, $where) {

        if ($this->nestedRollback) {
            return $this;
        }

        ksort($data);

        foreach ($data as $key => $value) {
            $fieldDetails .= "`$key`=:$key,";
        }
        $fieldDetails = rtrim($fieldDetails, ',');

        $sth = $this->getPdo()->prepare("UPDATE $table SET $fieldDetails WHERE $where");

        foreach ($data as $key => $value) {
            $sth->bindValue(":$key", $value);
        }

        $sth->execute();
        return $sth->rowCount();
    }

    /**
     * delete
     * 
     * @param string $table
     * @param string $where
     * @param integer $limit
     * @return integer Affected rows
     */
    public function delete($table, $where) {
        if ($this->nestedRollback) {
            return 0;
        }

        $sth = $this->getPdo()->prepare("DELETE FROM {$table} WHERE {$where}")->execute();
        return $sth->rowCount();
    }

    /*
     * -------------------------------------------------------------------------
     * TRANSACTION COUNTER
     * -------------------------------------------------------------------------
     */

    function beginTransaction() {
        if ($this->transactionCounter === 0) {
            $this->transactionCounter++;
            return $this->getPdo()->beginTransaction();
        } else {
            $this->transactionCounter++;
            return ($this->transactionCounter >= 0);
        }
    }

    function commit() {
        $this->transactionCounter--;
        // ha elértük a legkülső commitot
        if ($this->transactionCounter === 0) {
            // megnézzük a belső tranzakcióknál volt e rollback
            if ($this->nestedRollback) {
                // ha volt rollbackeljük az egészet
                $this->nestedRollback = FALSE;
                return $this->getPdo()->rollBack();
            } else {
                // ha nem volt akkor commitoljuk az egészet
                return $this->getPdo()->commit();
            }
        } else {
            // ha belső tranzakcióban vagyunk true értéked adunk vissza
            return ($this->transactionCounter >= 0);
        }
    }

    function rollback() {
        $this->transactionCounter--;

        if ($this->transactionCounter > 0) {
            // ha belső tranzakcióban vagyunk
            $this->nestedRollback = TRUE;
            return FALSE;
        } else {
            // ha a végső külső tranzakcióhoz értünk
            // resetelem a tranzakciószámot
            $this->transactionCounter = 0;
            // resetelem hogy volt e belső rollback
            $this->nestedRollback = FALSE;
            // végrehajtom a rollbacket
            return $this->getPdo()->rollback();
        }
    }

}
