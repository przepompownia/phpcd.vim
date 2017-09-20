<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\DocBlock\DocBlock;
use PHPCD\Element\PhysicalLocation;
use PHPCD\View\ObjectElementVisitor;

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

    public function acceptObjectElement(ObjectElementVisitor $visitor): void
    {
        $visitor->visitProperty($this);
    }

    /**
     * @return array
     */
    public function getNonTrivialTypes()
    {
        return $this->getNonTrivialTypesFromDocblock();
    }

    public function getPhysicalLocation(): PhysicalLocation
    {
        $class = $this->objectElement->getDeclaringClass();
        $filename = $class->getFileName();

        $fileObject = new \SplFileObject($filename);
        $fileObject->seek($class->getStartLine());

        $pattern = '/(private|protected|public|var)\s\$'.$this->getName().'/x';
        foreach ($fileObject as $line => $content) {
            if (preg_match($pattern, $content)) {
                return new PhysicalLocation($filename, $line + 1);
            }
        }

        return new PhysicalLocation($filename, $class->getStartLine());
    }
}
