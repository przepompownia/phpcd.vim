<?php

namespace PHPCD\Element\ObjectElement\Constant;

use PHPCD\Collection\Collection;

/**
 * @method ClassConstant[] getIterator()
 */
class ClassConstantCollection extends Collection
{
    /**
     * @var ClassConstant[]
     */
    protected $collection = [];

    /**
     * @param ClassConstant $constantInfo
     *
     * @return $this
     */
    public function add(ClassConstant $constantInfo)
    {
        $this->collection[$constantInfo->getName()] = $constantInfo;

        return $this;
    }
}
