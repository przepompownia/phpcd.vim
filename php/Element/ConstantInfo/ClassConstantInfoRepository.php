<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\Filter\ClassConstantFilter;

interface ClassConstantInfoRepository
{
    /**
     * @param ClassConstantFilter $filter
     * @return ClassConstantInfoCollection
     */
    public function find(ClassConstantFilter $filter);
}
