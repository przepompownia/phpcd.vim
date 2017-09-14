<?php

namespace PHPCD\Element\ObjectElement\Constant;

use PHPCD\Element\ObjectElement\ReflectionObjectElement;
use PHPCD\View\ClassConstantVisitor;
use PHPCD\DocBlock\DocBlock;

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

    public function accept(ClassConstantVisitor $visitor)
    {
        $visitor->visitElement($this);
    }
}
