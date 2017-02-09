<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\Element\ClassInfo\ReflectionClassInfoFactory;
use PHPCD\Filter\ClassConstantFilter;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\Element\ClassInfo\ClassInfoFactory;

class ReflectionClassConstantInfoRepository implements ClassConstantInfoRepository
{
    /**
     * @var PatternMatcher
     */
    private $patternMatcher;

    /**
     * @var ReflectionClassInfoFactory
     */
    protected $classInfoFactory;

    /**
     * @param PatternMatcher $patternMatcher
     */
    public function __construct(PatternMatcher $patternMatcher, ReflectionClassInfoFactory $factory)
    {
        $this->patternMatcher = $patternMatcher;
        $this->classInfoFactory = $factory;
    }

    /**
     * @param ClassConstantFilter $filter
     * @return ClassConstantInfoCollection
     */
    public function find(ClassConstantFilter $filter)
    {
        $classInfo = $this->classInfoFactory->createFromFilter($filter);

        $collection = new ClassConstantInfoCollection();

        foreach ($classInfo->getConstants() as $name => $value) {
            $collection->add(new GenericClassConstantInfo($classInfo, $name, $value));
        }

        return $collection;
    }
}
