<?php

namespace PHPCD\ObjectElementInfo;

use PHPUnit\Framework\TestCase;
use phpDocumentor\Reflection\DocBlockFactory;
use PHPCD\MethodInfoRepository\Test1;

class ReflectionPropertyInfoTest extends TestCase
{
    /**
     * @test
     */
    public function getAllowedTypes()
    {
        $docBlockFactory = DocBlockFactory::createInstance();
        $property = new \ReflectionProperty(Test1::class, 'pub1');
        $propertyInfo = new ReflectionPropertyInfo($property, $docBlockFactory);
        $types = $propertyInfo->getAllowedTypes()->getType();

        var_dump((string)$types);

        $index = 0;
        while ($types->has($index)) {
            $type = $types->get($index);
            var_dump((string)$type);
            ++$index;
        }

        // var_dump($types);
    }
}
