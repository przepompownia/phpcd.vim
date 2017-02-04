<?php

namespace PHPCD\ObjectElementInfo;

use PHPCD\Collection\Collection;

/**
 * @method MethodInfo[] getIterator()
 */
class MethodInfoCollection extends Collection
{
    /**
     * @var MethodInfo[]
     */
    protected $collection = [];

    /**
     * @param MethodInfo $methodInfo
     *
     * @return $this
     */
    public function add(MethodInfo $methodInfo)
    {
        $this->collection[$methodInfo->getName()] = $methodInfo;

        return $this;
    }
}
