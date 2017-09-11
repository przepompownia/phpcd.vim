<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\Element\ObjectElement\ObjectElementPath;
use PHPCD\Filter\ClassConstantFilter;

interface ClassConstantRepository
{
    /**
     * @param ClassConstantFilter $filter
     *
     * @return ClassConstantCollection
     */
    public function find(ClassConstantFilter $filter);

    /**
     * @param ObjectElementPath $elementPath
     *
     * @return ClassConstant
     */
    public function getByPath(ObjectElementPath $elementPath);
}
