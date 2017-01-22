<?php

namespace PHPCD\FunctionInfo;

use PHPCD\Collection;

class FunctionCollection extends Collection
{
    /**
     * @var FunctionInfo[]
     */
    protected $collection = [];

    /**
     * @param FunctionInfo $functionInfo
     * @return $this
     */
    public function add(FunctionInfo $functionInfo)
    {
        $this->collection[$functionInfo->getName()] = $functionInfo;

        return $this;
    }
}
