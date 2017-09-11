<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\NotFoundException;
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
     *
     * @return ClassConstantCollection
     */
    public function find(ClassConstantFilter $filter)
    {
        $classInfo = $this->classInfoFactory->createFromFilter($filter);

        $collection = new ClassConstantCollection();

        foreach ($classInfo->getConstants() as $name => $value) {
            if ($this->patternMatcher->match($filter->getPattern(), $name)) {
                $collection->add(new GenericClassConstant($classInfo, $name, $value));
            }
        }

        return $collection;
    }

    /**
     * @param ObjectElementPath $elementPath
     *
     * @return ClassConstant
     */
    public function getByPath(ObjectElementPath $elementPath)
    {
        $classInfo = $this->classInfoFactory->createClassInfo($elementPath->getClassName());

        if (! $classInfo->hasConstant($elementPath->getElementName())) {
            throw new NotFoundException(sprintf(
                'Class %s has not constant %s',
                $elementPath->getClassName(),
                $elementPath->getElementName()
            ));
        }

        return new GenericClassConstant(
            $elementPath->getClassName(),
            $elementPath->getElementName(),
            $classInfo->getConstantName()
        );
    }
}
