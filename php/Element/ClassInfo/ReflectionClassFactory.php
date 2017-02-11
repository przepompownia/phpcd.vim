<?php

namespace PHPCD\Element\ClassInfo;

use PHPCD\Filter\ClassElementFilter;

class ReflectionClassFactory implements ClassFactory
{
    /**
     * @param string|object $class
     *
     * @return ReflectionClass
     */
    public function createClassInfo($class)
    {
        return new namespace\ReflectionClass(new \ReflectionClass($class));
    }

    public function createCollection()
    {
        return new ClassCollection();
    }

    /**
     * @param ClassElementFilter $filter
     *
     * @return ReflectionClass
     */
    public function createFromFilter(ClassElementFilter $filter)
    {
        if (empty($filter->getClassName())) {
            throw new \InvalidArgumentException(sprintf('%s needs class name to find method.', self::class));
        }

        return $this->createClassInfo($filter->getClassName());
    }
}
