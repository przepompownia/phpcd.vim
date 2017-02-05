<?php

namespace tests\ObjectElementInfo;

use PHPUnit\Framework\TestCase;
use phpDocumentor\Reflection\DocBlockFactory;
use PHPCD\DocBlock\DocBlock;
use tests\MethodInfoRepository\Test1;
use PHPCD\Element\ObjectElementInfo\ReflectionPropertyInfo;

class ReflectionPropertyInfoTest extends TestCase
{
    /**
     * @test
     * @dataProvider getAllowedTypesDataProvider
     */
    public function getAllowedTypes($class, $method)
    {
        $docBlockFactory = DocBlockFactory::createInstance();
        $docBlock = new DocBlock($docBlockFactory);
        $property = new \ReflectionProperty($class, $method);
        $propertyInfo = new ReflectionPropertyInfo($property, $docBlock);
        $types = $propertyInfo->getAllowedTypes();


        // var_dump($types);
    }

    public function getAllowedTypesDataProvider()
    {
        return [
            [Test1::class, 'pub1'],
            [Test1::class, 'pub2'],
        ];
    }
}
