<?php

namespace PHPCD\ClassInfo;

use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\Filter\ClassElementFilter;

class ClassInfoFactory
{
    /**
     * @var PatternMatcher
     */
    private $pattern_matcher;

    /**
     * @param PatternMatcher $pattern_matcher
     */
    public function __construct(PatternMatcher $pattern_matcher)
    {
        $this->pattern_matcher = $pattern_matcher;
    }

    /**
     *
     * @param string|object $class
     * @return ClassInfo
     */
    public function createClassInfo($class)
    {
        return new namespace\ReflectionClass($class, $this->pattern_matcher);
    }

    public function createClassInfoCollection()
    {
        return new ClassInfoCollection;
    }

    public function createReflectionClassFromFilter(ClassElementFilter $filter)
    {
        if (empty($filter->getClass())) {
            throw new \InvalidArgumentException(sprintf('%s needs class name to find method.', self::class));
        }

        return new \ReflectionClass($filter->getClass());
    }
}
