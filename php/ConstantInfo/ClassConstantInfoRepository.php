<?php

namespace PHPCD\ConstantInfo;

use PHPCD\Filter\ClassConstantFilter;

interface ClassConstantInfoRepository
{
    public function find(ClassConstantFilter $filter);
}
