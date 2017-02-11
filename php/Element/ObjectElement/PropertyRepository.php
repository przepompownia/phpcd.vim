<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\Filter\PropertyFilter;

interface PropertyRepository
{
    /**
     * @return PropertyCollection
     */
    public function find(PropertyFilter $filter);

    /**
     * @param PropertyPath $path
     *
     * @return PropertyInfo
     */
    public function getByPath(PropertyPath $path);
}
