<?php

namespace PHPCD\ConstantInfo;

use PHPCD\Collection;

/**
 * @method ConstantInfo[] getIterator()
 */
class ConstantInfoCollection extends Collection
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
