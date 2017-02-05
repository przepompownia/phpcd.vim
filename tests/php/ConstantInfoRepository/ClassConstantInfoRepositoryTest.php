<?php

namespace tests\ConstantInfo;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\Element\ClassInfo\ClassInfoFactory;
use PHPCD\Filter\ClassConstantFilter;
use PHPCD\Element\ConstantInfo\ReflectionClassConstantInfoRepository;
use Mockery;

class ClassConstantInfoRepositoryTest extends MockeryTestCase
{
    /**
     * @test
     */
    public function find()
    {
        $className =  'tests\\MethodInfoRepository\\Test1';
        $repository = $this->getRepositoryWithTrivialMatcher($className);

        $constants = $repository->find(new ClassConstantFilter([
            ClassConstantFilter::CLASS_NAME => $className
        ], 'mocked'));

        $this->assertFalse($constants->isEmpty());
        $this->assertCount(2, $constants);

        $iterator = $constants->getIterator();

        $this->assertEquals('ZZZ', $iterator->current()->getName());
        $this->assertEquals('vvv', $iterator->current()->getValue());
        $iterator->next();
        $this->assertEquals('XXX', $iterator->current()->getName());
        $this->assertEquals('yyy', $iterator->current()->getValue());
    }

    private function getRepositoryWithTrivialMatcher($className)
    {
        $pattern_matcher = Mockery::mock(PatternMatcher::class);
        $pattern_matcher->shouldReceive('match')->andReturn(true);
        $factory = Mockery::mock(ClassInfoFactory::class);
        $factory->shouldReceive('createReflectionClassFromFilter')->once()->andReturn(new \ReflectionClass($className));

        return new ReflectionClassConstantInfoRepository($pattern_matcher, $factory);
    }
}
