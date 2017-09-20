<?php

namespace PHPCD\Element\ObjectElement\Constant;

use PHPCD\DocBlock\DocBlock;
use PHPCD\Element\ObjectElement\ReflectionObjectElement;
use PHPCD\Element\PhysicalLocation;
use PHPCD\View\ObjectElementVisitor;

class ReflectionClassConstant extends ReflectionObjectElement implements ClassConstant
{
    /**
     * @var \ReflectionClassConstant
     */
    protected $objectElement;

    public function __construct(DocBlock $docBlock, \ReflectionClassConstant $constant)
    {
        parent::__construct($docBlock);
        $this->objectElement = $constant;
    }

    public function getValue()
    {
        return $this->objectElement->getValue();
    }

    public function getNonTrivialTypes(): array
    {
        return $this->getNonTrivialTypesFromDocblock();
    }

    protected function getDocBlockTagName()
    {
        return 'x-x';
    }

    public function acceptObjectElement(ObjectElementVisitor $visitor): void
    {
        $visitor->visitConstant($this);
    }

    public function getPhysicalLocation(): PhysicalLocation
    {
        return new PhysicalLocation($this->objectElement->getDeclaringClass()->getFileName(), 1);
    }
}
