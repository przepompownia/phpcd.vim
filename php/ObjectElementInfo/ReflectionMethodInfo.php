<?php

namespace PHPCD\ObjectElementInfo;

use PHPCD\ObjectElementInfo\MethodInfo;

class ReflectionMethodInfo extends ReflectionObjectElementInfo implements MethodInfo
{
    public function __construct(\ReflectionMethod $method)
    {
        $this->objectElement = $method;
    }
}
