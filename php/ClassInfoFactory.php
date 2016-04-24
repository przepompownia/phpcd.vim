<?php

namespace PHPCD;

class ClassInfoFactory
{
    /**
     * @return ClassInfo
     */
    public function createClassInfo($class)
    {
        return new Reflection\ReflectionClass($class);
    }
}
