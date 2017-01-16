<?php

namespace tests\ConstantInfo;

use PHPUnit\Framework\TestCase;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\ClassInfo\ClassInfoFactory;
use PHPCD\Filter\ConstantFilter;
use PHPCD\ConstantInfo\ReflectionConstantInfoRepository;
use Mockery;

class ConstantInfoRepositoryTest extends TestCase
{
    /**
     * @test
     */
    public function find()
    {
        $className =  'tests\\MethodInfoRepository\\Test1';
        $repository = $this->getRepositoryWithTrivialMatcher($className);

        $constants = $repository->find(new ConstantFilter([
            ConstantFilter::CLASS_NAME => $className
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

        return new ReflectionConstantInfoRepository($pattern_matcher, $factory);
    }
}
