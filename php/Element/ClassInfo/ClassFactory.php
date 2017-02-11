<?php

namespace PHPCD\Element\ClassInfo;

use PHPCD\Filter\ClassElementFilter;

interface ClassFactory
{
    /**
     * @param string|object $class
     *
     * @return ClassInfo
     */
    public function createClassInfo($class);

    public function createCollection();

    /**
     * @param ClassElementFilter $filter
     *
     * @return ClassInfo
     */
    public function createFromFilter(ClassElementFilter $filter);
}
