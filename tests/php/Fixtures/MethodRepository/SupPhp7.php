<?php

namespace tests\Fixtures\MethodRepository;

use PHPCD\PatternMatcher as PM;

class SupPhp7
{
    /**
     * @return \ReflectionClass|Test1
     */
    public function doNothing(): PM\PatternMatcher
    {
    }
}
