<?php

namespace PHPCD\Element\ObjectElementInfo;

use PHPCD\Filter\MethodFilter;

class ReflectionMethodInfoRepository extends ReflectionObjectElementInfoRepository implements MethodInfoRepository
{
    /**
     * @return MethodInfoCollection
     */
    public function find(MethodFilter $filter)
    {
        $reflectionClass = $this->classInfoFactory->createReflectionClassFromFilter($filter);

        $collection = new MethodInfoCollection();

        foreach ($reflectionClass->getMethods() as $method) {
            $method = new ReflectionMethodInfo($method);
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
