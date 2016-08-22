<?php

namespace PHPCD\ObjectElementInfo;

abstract class ReflectionObjectElementInfo implements ObjectElementInfo
{
    /**
     * @var \ReflectionMethod|\ReflectionProperty
     */
    protected $objectElement;

    public function getName()
    {
        return $this->objectElement->getName();
    }

    public function isPublic()
    {
        return $this->objectElement->isPublic();
    }

    public function isProtected()
    {
        return $this->objectElement->isProtected();
    }

    public function isStatic()
    {
        return $this->objectElement->isStatic();
    }

    public function getClass()
    {
        return $this->objectElement->getDeclaringClass()->getName();
    }

    public function getDocComment()
    {
        return $this->objectElement->getDocComment();
    }
}
