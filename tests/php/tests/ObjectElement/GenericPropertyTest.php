<?php

namespace tests\ObjectElement;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPCD\Element\ObjectElement\GenericProperty;
use Mockery;
use PHPCD\Element\ClassInfo\ClassInfo;

class GenericPropertyTest extends MockeryTestCase
{
    public function testConstructor()
    {
        $classInfo = Mockery::mock(ClassInfo::class);
        $classInfo->shouldReceive('getName')->andReturn('Bar');
        $propInfo = new GenericProperty('Foo', $classInfo, 'protected', false);

        $this->assertTrue($propInfo->isProtected());
        $this->assertFalse($propInfo->isStatic());
        $this->assertEquals('Bar', $propInfo->getClass()->getName());
    }
}
