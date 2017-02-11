<?php

namespace PHPCD\Element\ObjectElement;

class MethodPath extends ObjectElementPath
{
    /**
     * @var string
     */
    private $methodName;

    public function __construct($className, $methodName)
    {
        $this->className = $className;
        $this->methodName = $methodName;
    }

    public function getMethodName()
    {
        return $this->methodName;
    }
}
