<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\Collection\Collection;

/**
 * @method PropertyInfo[] getIterator()
 */
class PropertyCollection extends Collection
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
