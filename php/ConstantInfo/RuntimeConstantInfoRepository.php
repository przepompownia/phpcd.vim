<?php

namespace PHPCD\ConstantInfo;

use PHPCD\Filter\ConstantFilter;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\ClassInfo\ClassInfoFactory;

class RuntimeConstantInfoRepository implements ConstantInfoRepository
{
    /**
     * @var PatternMatcher
     */
    private $patternMatcher;

    /**
     * @var ConstantInfoFactory
     */
    private $constantInfoFactory;

    /**
     * @param PatternMatcher $patternMatcher
     */
    public function __construct(PatternMatcher $patternMatcher, ConstantInfoFactory $constantInfoFactory)
    {
        $this->patternMatcher = $patternMatcher;
        $this->constantInfoFactory = $constantInfoFactory;
    }

    public function find(ConstantFilter $filter)
    {
        $collection = $this->constantInfoFactory->createConstantInfoCollection();

        $constants = get_defined_constants();

        $matcher = $this->patternMatcher;
        $pattern = $filter->getPattern();

        $arrayFilter = function ($key) use ($pattern, $matcher) {
            return $matcher->match($pattern, $key);
        };

        $constants = array_filter($constants, $arrayFilter, ARRAY_FILTER_USE_KEY);

        foreach ($constants as $name => $value) {
            $collection->add(new ConstantInfo($name, $value));
        }

        return $collection;
    }
}
