<?php

namespace PHPCD\Filter;

class ClassConstantFilter extends ObjectElementFilter
{
    /**
     * Get filter by the class where this element is defined.
     *
     * @return string|null
     */
    public function getClassName()
    {
        return $this->criteria[self::CLASS_NAME];
    }
}
