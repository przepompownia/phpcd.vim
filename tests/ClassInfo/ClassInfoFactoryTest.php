<?php

namespace PHPCD\ClassInfo;

use PHPUnit\Framework\TestCase;
use PHPCD\Filter\MethodFilter;
use PHPCD\ClassInfo\ClassInfoFactory;
use Mockery;

class ClassInfoFactoryTest extends TestCase
{
    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage PHPCD\ClassInfo\ClassInfoFactory needs class name to find method.
     */
    public function findWithNoClass()
    {
        $pattern_matcher = Mockery::mock(PatternMatcher::class);
        $pattern_matcher->shouldReceive('match')->andReturn(true);

        $factory = new ClassInfoFactory($pattern_matcher);

        $factory->createReflectionClassFromFilter(new MethodFilter([], ''));
    }
}
