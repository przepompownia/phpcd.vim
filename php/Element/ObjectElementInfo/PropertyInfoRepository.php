<?php

namespace PHPCD\Element\ObjectElementInfo;

use PHPCD\Filter\PropertyFilter;

interface PropertyInfoRepository
{
    /**
     * @return PropertyInfoCollection
     */
    public function find(PropertyFilter $filter);

    /**
     * @param PropertyPath $path
     *
     * @return PropertyInfo
     */
    public function getByPath(PropertyPath $path);
}
