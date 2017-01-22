<?php

namespace PHPCD\Filter;

class FunctionFilter
{
    private $pattern;

    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    public function getPattern()
    {
        return $this->pattern;
    }
}
