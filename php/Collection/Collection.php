<?php

namespace PHPCD\Collection;

use ArrayObject;
use IteratorAggregate;
use PHPCD\Element\CollectionVisitor;

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

    public function accept(CollectionVisitor $visitor)
    {
        foreach ($this->collection as $item) {
            $item->accept($visitor);
        }
    }
}
