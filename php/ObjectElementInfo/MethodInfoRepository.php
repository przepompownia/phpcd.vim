<?php

namespace PHPCD\ObjectElementInfo;

use PHPCD\Filter\MethodFilter;
use PHPCD\ObjectElementInfo\MethodInfoCollection;

interface MethodInfoRepository
{
    /**
     * @return MethodInfoCollection
     */
    public function find(MethodFilter $filter);

    /**
     * @param string $path
     * @return MethodInfo
     */
    public function getByPath($path);
}
