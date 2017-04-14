<?php

namespace tests\ObjectElement;

use PHPUnit\Framework\TestCase;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\DocBlockFactory;
use PHPCD\DocBlock\DocBlock;
use tests\Fixtures\MethodRepository\Test1;
use PHPCD\Element\ObjectElement\ReflectionProperty;

class ReflectionPropertyTest extends TestCase
{
    /**
     * @test
     * @dataProvider getAllowedTypesDataProvider
     */
    public function getAllowedTypes($class, $method)
    {
        $contextFactory = new ContextFactory();
        $docBlockFactory = DocBlockFactory::createInstance();
        $docBlock = new DocBlock($docBlockFactory, $contextFactory);
        $property = new \ReflectionProperty($class, $method);
        $propertyInfo = new ReflectionProperty($docBlock, $property);
        $type = $propertyInfo->getFirstTypeString();
    }

    public function getAllowedTypesDataProvider()
    {
        return [
            [Test1::class, 'pub1'],
            [Test1::class, 'pub2'],
        ];
    }
}
