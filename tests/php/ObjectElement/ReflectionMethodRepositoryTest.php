<?php

namespace tests\ObjectElement;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\DocBlock\DocBlock;
use PHPCD\Element\ClassInfo\ReflectionClass;
use PHPCD\Element\ClassInfo\ReflectionClassFactory;
use PHPCD\Element\ObjectElement\ReflectionMethodRepository;
use PHPCD\Filter\MethodFilter;
use PHPCD\PatternMatcher\PatternMatcher;
use tests\Fixtures\MethodRepository\Test1;

class ReflectionMethodRepositoryTest extends MockeryTestCase
{

    /**
     * @test
     */
    public function findAllMethods()
    {
        $className =  Test1::class;
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
        $className =  Test1::class;
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
        $patternMatcher = Mockery::mock(PatternMatcher::class);
        $patternMatcher->shouldReceive('match')->andReturn(true);
        $factory = Mockery::mock(ReflectionClassFactory::class);
        $factory->shouldReceive('createFromFilter')->once()
            ->andReturn(new ReflectionClass(new \ReflectionClass($className)));
        $docBlock = Mockery::mock(DocBlock::class);

        return new ReflectionMethodRepository($patternMatcher, $factory, $docBlock);
    }
}
