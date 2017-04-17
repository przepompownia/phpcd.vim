<?php

namespace PHPCD\PatternMatcher;

abstract class AbstractPatternMatcher implements PatternMatcher
{
    private $caseSensitive = false;

    public function __construct($caseSensitive = null)
    {
        if ($caseSensitive === true) {
            $this->caseSensitive = $caseSensitive;
        }
    }

    /**
     * @return bool
     */
    public function isCaseSensitive()
    {
        return $this->caseSensitive;
    }
}
