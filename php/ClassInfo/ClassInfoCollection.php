<?php

namespace PHPCD\ClassInfo;

use PHPCD\Collection;

class ClassInfoCollection extends Collection
{
    /**
     * @var ClassInfo[]
     */
    protected $collection = [];

    /**
     * @param ClassInfo $class_info
     * @return $this
     */
    public function add(ClassInfo $class_info)
    {
        $this->collection[$class_info->getName()] = $class_info;

        return $this;
    }
}
