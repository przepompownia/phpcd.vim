<?php

namespace PHPCD\ClassInfo;

use PHPCD\Filter\ClassElementFilter;

class ClassInfoFactory
{
    /**
     *
     * @param string|object $class
     * @return ClassInfo
     */
    public function createClassInfo($class)
    {
        return new namespace\ReflectionClass($class);
    }

    public function createClassInfoCollection()
    {
        return new ClassInfoCollection;
    }

    public function createReflectionClassFromFilter(ClassElementFilter $filter)
    {
        if (empty($filter->getClassName())) {
            throw new \InvalidArgumentException(sprintf('%s needs class name to find method.', self::class));
        }

        return new \ReflectionClass($filter->getClassName());
    }
}
