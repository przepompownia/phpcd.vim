<?php

namespace PHPCD\FunctionInfo;

class ReflectionFunctionInfo implements FunctionInfo
{
    /**
     * @var \ReflectionFunction
     */
    private $reflectionFunction;

    public function __construct(\ReflectionFunction $reflectionFunction)
    {
        $this->reflectionFunction = $reflectionFunction;
    }

    public function getName()
    {
        return $this->reflectionFunction->getName();
    }

    public function getDocComment()
    {
        return $this->reflectionFunction->getDocComment();
    }

    public function getParameters()
    {
        return $this->reflectionFunction->getParameters();
    }

    public function getFileName()
    {
        return $this->reflectionFunction->getFileName();
    }
}
