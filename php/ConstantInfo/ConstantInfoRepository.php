<?php

namespace PHPCD\ConstantInfo;

use PHPCD\Filter\ConstantFilter;

interface ConstantInfoRepository
{
    public function find(ConstantFilter $filter);
}
