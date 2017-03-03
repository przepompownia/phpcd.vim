<?php

namespace PHPCD\DocBlock;

use phpDocumentor\Reflection\Types\Compound;

class CompoundTypeIterator implements \Iterator
{
    private $index = 0;

    private $type;

    public function __construct(Compound $compoundType)
    {
        $this->type = $compoundType;
    }

    public function current()
    {
        return $this->type->get($this->index);
    }

    public function key()
    {
        return $this->index;
    }

    public function next()
    {
        ++$this->index;
    }

    public function rewind()
    {
        $this->index = 0;
    }

    public function valid()
    {
        return $this->type->has($this->index);
    }
}
