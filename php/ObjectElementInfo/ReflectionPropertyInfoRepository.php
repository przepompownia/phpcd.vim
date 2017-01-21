<?php

namespace PHPCD\ObjectElementInfo;

use PHPCD\NotFoundException;
use PHPCD\Filter\PropertyFilter;
use PHPCD\ObjectElementInfo\PropertyInfoCollection;
use PHPCD\ObjectElementInfo\GenericPropertyInfo;

class ReflectionPropertyInfoRepository extends ReflectionObjectElementInfoRepository implements PropertyInfoRepository
{
    const VIRTUAL_PROPERTY_READ_REGEX = '/@property(|-write|-read)\s+(?<paths>\S+)\s+\$?(?<names>\S+)/m';

    /**
     * @return PropertyInfoCollection
     */
    public function find(PropertyFilter $filter)
    {
        $reflectionClass = $this->classInfoFactory->createReflectionClassFromFilter($filter);

        $collection = new PropertyInfoCollection();

        foreach ($reflectionClass->getProperties() as $property) {
            $property = new ReflectionPropertyInfo($property, $this->docBlock);
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

        if (! preg_match_all(self::VIRTUAL_PROPERTY_READ_REGEX, $reflection->getDocComment(), $matches)) {
            return [];
        }

        foreach ($matches['names'] as $idx => $propertyName) {
            $classInfo = $this->classInfoFactory->createClassInfo($matches['paths'][$idx]);
            $properties[] = new GenericPropertyInfo($propertyName, $classInfo, 'public');
        }

        return $properties;
    }

    /**
     * @var PropertyPath $path
     * @return PropertyInfo
     */
    public function getByPath(PropertyPath $path)
    {
        try {
            $property = new \ReflectionProperty($path->getClassName(), $path->getPropertyName());
        } catch (\ReflectionException $e) {
            throw new NotFoundException($e->getMessage(), $e->getCode(), $e);
        }

        return new ReflectionPropertyInfo($property, $this->docBlock);
    }
}
