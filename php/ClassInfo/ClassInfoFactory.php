<?php

namespace PHPCD\ClassInfo;

class ClassInfoFactory
{
    /**
     * @return ClassInfo
     */
    public function createClassInfo($class)
    {
        return new namespace\ReflectionClass($class);
    }
}
