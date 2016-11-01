<?php

namespace PHPCD\ObjectElementInfo;

class PropertyPath extends ObjectElementPath
{
    /**
    * @var string
    */
    private $propertyName;

    public function __construct($className, $propertyName)
    {
        $this->className = $className;
        $this->propertyName = $propertyName;
    }

    public function getPropertyName()
    {
        return $this->propertyName;
    }
}
