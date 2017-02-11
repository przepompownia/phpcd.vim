<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\Filter\ClassConstantFilter;

interface ClassConstantRepository
{
    /**
     * @param ClassConstantFilter $filter
     * @return ClassConstantCollection
     */
    public function find(ClassConstantFilter $filter);
}
