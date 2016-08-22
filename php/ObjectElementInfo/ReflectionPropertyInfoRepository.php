<?php

namespace PHPCD\ObjectElementInfo;

use PHPCD\Filter\PropertyFilter;
use PHPCD\ObjectElementInfo\PropertyInfoCollection;

class ReflectionPropertyInfoRepository extends ReflectionObjectElementInfoRepository implements PropertyInfoRepository
{
    /**
     * @return PropertyInfoCollection
     */
    public function find(PropertyFilter $filter)
    {
        $reflectionClass = $this->classInfoFactory->createReflectionClassFromFilter($filter);

        $collection = new PropertyInfoCollection();

        foreach ($reflectionClass->getProperties() as $property) {
            $property = new ReflectionPropertyInfo($property);
            if (true === $this->filter($property, $filter)) {
                $collection->add($property);
            }
        }

        return $collection;
    }

    /**
     * @var string $path
     * @return PropertyInfo
     */
    public function getByPath($path)
    {
    }
}
