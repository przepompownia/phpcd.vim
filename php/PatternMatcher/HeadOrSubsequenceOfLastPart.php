<?php

namespace PHPCD\PatternMatcher;

class HeadOrSubsequenceOfLastPart extends AbstractPatternMatcher implements PatternMatcher
{
    /**
     * @var HeadPatternMatcher
     */
    private $headMatcher;

    /**
     * @var SubsequencePatternMatcher
     */
    private $subsequenceMatcher;

    /**
     * @param HeadPatternMatcher        $headMatcher
     * @param SubsequencePatternMatcher $subsequenceMatcher
     * @param  $caseSensitive
     */
    public function __construct(
        HeadPatternMatcher $headMatcher,
        SubsequencePatternMatcher $subsequenceMatcher,
        $caseSensitive = null
    ) {
        $this->headMatcher = $headMatcher;
        $this->subsequenceMatcher = $subsequenceMatcher;
        parent::__construct($caseSensitive);
    }

    /**
     * @return bool
     */
    public function match($pattern, $string)
    {
        if ($this->headMatcher->match(ltrim($pattern, '\\'), $string)) {
            return true;
        }

        $parts = explode('\\', $string);

        return $this->subsequenceMatcher->match($pattern, end($parts));
    }
}
