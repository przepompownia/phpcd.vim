<?php

namespace test\Element\ClassInfo;

use Mockery;
use PHPCD\Element\ClassInfo\ReflectionClassInfoFactory;
use PHPCD\Filter\MethodFilter;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPUnit\Framework\TestCase;

class ClassInfoFactoryTest extends TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage PHPCD\Element\ClassInfo\ReflectionClassInfoFactory needs class name to find method.
     */
    public function findWithNoClass()
    {
        $pattern_matcher = Mockery::mock(PatternMatcher::class);
        $pattern_matcher->shouldReceive('match')->andReturn(true);

        $factory = new ReflectionClassInfoFactory($pattern_matcher);

        $factory->createFromFilter(new MethodFilter([], ''));
    }
}
