<?php

namespace PHPCD\PatternMatcher;

class SubsequencePatternMatcher extends AbstractPatternMatcher implements PatternMatcher
{
    /**
     * @return bool
     */
    public function match($pattern, $string)
    {
        if (!$pattern) {
            return true;
        }

        $modifiers = $this->isCaseSensitive() ? '' : 'i';
        $regex = sprintf('/%s/%s', implode('.*', array_map('preg_quote', str_split($pattern))), $modifiers);

        return (bool) preg_match($regex, $string);
    }
}
