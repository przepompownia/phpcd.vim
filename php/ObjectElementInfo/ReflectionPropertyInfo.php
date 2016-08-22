<?php

namespace PHPCD\ObjectElementInfo;

use PHPCD\ObjectElementInfo\PropertyInfo;

class ReflectionPropertyInfo extends ReflectionObjectElementInfo implements PropertyInfo
{
    public function __construct(\ReflectionProperty $property)
    {
        $this->objectElement = $property;
    }
}
