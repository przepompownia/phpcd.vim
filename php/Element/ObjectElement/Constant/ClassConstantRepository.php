<?php

namespace PHPCD\Element\ObjectElement\Constant;

use PHPCD\Element\ObjectElement\ObjectElementPath;
use PHPCD\Filter\ClassConstantFilter;

interface ClassConstantRepository
{
    public function find(ClassConstantFilter $filter): ClassConstantCollection;

    public function getByPath(ObjectElementPath $elementPath): ClassConstant;
}
