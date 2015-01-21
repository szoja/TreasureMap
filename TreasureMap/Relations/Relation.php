<?php

namespace TreasureMap\Relations;

abstract class Relation {

    protected $model;
    protected $related;
    protected $connector;

    /** @var \TreasureMap\Support\Collection */
    protected $collection;

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    abstract public function getResults();
}
