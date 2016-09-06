<?php

namespace PHPCD\ClassInfo;

use PHPCD\Filter\ClassFilter;

class ReflectionClass extends \ReflectionClass implements ClassInfo
{
    public function isAbstractClass()
    {
        return $this->isAbstract() && $this->isInstantiable();
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
}
