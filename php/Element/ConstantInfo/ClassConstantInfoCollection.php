<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\Collection\Collection;

/**
 * @method ClassConstantInfo[] getIterator()
 */
class ClassConstantInfoCollection extends Collection
{
    /**
     * @var ClassConstantInfo[]
     */
    protected $collection = [];

    /**
     * @param ClassConstantInfo $constantInfo
     *
     * @return $this
     */
    public function add(ClassConstantInfo $constantInfo)
    {
        $this->collection[$constantInfo->getName()] = $constantInfo;

        return $this;
    }
}
