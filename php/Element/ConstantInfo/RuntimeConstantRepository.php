<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\Filter\ConstantFilter;
use PHPCD\PatternMatcher\PatternMatcher;

class RuntimeConstantRepository implements ConstantRepository
{
    /**
     * @var PatternMatcher
     */
    private $patternMatcher;

    /**
     * @var ConstantFactory
     */
    private $constantFactory;

    /**
     * @param PatternMatcher $patternMatcher
     */
    public function __construct(PatternMatcher $patternMatcher, ConstantFactory $constantFactory)
    {
        $this->patternMatcher = $patternMatcher;
        $this->constantFactory = $constantFactory;
    }

    public function find(ConstantFilter $filter)
    {
        $collection = $this->constantFactory->createConstantCollection();

        $constants = get_defined_constants();

        $matcher = $this->patternMatcher;
        $pattern = $filter->getPattern();

        $arrayFilter = function ($key) use ($pattern, $matcher) {
            return $matcher->match($pattern, $key);
        };

        $constants = array_filter($constants, $arrayFilter, ARRAY_FILTER_USE_KEY);

        foreach ($constants as $name => $value) {
            $collection->add(new GenericConstant($name, $value));
        }

        return $collection;
    }
}
