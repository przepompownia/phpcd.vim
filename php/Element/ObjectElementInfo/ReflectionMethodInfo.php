<?php

namespace PHPCD\Element\ObjectElementInfo;

use PHPCD\View\MethodVisitor;

class ReflectionMethodInfo extends ReflectionObjectElementInfo implements MethodInfo
{
    public function __construct(\ReflectionMethod $method)
    {
        $this->objectElement = $method;
    }

    public function getParameters()
    {
        return $this->objectElement->getParameters();
    }

    public function accept(MethodVisitor $visitor)
    {
        $visitor->visitElement($this);
    }
}
