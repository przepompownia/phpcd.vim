<?php

namespace PHPCD\Element\ObjectElementInfo;

use PHPCD\DocBlock\DocBlock;
use PHPCD\Element\ClassInfo\ReflectionClassInfoFactory;
use PHPCD\Filter\ObjectElementFilter;
use PHPCD\PatternMatcher\PatternMatcher;

abstract class ReflectionObjectElementInfoRepository
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
     * @var DocBlock
     */
    protected $docBlock;

    /**
     * @param PatternMatcher $patternMatcher
     */
    public function __construct(
        PatternMatcher $patternMatcher,
        ReflectionClassInfoFactory $factory,
        DocBlock $docBlock
    ) {
        $this->patternMatcher = $patternMatcher;
        $this->classInfoFactory = $factory;
        $this->docBlock = $docBlock;
    }

    /**
     * @return bool
     */
    protected function filter(ObjectElementInfo $element, ObjectElementFilter $filter)
    {
        if ($filter->getPattern() && !$this->patternMatcher->match($filter->getPattern(), $element->getName())) {
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
        return $element->getClass()->getName() === $filter->getClassName();
    }
}
