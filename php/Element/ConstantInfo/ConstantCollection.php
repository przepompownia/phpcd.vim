<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\Collection\Collection;

/**
 * @method ConstantInfo[] getIterator()
 */
class ConstantCollection extends Collection
{
    /**
     * @var ConstantInfo[]
     */
    protected $collection = [];

    /**
     * @param ConstantInfo $constantInfo
     *
     * @return $this
     */
    public function add(ConstantInfo $constantInfo)
    {
        $this->collection[$constantInfo->getName()] = $constantInfo;

        return $this;
    }
}
