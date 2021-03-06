<?php

namespace PHPCD\Element\FunctionInfo;

use PHPCD\Collection\Collection;

/**
 * @method FunctionInfo[] getIterator()
 */
class FunctionCollection extends Collection
{
    /**
     * @var FunctionInfo[]
     */
    protected $collection = [];

    /**
     * @param FunctionInfo $functionInfo
     *
     * @return $this
     */
    public function add(FunctionInfo $functionInfo)
    {
        $this->collection[$functionInfo->getName()] = $functionInfo;

        return $this;
    }
}
