<?php

declare(strict_types=1);

namespace tests\ObjectElement;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\DocBlock\DocBlock;
use PHPCD\Element\ObjectElement\CompoundObjectElementRepository;
use PHPCD\Element\ObjectElement\Constant\ClassConstantRepository;
use PHPCD\Element\ObjectElement\MethodRepository;
use PHPCD\Element\ObjectElement\ObjectElementPath;
use PHPCD\Element\ObjectElement\PropertyRepository;
use PHPCD\Element\ObjectElement\ReflectionMethod;
use PHPCD\Element\ObjectElement\ReflectionProperty;
use PHPCD\PatternMatcher\PatternMatcher;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\ContextFactory;
use Psr\Log\LoggerInterface as Logger;
use tests\Fixtures\MethodRepository\Sup;
use tests\Fixtures\MethodRepository\SupPhp7;
use tests\Fixtures\MethodRepository\Test1;

class CompoundObjectElementRepositoryTest extends MockeryTestCase
{
    /**
     * @test
     * @group php7
     * @dataProvider methodDataProviderPHP7
     */
    public function getTypesReturnedByMethodForPHP7($class, $method, $expectedTypes)
    {
        return $this->getTypesReturnedByMethod($class, $method, $expectedTypes);
    }

    /**
     * @test
     * @dataProvider methodDataProvider
     */
    public function getTypesReturnedByMethodForPHP5($class, $method, $expectedTypes)
    {
        return $this->getTypesReturnedByMethod($class, $method, $expectedTypes);
    }

    private function getTypesReturnedByMethod($class, $method, $expectedTypes)
    {
        $logger = Mockery::mock(Logger::class);
        $constantRepository = Mockery::mock(ClassConstantRepository::class);
        $propertyRepository = Mockery::mock(PropertyRepository::class);
        $methodRepository = Mockery::mock(MethodRepository::class);
        $docBlockFactory = DocblockFactory::createInstance();
        $contextFactory = new ContextFactory();
        $docBlock = new DocBlock($docBlockFactory, $contextFactory);
        $methodInfo = new ReflectionMethod($docBlock, new \ReflectionMethod($class, $method));

        $methodRepository->shouldReceive('getByPath')->andReturn($methodInfo);
        $objectElementRepository = new CompoundObjectElementRepository(
            $constantRepository,
            $propertyRepository,
            $methodRepository,
            $logger
        );


        $path = new ObjectElementPath($class, $method);
        $types = $objectElementRepository->getTypesReturnedByMethod($path);

        $this->assertEquals(count($expectedTypes), count($types));

        foreach ($expectedTypes as $expectedType) {
            $this->assertContains($expectedType, $types);
        }
    }

    /**
     * @test
     * @dataProvider proptypeDataProvider
     */
    public function getTypesOfProperty($class, $property, $expectedTypes)
    {
        $logger = Mockery::mock(Logger::class);
        $constantRepository = Mockery::mock(ClassConstantRepository::class);
        $propertyRepository = Mockery::mock(PropertyRepository::class);
        $methodRepository = Mockery::mock(MethodRepository::class);
        $docBlockFactory = DocblockFactory::createInstance();
        $contextFactory = new ContextFactory();
        $docBlock = new DocBlock($docBlockFactory, $contextFactory);
        $propertyInfo = new ReflectionProperty($docBlock, new \ReflectionProperty($class, $property));

        $propertyRepository->shouldReceive('getByPath')->andReturn($propertyInfo);
        $objectElementRepository = new CompoundObjectElementRepository(
            $constantRepository,
            $propertyRepository,
            $methodRepository,
            $logger
        );

        $path = new ObjectElementPath($class, $property);
        $types = $objectElementRepository->getTypesOfProperty($path);

        $this->assertEquals(count($expectedTypes), count($types));

        foreach ($expectedTypes as $expectedType) {
            $this->assertContains($expectedType, $types);
        }
    }

    public function proptypeDataProvider()
    {
        return [
            [
                Sup::class,
                'pub5',
                ['\\ReflectionClass', '\\'.Test1::class]
            ],
            [
                Sup::class,
                'pub6',
                ['\\'.PatternMatcher::class]
            ],
        ];
    }

    public function methodDataProvider()
    {
        return [
            [
                Sup::class,
                'baz',
                ['\\ReflectionClass', '\\'.Test1::class]
            ],
        ];
    }

    public function methodDataProviderPHP7()
    {
        return [
            [
                SupPhp7::class,
                'doNothing',
                ['\\'.PatternMatcher::class]
            ],
        ];
    }
}
