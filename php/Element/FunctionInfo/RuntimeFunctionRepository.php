<?php

namespace PHPCD\Element\FunctionInfo;

use PHPCD\Filter\FunctionFilter;
use PHPCD\PatternMatcher\PatternMatcher;

class RuntimeFunctionRepository implements FunctionRepository
{
    /**
     * @var PatternMatcher
     */
    private $patternMatcher;

    /**
     * @var FunctionFactory
     */
    private $functionFactory;

    public function __construct(PatternMatcher $patternMatcher, FunctionFactory $functionFactory)
    {
        $this->patternMatcher = $patternMatcher;
        $this->functionFactory = $functionFactory;
    }

    /**
     * @return FunctionCollection
     */
    public function find(FunctionFilter $filter)
    {
        $collection = $this->functionFactory->createFunctionCollection();

        $functions = get_defined_functions();
        $functions = array_merge($functions['internal'], $functions['user']);

        $matcher = $this->patternMatcher;
        $pattern = $filter->getPattern();

        $arrayFilter = function ($value) use ($pattern, $matcher) {
            return $matcher->match($pattern, $value);
        };

        $functions = array_filter($functions, $arrayFilter);

        foreach ($functions as $functionName) {
            $function = $this->get($functionName);
            $collection->add($function);
        }

        return $collection;
    }

    /**
     * Get FunctionInfo based on class name.
     *
     * @return FunctionInfo
     */
    public function get($functionName)
    {
        return $this->functionFactory->createFunction($functionName);
    }
}
