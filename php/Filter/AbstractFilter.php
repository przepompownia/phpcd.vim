<?php

namespace PHPCD\Filter;

abstract class AbstractFilter
{
    protected $criteria = [];

    protected $criteriaNames = [];

    /**
     * @var string|null
     */
    private $pattern;

    public function __construct(array $criteria, $pattern = null)
    {
        $this->validatePattern($pattern);
        $this->pattern = $pattern;

        foreach ($this->criteriaNames as $field) {
            if (isset($criteria[$field])) {
                $this->criteria[$field] = $criteria[$field];
            } else {
                $this->criteria[$field] = null;
            }
        }
    }

    /**
     * Get regex pattern to match against class name
     *
     * @return string|null
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    public function getCriteriaNames()
    {
        return $this->criteriaNames;
    }

    private function validatePattern($pattern)
    {
        if (!is_string($pattern)) {
            throw new \InvalidArgumentException('Class name pattern must be string.');
        }
    }
}
