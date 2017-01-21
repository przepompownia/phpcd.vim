<?php

namespace PHPCD\Filter;

class ConstantFilter extends AbstractFilter implements ClassElementFilter
{
    protected $criteriaNames = [
        self::CLASS_NAME
    ];

    /**
     * Get filter by the class where this element is defined
     *
     * @return string|null
     */
    public function getClassName()
    {
        return $this->criteria[self::CLASS_NAME];
    }
}
