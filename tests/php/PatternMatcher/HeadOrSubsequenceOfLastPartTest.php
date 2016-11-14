<?php

namespace PHPCD\PatternMatcher;

use PHPUnit\Framework\TestCase;
use Mockery;

class HeadOrSubsequenceOfLastPartTest extends TestCase
{
    /**
     * @test
     */
    public function whenHeadMatches()
    {
        $headMatcher = Mockery::mock(HeadPatternMatcher::class);
        $subsequenceMatcher = Mockery::mock(SubsequencePatternMatcher::class);

        $headMatcher->shouldReceive('match')->once()->andReturn(true);
        $subsequenceMatcher->shouldNotReceive('match');

        $matcher = new HeadOrSubsequenceOfLastPart($headMatcher, $subsequenceMatcher, false);

        $this->assertTrue($matcher->match('foo', 'bar'));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function whenHeadDoesNotMatchAndSubsequenceMatches($pattern, $fullName, $lastPart)
    {
        $headMatcher = Mockery::mock(HeadPatternMatcher::class);
        $subsequenceMatcher = Mockery::mock(SubsequencePatternMatcher::class);

        $headMatcher->shouldReceive('match')->once()->andReturn(false);
        $subsequenceMatcher->shouldReceive('match')->once()->withArgs([$pattern, $lastPart])->andReturn(true);

        $matcher = new HeadOrSubsequenceOfLastPart($headMatcher, $subsequenceMatcher, false);

        $this->assertTrue($matcher->match($pattern, $fullName));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function whenNoneMatches($pattern, $fullName, $lastPart)
    {
        $headMatcher = Mockery::mock(HeadPatternMatcher::class);
        $subsequenceMatcher = Mockery::mock(SubsequencePatternMatcher::class);

        $headMatcher->shouldReceive('match')->once()->andReturn(false);
        $subsequenceMatcher->shouldReceive('match')->once()->withArgs([$pattern, $lastPart])->andReturn(false);

        $matcher = new HeadOrSubsequenceOfLastPart($headMatcher, $subsequenceMatcher, false);

        $this->assertFalse($matcher->match($pattern, $fullName));
    }

    public function dataProvider()
    {
        return [
            ['foo', 'PHP\\PHPCD', 'PHPCD'],
            ['bar\\', 'PHP\\PHPCD', 'PHPCD'],
            ['ba\\z', 'PH\\P\\CD', 'CD'],
        ];
    }
}
