<?php

namespace PHPCD\Collection;

use ArrayObject;
use IteratorAggregate;

abstract class Collection implements IteratorAggregate
{
    protected $collection = [];

    public function getIterator()
    {
        return (new ArrayObject($this->collection))->getIterator();
    }

    public function isEmpty()
    {
        return empty($this->collection);
    }
}
