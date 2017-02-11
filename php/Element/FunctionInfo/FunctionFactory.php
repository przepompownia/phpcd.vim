<?php

namespace PHPCD\Element\FunctionInfo;

class FunctionFactory
{
    /**
     * @return ReflectionFunction
     */
    public function createFunction($functionName)
    {
        return new ReflectionFunction(new \ReflectionFunction($functionName));
    }

    /**
     * @return FunctionCollection
     */
    public function createFunctionCollection()
    {
        return new FunctionCollection();
    }
}
