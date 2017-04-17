<?php

namespace PHPCD\Element\FunctionInfo;

use PHPCD\DocBlock\DocBlock;

class FunctionFactory
{
    /**
     * @var DocBlock
     */
    protected $docBlock;

    public function __construct(DocBlock $docBlock)
    {
        $this->docBlock = $docBlock;
    }
    /**
     * @return ReflectionFunction
     */
    public function createFunction($functionName)
    {
        return new ReflectionFunction($this->docBlock, new \ReflectionFunction($functionName));
    }

    /**
     * @return FunctionCollection
     */
    public function createFunctionCollection()
    {
        return new FunctionCollection();
    }
}
