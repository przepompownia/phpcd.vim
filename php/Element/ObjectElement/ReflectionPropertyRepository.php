<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\Element\ClassInfo\ReflectionClass;
use PHPCD\NotFoundException;
use PHPCD\Filter\PropertyFilter;

class ReflectionPropertyRepository extends ReflectionObjectElementRepository implements PropertyRepository
{
    const VIRTUAL_PROPERTY_READ_REGEX = '/@property(|-write|-read)\s+(?<paths>\S+)\s+\$?(?<names>\S+)/m';

    /**
     * @return PropertyCollection
     */
    public function find(PropertyFilter $filter)
    {
        $reflectionClass = $this->classInfoFactory->createFromFilter($filter);

        $collection = new PropertyCollection();

        foreach ($reflectionClass->getProperties() as $property) {
            $property = new ReflectionProperty($this->docBlock, $property);
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

    private function getVirtualProperties(ReflectionClass $reflection)
    {
        $properties = [];

        if (!preg_match_all(self::VIRTUAL_PROPERTY_READ_REGEX, $reflection->getDocComment(), $matches)) {
            return [];
        }

        foreach ($matches['names'] as $idx => $propertyName) {
            $classInfo = $this->classInfoFactory->createClassInfo($matches['paths'][$idx]);
            $properties[] = new GenericProperty($propertyName, $classInfo, 'public');
        }

        return $properties;
    }

    public function getByPath(ObjectElementPath $path): PropertyInfo
    {
        try {
            $property = new \ReflectionProperty($path->getClassName(), $path->getElementName());

            return new ReflectionProperty($this->docBlock, $property);
        } catch (\ReflectionException $e) {
            throw new NotFoundException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
