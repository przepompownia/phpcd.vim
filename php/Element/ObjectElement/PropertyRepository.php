<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\Filter\PropertyFilter;

interface PropertyRepository
{
    /**
     * @return PropertyCollection
     */
    public function find(PropertyFilter $filter);

    public function getByPath(ObjectElementPath $path): PropertyInfo;
}
