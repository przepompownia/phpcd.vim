<?php

namespace tests\PatternMatcher;

use PHPUnit\Framework\TestCase;

class HeadOrSubsequenceOfLastPartFunctionalTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     */
    public function match($pattern, $fullName, $shouldMatch)
    {
        $case_sensitivity = false;
        $matcher = new \PHPCD\PatternMatcher\HeadOrSubsequenceOfLastPart(
            new \PHPCD\PatternMatcher\HeadPatternMatcher($case_sensitivity),
            new \PHPCD\PatternMatcher\SubsequencePatternMatcher($case_sensitivity),
            $case_sensitivity
        );

        $this->assertEquals($shouldMatch, $matcher->match($pattern, $fullName));
    }

    public function dataProvider()
    {
        return [
            ['PhP\\D', 'PHP\\PHPCD', false],
            ['PHP\\', 'PHP\\PHPCD', true],
            ['Ppd', 'PHP\\PHPCD', true],
            ['\PHP', 'PHP\\PHPCD', true],
        ];
    }
}
