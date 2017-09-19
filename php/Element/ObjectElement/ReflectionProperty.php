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

    public function getDeclarationLineNumber(): int
    {
        $class = $this->objectElement->getDeclaringClass();
        $fileObject = new \SplFileObject($class->getFileName());
        $fileObject->seek($class->getStartLine());

        $pattern = '/(private|protected|public|var)\s\$'.$this->getName().'/x';
        foreach ($fileObject as $line => $content) {
            if (preg_match($pattern, $content)) {
                return $line + 1;
            }
        }

        return $class->getStartLine();
    }
}
