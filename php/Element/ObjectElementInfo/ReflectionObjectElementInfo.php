<?php

namespace PHPCD\Element\ObjectElementInfo;

use PHPCD\Element\ClassInfo\ReflectionClass;

abstract class ReflectionObjectElementInfo implements ObjectElementInfo
{
    /**
     * @var \ReflectionMethod|\ReflectionProperty
     */
    protected $objectElement;

    /**
     * @var ReflectionClass;
     */
    protected $classInfo;

    /**
     * @return ClassInfo
     */
    public function getClass()
    {
        if (null === $this->classInfo) {
            $this->classInfo = new ReflectionClass($this->objectElement->getDeclaringClass());
        }

        return $this->classInfo;
    }

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

    public function getDocComment()
    {
        return $this->objectElement->getDocComment();
    }

    public function getModifiers()
    {
        return \Reflection::getModifierNames($this->objectElement->getModifiers());
    }
}
