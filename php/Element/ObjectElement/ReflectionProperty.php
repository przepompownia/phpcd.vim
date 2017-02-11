<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\View\PropertyVisitor;
use PHPCD\DocBlock\DocBlock;

class ReflectionProperty extends ReflectionObjectElement implements PropertyInfo
{
    /**
     * @var DocBlock
     */
    protected $docBlock;

    public function __construct(\ReflectionProperty $property, DocBlock $docBlock)
    {
        $this->objectElement = $property;
        $this->docBlock = $docBlock;
    }

    /**
     * @return array
     */
    public function getAllowedTypes()
    {
        $docBlock = $this->getDocComment();

        return $this->docBlock->getTypesFromDocBlock($docBlock);
    }

    /**
     * @return array
     */
    public function getAllowedNonTrivialTypes()
    {
        return [];
    }

    public function accept(PropertyVisitor $visitor)
    {
        $visitor->visitElement($this);
    }
}
