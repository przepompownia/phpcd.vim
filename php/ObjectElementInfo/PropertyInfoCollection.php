<?php

namespace PHPCD\ObjectElementInfo;

use PHPCD\Collection;

/**
 * @method PropertyInfo[] getIterator()
 */
class PropertyInfoCollection extends Collection
{
    /**
     * @var PropertyInfo[]
     */
    protected $collection = [];

    /**
     * @param PropertyInfo $propertyInfo
     *
     * @return $this
     */
    public function add(PropertyInfo $propertyInfo)
    {
        $this->collection[$propertyInfo->getName()] = $propertyInfo;

        return $this;
    }
}
