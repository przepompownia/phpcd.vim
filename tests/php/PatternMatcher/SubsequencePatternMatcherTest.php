<?php

namespace PHPCD\PatternMatcher;

use PHPUnit\Framework\TestCase;

class SubsequencePatternMatcherTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     */
    public function match($pattern, $fullName, $shouldMatch)
    {
        $matcher = new SubsequencePatternMatcher(false);

        $this->assertEquals($shouldMatch, $matcher->match($pattern, $fullName));
    }

    public function dataProvider()
    {
        return [
            ['PhP\\D', 'PHP\\PHPCD', true],
            ['PHP\\', 'PHP\\PHPCD', true],
        ];
    }
}
