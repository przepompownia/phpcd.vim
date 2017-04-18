<?php

namespace tests\Element\ClassInfo;

use Mockery;
use PHPCD\Element\ClassInfo\ReflectionClassFactory;
use PHPCD\Filter\MethodFilter;
use PHPUnit\Framework\TestCase;

class ClassInfoFactoryTest extends TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage PHPCD\Element\ClassInfo\ReflectionClassFactory needs class name to find method.
     */
    public function findWithNoClass()
    {
        $factory = new ReflectionClassFactory();

        $factory->createFromFilter(new MethodFilter([], ''));
    }
}
