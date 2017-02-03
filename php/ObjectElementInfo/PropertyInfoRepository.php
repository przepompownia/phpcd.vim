<?php

namespace PHPCD\ObjectElementInfo;

use PHPCD\Filter\PropertyFilter;
use PHPCD\ObjectElementInfo\PropertyInfoCollection;

interface PropertyInfoRepository
{
    /**
     * @return PropertyInfoCollection
     */
    public function find(PropertyFilter $filter);

    /**
     * @param PropertyPath $path
     * @return PropertyInfo
     */
    public function getByPath(PropertyPath $path);
}
