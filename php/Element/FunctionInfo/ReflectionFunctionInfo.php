<?php

namespace PHPCD\Element\FunctionInfo;

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

    public function getReturnTypes()
    {
        $type = $this->getPHPReturnType();
        if (null !== $type) {
            return [$type];
        }
    }

    private function getPHPReturnType()
    {
        if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
            $type = (string) $this->reflectionFunction->getReturnType();

            if ('' !== $type) {
                return $type;
            }
        }
    }

    public function getStartLine()
    {
        return $this->reflectionFunction->getStartLine();
    }
}
