<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\DocBlock\DocBlock;
use PHPCD\Element\ClassInfo\ReflectionClassFactory;
use PHPCD\Filter\ObjectElementFilter;
use PHPCD\PatternMatcher\PatternMatcher;

abstract class ReflectionObjectElementRepository
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
     * @var DocBlock
     */
    protected $docBlock;

    /**
     * @param PatternMatcher $patternMatcher
     */
    public function __construct(
        PatternMatcher $patternMatcher,
        ReflectionClassFactory $factory,
        DocBlock $docBlock
    ) {
        $this->patternMatcher = $patternMatcher;
        $this->classInfoFactory = $factory;
        $this->docBlock = $docBlock;
    }

    /**
     * @return bool
     */
    protected function filter(ObjectElement $element, ObjectElementFilter $filter)
    {
        if ($filter->getPattern() && !$this->patternMatcher->match($filter->getPattern(), $element->getName())) {
            return false;
        }

        if (null !== $filter->isStaticOnly() && ($element->isStatic() xor $filter->isStaticOnly())) {
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
