<?php

namespace PHPCD\Filter;

abstract class ObjectElementFilter extends AbstractFilter implements ClassElementFilter
{
    const PUBLIC_ONLY = 'publicOnly';
    const STATIC_ONLY = 'staticOnly';

    protected $criteriaNames = [
        self::PUBLIC_ONLY,
        self::STATIC_ONLY,
        self::CLASS_NAME
    ];

    /**
     * @return bool|null
     */
    public function isPublicOnly()
    {
        return $this->criteria[self::PUBLIC_ONLY];
    }

    /**
     * @return bool|null
     */
    public function isStaticOnly()
    {
        return $this->criteria[self::STATIC_ONLY];
    }

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
