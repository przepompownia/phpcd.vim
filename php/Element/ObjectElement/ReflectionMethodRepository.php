<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\Filter\MethodFilter;
use PHPCD\NotFoundException;

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

    public function getByPath(MethodPath $path)
    {
        try {
            $reflectionMethod = new \ReflectionMethod($path->getClassName(), $path->getMethodName());
        } catch (\ReflectionException $e) {
            throw new NotFoundException($e->getMessage(), $e->getCode(), $e);
        }

        return new ReflectionMethod($this->docBlock, $reflectionMethod);
    }
}
