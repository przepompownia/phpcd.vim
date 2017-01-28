<?php

namespace PHPCD\Filter;

class ConstantFilter extends AbstractFilter
{
    public function __construct($pattern)
    {
        parent::__construct([], $pattern);
    }
}
