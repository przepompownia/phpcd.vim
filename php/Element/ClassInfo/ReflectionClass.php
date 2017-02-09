<?php

namespace PHPCD\Element\ClassInfo;

use PHPCD\Element\AbstractElement;
use PHPCD\Filter\ClassFilter;

class ReflectionClass extends AbstractElement implements ClassInfo
{
    /**
     * @var \ReflectionClass
     */
    private $reflectionClass;

    public function __construct(\ReflectionClass $reflectionClass)
    {
        $this->reflectionClass = $reflectionClass;
    }

    public function isAbstractClass()
    {
        return $this->reflectionClass->isAbstract() && $this->isInstantiable();
    }

    public function matchesFilter(ClassFilter $classFilter)
    {
        $methods = $classFilter->getCriteriaNames();

        foreach ($methods as $method) {
            if ($classFilter->$method() !== null) {
                if ($classFilter->$method() !== $this->$method()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isFinal()
    {
        return $this->reflectionClass->isFinal();
    }

    /**
     * @return bool
     */
    public function isTrait()
    {
        return $this->reflectionClass->isTrait();
    }

    /**
     * @return bool
     */
    public function isInstantiable()
    {
        return $this->reflectionClass->isInstantiable();
    }

    /**
     * @return bool
     */
    public function isInterface()
    {
        return $this->reflectionClass->isInterface();
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        return $this->reflectionClass->getShortName();
    }

    /**
     * @return string
     */
    public function getDocComment()
    {
        return $this->reflectionClass->getDocComment();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->reflectionClass->getName();
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->reflectionClass->getFileName();
    }

    /**
     * @return int
     */
    public function getStartLine()
    {
        return $this->reflectionClass->getStartLine();
    }

    public function getConstants()
    {
        return $this->reflectionClass->getConstants();
    }

    public function getMethods()
    {
        return $this->reflectionClass->getMethods();
    }

    public function getProperties()
    {
        return $this->reflectionClass->getProperties();
    }
}
