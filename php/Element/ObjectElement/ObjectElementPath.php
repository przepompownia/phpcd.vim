<?php

namespace PHPCD\Element\ObjectElement;

class ObjectElementPath
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $elementName;

    public function __construct($className, $elementName)
    {
        $this->className = $className;
        $this->elementName = $elementName;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getElementName()
    {
        return $this->elementName;
    }
}
