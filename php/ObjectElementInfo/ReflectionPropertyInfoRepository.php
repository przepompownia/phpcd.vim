<?php

namespace PHPCD\ObjectElementInfo;

use PHPCD\Filter\PropertyFilter;
use PHPCD\ObjectElementInfo\PropertyInfoCollection;
use PHPCD\ObjectElementInfo\GenericPropertyInfo;

class ReflectionPropertyInfoRepository extends ReflectionObjectElementInfoRepository implements PropertyInfoRepository
{
    const VIRTUAL_PROPRTY_READ_REGEX = '/@property(|-write|-read)\s+(?<paths>\S+)\s+\$?(?<names>\S+)/m';

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

        foreach ($this->getVirtualProperties($reflectionClass) as $property) {
            if (true === $this->filter($property, $filter)) {
                $collection->add($property);
            }
        }

        return $collection;
    }

    private function getVirtualProperties(\ReflectionClass $reflection)
    {
        $properties = [];

        if (! preg_match_all(self::VIRTUAL_PROPRTY_READ_REGEX, $reflection->getDocComment(), $matches)) {
            return [];
        }

        foreach ($matches['names'] as $idx => $propertyName) {
            $properties[] = new GenericPropertyInfo($propertyName, $matches['paths'][$idx], 'public');
        }

        return $properties;
    }

    /**
     * @var string $path
     * @return PropertyInfo
     */
    public function getByPath($path)
    {
    }
}
