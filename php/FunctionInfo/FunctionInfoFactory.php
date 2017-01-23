<?php

namespace PHPCD\FunctionInfo;

use PHPCD\FunctionInfo\FunctionCollection;

class FunctionInfoFactory
{
    /**
     * @return \ReflectionFunctionInfo
     */
    public function createFunctionInfo($functionName)
    {
        return new \ReflectionFunction($functionName);
    }

    /**
     * @return FunctionCollection
     */
    public function createFunctionCollection()
    {
        return new FunctionCollection();
    }

}
