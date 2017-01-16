<?php

namespace tests\ObjectElementInfo;

use PHPUnit\Framework\TestCase;
use PHPCD\ClassInfo\ClassInfoFactory;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\Filter\MethodFilter;
use PHPCD\ObjectElementInfo\ReflectionMethodInfoRepository;
use PHPCD\DocBlock\DocBlock;
use Mockery;

class ReflectionMethodInfoRepositoryTest extends TestCase
{

    /**
     * @test
     */
    public function findAllMethods()
    {
        $className =  'tests\\MethodInfoRepository\\Test1';
        $repository = $this->getRepositoryWithTrivialMatcher($className);

        $methods = $repository->find(new MethodFilter([
            MethodFilter::CLASS_NAME => $className
        ], 'mocked'));

        $this->assertFalse($methods->isEmpty());

        $this->assertCount(5, $methods);
        $method = $methods->getIterator()->current();
        $this->assertEquals('play', $method->getName());
        $this->assertTrue($method->isPublic());
    }

    /**
     * @test
     */
    public function findPublicMethodsOnly()
    {
        $className =  'tests\\MethodInfoRepository\\Test1';
        $repository = $this->getRepositoryWithTrivialMatcher($className);

        $filter = new MethodFilter([
            MethodFilter::CLASS_NAME => $className,
            MethodFilter::PUBLIC_ONLY => true
        ], 'mocked');

        $methods = $repository->find($filter);
        $this->assertCount(2, $methods);
    }

    private function getRepositoryWithTrivialMatcher($className)
    {
        $pattern_matcher = Mockery::mock(PatternMatcher::class);
        $pattern_matcher->shouldReceive('match')->andReturn(true);
        $factory = Mockery::mock(ClassInfoFactory::class);
        $factory->shouldReceive('createReflectionClassFromFilter')->once()->andReturn(new \ReflectionClass($className));
        $docBlock = Mockery::mock(DocBlock::class);

        return new ReflectionMethodInfoRepository($pattern_matcher, $factory, $docBlock);
    }
}
