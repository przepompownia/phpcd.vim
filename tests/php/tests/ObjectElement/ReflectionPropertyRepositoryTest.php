<?php

namespace tests\ObjectElement;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\DocBlock\DocBlock;
use PHPCD\Element\ClassInfo\ReflectionClass;
use PHPCD\Element\ClassInfo\ReflectionClassFactory;
use PHPCD\Element\ObjectElement\PropertyInfo;
use PHPCD\Element\ObjectElement\PropertyPath;
use PHPCD\Element\ObjectElement\ReflectionPropertyRepository;
use PHPCD\Filter\PropertyFilter;
use PHPCD\PatternMatcher\PatternMatcher;

class ReflectionPropertyRepositoryTest extends MockeryTestCase
{
    /**
     * @test
     */
    public function findAllProperties()
    {
        $className =  'tests\\MethodRepository\\Test1';
        $repository = $this->getRepositoryWithTrivialMatcher($className);

        $properties = $repository->find(new PropertyFilter([
            PropertyFilter::CLASS_NAME => $className
        ], 'mocked'));

        $this->assertFalse($properties->isEmpty());

        $this->assertCount(7, $properties);
        $property = $properties->getIterator()->current();
        $this->assertEquals('pub1', $property->getName());
        $this->assertTrue($property->isPublic());
    }

    private function getRepositoryWithTrivialMatcher($className)
    {
        $patternMatcher = Mockery::mock(PatternMatcher::class);
        $patternMatcher->shouldReceive('match')->andReturn(true);
        $factory = Mockery::mock(ReflectionClassFactory::class);
        $factory->shouldReceive('createFromFilter')->once()
            ->andReturn(new ReflectionClass(new \ReflectionClass($className)))->byDefault();
        $classInfo = Mockery::mock(ReflectionClass::class);
        $factory->shouldReceive('createClassInfo')->andReturn($classInfo);
        $docBlock = Mockery::mock(DocBlock::class);

        return new ReflectionPropertyRepository($patternMatcher, $factory, $docBlock);
    }

    /**
     * @test
     */
    public function findPublicPropertiesOnly()
    {
        $className =  'tests\\MethodRepository\\Test1';
        $repository = $this->getRepositoryWithTrivialMatcher($className);

        $filter = new PropertyFilter([
            PropertyFilter::CLASS_NAME => $className,
            PropertyFilter::PUBLIC_ONLY => true
        ], 'mocked');

        $properties = $repository->find($filter);
        $this->assertCount(5, $properties);
    }

    /**
     * @test
     * @expectedException \PHPCD\NotFoundException
     */
    public function getByPathOfNonexistingProperty()
    {
        $pattern_matcher = Mockery::mock(PatternMatcher::class);
        $factory = Mockery::mock(ReflectionClassFactory::class);
        $docBlock = Mockery::mock(DocBlock::class);
        $repository = new ReflectionPropertyRepository($pattern_matcher, $factory, $docBlock);

        $className =  \tests\MethodRepository\Test1::class;
        $propertyName = 'doesnotexist';
        $path = new PropertyPath($className, $propertyName);

        $property = $repository->getByPath($path);
    }

    /**
     * @test
     */
    public function getByPath()
    {
        $patternMatcher = Mockery::mock(PatternMatcher::class);
        $factory = Mockery::mock(ReflectionClassFactory::class);
        $docBlock = Mockery::mock(DocBlock::class);
        $repository = new ReflectionPropertyRepository($patternMatcher, $factory, $docBlock);

        $className =  \tests\MethodRepository\Test1::class;
        $propertyName = 'pub1';
        $path = new PropertyPath($className, $propertyName);

        $property = $repository->getByPath($path);
        $this->assertInstanceof(PropertyInfo::class, $property);
        $this->assertEquals($propertyName, $property->getName());
    }
}
