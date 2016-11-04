<?php

namespace PHPCD\ObjectElementInfo;

use PHPUnit\Framework\TestCase;
use phpDocumentor\Reflection\DocBlockFactory;
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
        $property = new \ReflectionProperty($class, $method);
        $propertyInfo = new ReflectionPropertyInfo($property, $docBlockFactory);
        $types = $propertyInfo->getAllowedTypes()->getType();

        var_dump(explode('|', (string)$types));


        $index = 0;
        while ($types->has($index)) {
            $type = $types->get($index);
            var_dump(sprintf("%s: %s\n", get_class($type), (string)$type));
            ++$index;
        }

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
