<?php

namespace PHPCD\Element\ClassInfo;

use PHPCD\Collection\Collection;

/**
 * @method ClassInfo[] getIterator()
 */
class ClassCollection extends Collection
{
    /**
     * @var ClassInfo[]
     */
    protected $collection = [];

    /**
     * @param ClassInfo $classInfo
     *
     * @return $this
     */
    public function add(ClassInfo $classInfo)
    {
        $this->collection[$classInfo->getName()] = $classInfo;

        return $this;
    }
}
