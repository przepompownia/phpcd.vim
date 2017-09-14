<?php

namespace PHPCD\Element\ObjectElement\Constant;

use PHPCD\Element\ClassInfo\ReflectionClassFactory;
use PHPCD\Element\ObjectElement\ReflectionObjectElementRepository;
use PHPCD\Filter\ClassConstantFilter;
use PHPCD\PatternMatcher\PatternMatcher;

class ReflectionClassConstantRepository extends ReflectionObjectElementRepository implements ClassConstantRepository
{
    /**
     * @var PatternMatcher
     */
    private $patternMatcher;

    /**
     * @var ReflectionClassFactory
     */
    protected $classInfoFactory;

    public function find(ClassConstantFilter $filter): ClassConstantCollection
    {
        $classInfo = $this->classInfoFactory->createFromFilter($filter);

        $collection = new ClassConstantCollection();

        foreach (array_keys($classInfo->getConstants()) as $constantName) {
            $constant = new ReflectionClassConstant(
                $this->docBlock,
                new \ReflectionClassConstant($classInfo->getName(), $constantName)
            );
            if (true === $this->filter($constant, $filter)) {
                $collection->add($constant);
            }
        }

        return $collection;
    }
}
