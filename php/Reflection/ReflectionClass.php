<?php

namespace PHPCD\Reflection;

use PHPCD\ClassInfo;
use PHPCD\ClassFilter;

class ReflectionClass extends \ReflectionClass implements ClassInfo
{
    /**
     * Get methods available for given class
     * depending on context
     *
     * @param bool|null $static Show static|non static|both types
     * @param bool public_only restrict the result to public methods
     * @return ReflectionMethod[]
     */
    public function getAvailableMethods($static, $public_only = false)
    {
        $methods = $this->getMethods();

        foreach ($methods as $key => $method) {
            if (false === $this->filterMethod($method, $static, $public_only)) {
                unset($methods[$key]);
            }
        }

        return $methods;
    }

    /**
     * Get properties available for given class
     * depending on context
     *
     * @param bool|null $static Show static|non static|both types
     * @param bool public_only restrict the result to public properties
     * @return ReflectionProperty[]
     */
    public function getAvailableProperties($static, $public_only = false)
    {
        $properties = $this->getProperties();

        foreach ($properties as $key => $property) {
            if (false === $this->filterMethod($property, $static, $public_only)) {
                unset($properties[$key]);
            }
        }

        return $properties;
    }

    /**
     * @param \ReflectionMethod|\ReflectionProperty $element
     * @return bool
     */
    private function filterMethod($element, $static, $public_only)
    {
        if (!$element instanceof \ReflectionMethod && !$element instanceof \ReflectionProperty) {
            throw new \InvalidArgumentException(
                'Parameter must be a member of ReflectionMethod or ReflectionProperty class'
            );
        }

        if ($static !== null && ($element->isStatic() xor $static)) {
            return false;
        }

        if ($element->isPublic()) {
            return true;
        }

        if ($public_only) {
            return false;
        }

        if ($element->isProtected()) {
            return true;
        }

        // $element is then private
        return $element->getDeclaringClass()->getName() === $this->getName();
    }

    public function isAbstractClass()
    {
        return $this->isAbstract() && $this->isInstantiable();
    }

    public function matchesFilter(\PHPCD\ClassFilter $classFilter)
    {
        $methods = $classFilter->getFieldNames();

        foreach ($methods as $method) {
            if ($classFilter->$method() !== null) {
                if ($classFilter->$method() !== $this->$method()) {
                    return false;
                }
            }
        }

        return true;
    }
}
