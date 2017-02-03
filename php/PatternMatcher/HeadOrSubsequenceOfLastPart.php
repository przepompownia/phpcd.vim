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
     * @param  $case_sensitive
     */
    public function __construct(
        HeadPatternMatcher $headMatcher,
        SubsequencePatternMatcher $subsequenceMatcher,
        $case_sensitive = null
    ) {
        $this->headMatcher = $headMatcher;
        $this->subsequenceMatcher = $subsequenceMatcher;
        $this->case_sensitive = $case_sensitive;
    }

    /**
     * @return bool
     */
    public function match($pattern, $string)
    {
        if ($this->headMatcher->match($pattern, $string)) {
            return true;
        }

        $parts = explode('\\', $string);

        return $this->subsequenceMatcher->match($pattern, end($parts));
    }
}
