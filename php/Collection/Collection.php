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
        foreach ($this->collection as $item) {
            yield $item;
        }
    }

    public function isEmpty()
    {
        return empty($this->collection);
    }

    public function accept(CollectionVisitor $visitor)
    {
        foreach ($this as $item) {
            $item->accept($visitor);
        }
    }
}
