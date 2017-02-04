<?php

namespace PHPCD\ClassInfo;

use PHPCD\Collection;

/**
 * @method ClassInfo[] getIterator()
 */
class ClassInfoCollection extends Collection
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
