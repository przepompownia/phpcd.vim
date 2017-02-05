<?php

namespace PHPCD\Element\ObjectElementInfo;

use PHPCD\DocBlock\DocBlock;

class ReflectionPropertyInfo extends ReflectionObjectElementInfo implements PropertyInfo
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
}
