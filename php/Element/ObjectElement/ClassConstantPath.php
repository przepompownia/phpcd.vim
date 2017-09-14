<?php

namespace PHPCD\Element\ObjectElement;

class ClassConstantPath extends ObjectElementPath
{
    /**
     * @var string
     */
    private $constantName;

    public function __construct($className, $constantName)
    {
        $this->className = $className;
        $this->constantName = $constantName;
    }

    public function getConstantName()
    {
        return $this->constantName;
    }
}
