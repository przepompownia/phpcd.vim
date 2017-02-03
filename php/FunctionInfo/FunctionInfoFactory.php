<?php

namespace PHPCD\FunctionInfo;

class FunctionInfoFactory
{
    /**
     * @return \ReflectionFunctionInfo
     */
    public function createFunctionInfo($functionName)
    {
        return new ReflectionFunctionInfo(new \ReflectionFunction($functionName));
    }

    /**
     * @return FunctionCollection
     */
    public function createFunctionCollection()
    {
        return new FunctionCollection();
    }
}
