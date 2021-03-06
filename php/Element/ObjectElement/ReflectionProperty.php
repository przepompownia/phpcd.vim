<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\View\PropertyVisitor;
use PHPCD\DocBlock\DocBlock;

class ReflectionProperty extends ReflectionObjectElement implements PropertyInfo
{
    public function __construct(DocBlock $docBlock, \ReflectionProperty $property)
    {
        parent::__construct($docBlock);
        $this->objectElement = $property;
    }

    protected function getDocBlockTagName()
    {
        return DocBlock::TAG_VAR;
    }

    public function accept(PropertyVisitor $visitor)
    {
        $visitor->visitElement($this);
    }

    /**
     * @return array
     */
    public function getNonTrivialTypes()
    {
        return $this->getNonTrivialTypesFromDocblock();
    }
}
