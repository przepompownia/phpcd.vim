<?php

namespace PHPCD\Filter;

class FunctionFilter extends AbstractFilter
{
    public function __construct($pattern)
    {
        parent::__construct([], $pattern);
    }
}
