<?php

namespace tests\ConstantInfo;

use PHPCD\Element\ClassInfo\ReflectionClass;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\Element\ClassInfo\ReflectionClassFactory;
use PHPCD\Filter\ClassConstantFilter;
use PHPCD\Element\ConstantInfo\ReflectionClassConstantRepository;
use Mockery;
use tests\Fixtures\MethodRepository\Test1;

class ClassConstantRepositoryTest extends MockeryTestCase
{
    /**
     * @test
     */
    public function find()
    {
        $className =  Test1::class;
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
        $patternMatcher = Mockery::mock(PatternMatcher::class);
        $patternMatcher->shouldReceive('match')->andReturn(true);
        $factory = Mockery::mock(ReflectionClassFactory::class);
        $factory->shouldReceive('createFromFilter')->once()
            ->andReturn(new ReflectionClass(new \ReflectionClass($className)));

        return new ReflectionClassConstantRepository($patternMatcher, $factory);
    }
}
