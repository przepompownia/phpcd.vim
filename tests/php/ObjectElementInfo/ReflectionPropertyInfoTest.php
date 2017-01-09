<?php

namespace PHPCD\ObjectElementInfo;

use PHPUnit\Framework\TestCase;
use phpDocumentor\Reflection\DocBlockFactory;
use PHPCD\DocBlock\DocBlock;
use PHPCD\MethodInfoRepository\Test1;

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
