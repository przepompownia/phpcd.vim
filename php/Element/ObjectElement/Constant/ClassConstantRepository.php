<?php

namespace PHPCD\Element\ObjectElement\Constant;

use PHPCD\Element\ObjectElement\ClassConstantPath;
use PHPCD\Filter\ClassConstantFilter;

interface ClassConstantRepository
{
    public function find(ClassConstantFilter $filter): ClassConstantCollection;

    public function getByPath(ClassConstantPath $elementPath): ClassConstant;
}
