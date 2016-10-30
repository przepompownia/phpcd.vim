<?php

namespace PHPCD\ObjectElementInfo;

use PHPUnit\Framework\TestCase;
use PHPCD\ClassInfo\ClassInfoFactory;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\Filter\PropertyFilter;
use PHPCD\ObjectElementInfo\ReflectionPropertyInfoRepository;
use Mockery;

class ReflectionPropertyInfoRepositoryTest extends TestCase
{
    /**
     * @test
     */
    public function findAllProperties()
    {
        $className =  'PHPCD\\MethodInfoRepository\\Test1';
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
        $factory->shouldReceive('createReflectionClassFromFilter')->once()->andReturn(new \ReflectionClass($className));

        return new ReflectionPropertyInfoRepository($pattern_matcher, $factory);
    }

    /**
     * @test
     */
    public function findPublicPropertiesOnly()
    {
        $className =  'PHPCD\\MethodInfoRepository\\Test1';
        $repository = $this->getRepositoryWithTrivialMatcher($className);

        $filter = new PropertyFilter([
            PropertyFilter::CLASS_NAME => $className,
            PropertyFilter::PUBLIC_ONLY => true
        ], 'mocked');

        $properties = $repository->find($filter);
        $this->assertCount(5, $properties);
    }
}
