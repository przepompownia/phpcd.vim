<?php

namespace PHPCD\Element\ClassInfo\ClassLoader;

class NullClassLoader implements ClassLoader
{
    /**
     * @return array
     */
    public function getClassMap()
    {
        return [];
    }

    /**
     * @return string|bool
     */
    public function findFile($classpath)
    {
        return false;
    }
}
