<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\Filter\ConstantFilter;

interface ConstantRepository
{
    public function find(ConstantFilter $filter);
}
