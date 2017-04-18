<?php

namespace PHPCD\Element\ClassInfo\ClassLoader;

interface ClassLoader
{
    /**
     * @return array
     */
    public function getClassMap();

    /**
     * @return string|bool
     */
    public function findFile($classpath);
}
