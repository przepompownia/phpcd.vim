<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\View\MethodVisitor;
use PHPCD\DocBlock\DocBlock;

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

    public function accept(MethodVisitor $visitor)
    {
        $visitor->visitElement($this);
    }

    /**
     * @return array
     */
    public function getNonTrivialTypes()
    {
        if (version_compare(PHP_VERSION, '7.0.0') >= 0 && $this->objectElement->hasReturnType()) {
            return [(string) $this->objectElement->getReturnType()];
        }

        return $this->getNonTrivialTypesFromDocblock();
    }
}
