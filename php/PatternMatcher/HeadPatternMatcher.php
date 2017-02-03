<?php

namespace PHPCD\PatternMatcher;

class HeadPatternMatcher extends AbstractPatternMatcher implements PatternMatcher
{
    /**
     * @return bool
     */
    public function match($pattern, $string)
    {
        if (!$pattern) {
            return true;
        }

        if ($this->isCaseSensitive()) {
            return 0 === strpos($string, $pattern);
        } else {
            return 0 === stripos($string, $pattern);
        }
    }
}
