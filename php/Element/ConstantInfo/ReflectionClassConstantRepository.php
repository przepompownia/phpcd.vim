<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\Element\ClassInfo\ReflectionClassFactory;
use PHPCD\Filter\ClassConstantFilter;
use PHPCD\PatternMatcher\PatternMatcher;

class ReflectionClassConstantRepository implements ClassConstantRepository
{
    /**
     * @var PatternMatcher
     */
    private $patternMatcher;

    /**
     * @var ReflectionClassFactory
     */
    protected $classInfoFactory;

    /**
     * @param PatternMatcher $patternMatcher
     */
    public function __construct(PatternMatcher $patternMatcher, ReflectionClassFactory $factory)
    {
        $this->patternMatcher = $patternMatcher;
        $this->classInfoFactory = $factory;
    }

    /**
     * @param ClassConstantFilter $filter
     * @return ClassConstantCollection
     */
    public function find(ClassConstantFilter $filter)
    {
        $classInfo = $this->classInfoFactory->createFromFilter($filter);

        $collection = new ClassConstantCollection();

        foreach ($classInfo->getConstants() as $name => $value) {
            $collection->add(new GenericClassConstant($classInfo, $name, $value));
        }

        return $collection;
    }
}
