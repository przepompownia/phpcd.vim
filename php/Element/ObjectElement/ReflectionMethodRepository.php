<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\Filter\MethodFilter;

class ReflectionMethodRepository extends ReflectionObjectElementRepository implements MethodRepository
{
    /**
     * @return MethodCollection
     */
    public function find(MethodFilter $filter)
    {
        $reflectionClass = $this->classInfoFactory->createFromFilter($filter);

        $collection = new MethodCollection();

        foreach ($reflectionClass->getMethods() as $method) {
            $method = new ReflectionMethod($this->docBlock, $method);
            if (true === $this->filter($method, $filter)) {
                $collection->add($method);
            }
        }

        return $collection;
    }

    /**
     * @param string $path
     *
     * @return MethodInfo
     */
    public function getByPath($path)
    {
    }
}
