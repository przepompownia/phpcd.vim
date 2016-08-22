<?php

namespace PHPCD;

use ArrayObject;
use IteratorAggregate;

class Collection implements IteratorAggregate
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
