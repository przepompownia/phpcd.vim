<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\DocBlock\DocBlock;
use PHPCD\Element\PhysicalLocation;
use PHPCD\View\ObjectElementVisitor;

class ReflectionMethod extends ReflectionObjectElement implements MethodInfo
{
    public function __construct(DocBlock $docBlock, \ReflectionMethod $method)
    {
        parent::__construct($docBlock);
        $this->objectElement = $method;
    }

    public function getParameters()
    {
        return $this->objectElement->getParameters();
    }

    protected function getDocBlockTagName()
    {
        return DocBlock::TAG_RETURN;
    }

    public function acceptObjectElement(ObjectElementVisitor $visitor): void
    {
        $visitor->visitMethod($this);
    }

    public function getNonTrivialTypes(): array
    {
        if ($this->objectElement->hasReturnType()) {
            $typeString = '\\'.(string) $this->objectElement->getReturnType();

            return [$typeString];
        }

        return $this->getNonTrivialTypesFromDocblock();
    }

    public function getPhysicalLocation(): PhysicalLocation
    {
        return new PhysicalLocation($this->objectElement->getFileName(), $this->objectElement->getStartLine());
    }
}
