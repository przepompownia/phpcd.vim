<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\Filter\ConstantFilter;

interface ConstantInfoRepository
{
    public function find(ConstantFilter $filter);
}
