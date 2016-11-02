<?php

namespace PHPCD\ObjectElementInfo;

use PHPCD\Filter\ObjectElementFilter;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\ClassInfo\ClassInfoFactory;
use phpDocumentor\Reflection\DocBlockFactory;

abstract class ReflectionObjectElementInfoRepository
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
     * @var DocBlockFactory
     */
    protected $docBlockFactory;

    /**
     * @param PatternMatcher $pattern_matcher
     */
    public function __construct(
        PatternMatcher $pattern_matcher,
        ClassInfoFactory $factory,
        DocBlockFactory $docBlockFactory
    ) {
        $this->pattern_matcher = $pattern_matcher;
        $this->classInfoFactory = $factory;
        $this->docBlockFactory = $docBlockFactory;
    }

    /**
     * @param \ReflectionMethod|\ReflectionProperty $element
     * @return bool
     */
    protected function filter(ObjectElementInfo $element, ObjectElementFilter $filter)
    {
        if ($filter->getPattern() && !$this->pattern_matcher->match($filter->getPattern(), $element->getName())) {
            return false;
        }

        if ($filter->isStaticOnly() !== null && ($element->isStatic() xor $filter->isStaticOnly())) {
            return false;
        }

        if ($element->isPublic()) {
            return true;
        }

        if ($filter->isPublicOnly()) {
            return false;
        }

        if ($element->isProtected()) {
            return true;
        }

        // $element is then private
        return $element->getClass() === $filter->getClass();
    }
}
