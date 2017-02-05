<?php

namespace PHPCD\Element\ObjectElementInfo;

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
}
