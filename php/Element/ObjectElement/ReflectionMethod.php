<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\View\MethodVisitor;

class ReflectionMethod extends ReflectionObjectElement implements MethodInfo
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
