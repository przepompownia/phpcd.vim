<?php

namespace PHPCD\ConstantInfo;

use PHPCD\Filter\ConstantFilter;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\ClassInfo\ClassInfoFactory;

class ReflectionConstantInfoRepository implements ConstantInfoRepository
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

    public function find(ConstantFilter $filter)
    {
        $reflectionClass = $this->classInfoFactory->createReflectionClassFromFilter($filter);

        $collection = new ConstantInfoCollection();

        foreach ($reflectionClass->getConstants() as $name => $value) {
            $collection->add(new ConstantInfo($name, $value));
        }

        return $collection;
    }
}
