<?php

namespace tests\ObjectElementInfo;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\Element\ObjectElementInfo\PropertyPath;
use PHPCD\Element\ClassInfo\ClassInfoFactory;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\Filter\PropertyFilter;
use PHPCD\Element\ObjectElementInfo\ReflectionPropertyInfoRepository;
use PHPCD\Element\ClassInfo\ClassInfo;
use PHPCD\Element\ObjectElementInfo\PropertyInfo;
use PHPCD\DocBlock\DocBlock;
use Mockery;

class ReflectionPropertyInfoRepositoryTest extends MockeryTestCase
{
    /**
     * @test
     */
    public function findAllProperties()
    {
        $className =  'tests\\MethodInfoRepository\\Test1';
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
        $pattern_matcher = Mockery::mock(PatternMatcher::class);
        $pattern_matcher->shouldReceive('match')->andReturn(true);
        $factory = Mockery::mock(ClassInfoFactory::class);
        $factory->shouldReceive('createReflectionClassFromFilter')->once()->andReturn(new \ReflectionClass($className))->byDefault();
        $classInfo = Mockery::mock(ClassInfo::class);
        $factory->shouldReceive('createClassInfo')->andReturn($classInfo);
        $docBlock = Mockery::mock(DocBlock::class);

        return new ReflectionPropertyInfoRepository($pattern_matcher, $factory, $docBlock);
    }

    /**
     * @test
     */
    public function findPublicPropertiesOnly()
    {
        $className =  'tests\\MethodInfoRepository\\Test1';
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
     * @expectedException PHPCD\NotFoundException
     */
    public function getByPathOfNonexistingProperty()
    {
        $pattern_matcher = Mockery::mock(PatternMatcher::class);
        $factory = Mockery::mock(ClassInfoFactory::class);
        $classInfo = Mockery::mock(ClassInfo::class);
        $docBlock = Mockery::mock(DocBlock::class);
        $repository = new ReflectionPropertyInfoRepository($pattern_matcher, $factory, $docBlock);

        $className =  \tests\MethodInfoRepository\Test1::class;
        $propertyName = 'doesnotexist';
        $path = new PropertyPath($className, $propertyName);

        $property = $repository->getByPath($path);
    }

    /**
     * @test
     */
    public function getByPath()
    {
        $pattern_matcher = Mockery::mock(PatternMatcher::class);
        $factory = Mockery::mock(ClassInfoFactory::class);
        $classInfo = Mockery::mock(ClassInfo::class);
        $docBlock = Mockery::mock(DocBlock::class);
        $repository = new ReflectionPropertyInfoRepository($pattern_matcher, $factory, $docBlock);

        $className =  \tests\MethodInfoRepository\Test1::class;
        $propertyName = 'pub1';
        $path = new PropertyPath($className, $propertyName);

        $property = $repository->getByPath($path);
        $this->assertInstanceof(PropertyInfo::class, $property);
        $this->assertEquals($propertyName, $property->getName());
    }
}
