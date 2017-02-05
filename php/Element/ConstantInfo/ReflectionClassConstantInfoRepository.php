<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\Filter\ClassConstantFilter;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\Element\ClassInfo\ClassInfoFactory;

class ReflectionClassConstantInfoRepository implements ClassConstantInfoRepository
{
    /**
     * @var PatternMatcher
     */
    private $pattern_matcher;

    /**
     * @var ClassInfoFactory
     */
    protected $classInfoFactory;

    /**
     * @param PatternMatcher $pattern_matcher
     */
    public function __construct(PatternMatcher $pattern_matcher, ClassInfoFactory $factory)
    {
        $this->pattern_matcher = $pattern_matcher;
        $this->classInfoFactory = $factory;
    }

    public function find(ClassConstantFilter $filter)
    {
        $reflectionClass = $this->classInfoFactory->createReflectionClassFromFilter($filter);

        $collection = new ConstantInfoCollection();

        foreach ($reflectionClass->getConstants() as $name => $value) {
            $collection->add(new ConstantInfo($name, $value));
        }

        return $collection;
    }
}
