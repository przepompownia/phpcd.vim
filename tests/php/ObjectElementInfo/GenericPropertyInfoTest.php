<?php

namespace tests\ObjectElementInfo;

use PHPUnit\Framework\TestCase;
use PHPCD\ObjectElementInfo\GenericPropertyInfo;
use Mockery;
use PHPCD\ClassInfo\ClassInfo;

class GenericPropertyInfoTest extends TestCase
{
    public function testConstructor()
    {
        $classInfo = Mockery::mock(ClassInfo::class);
        $classInfo->shouldReceive('getName')->andReturn('Bar');
        $propInfo = new GenericPropertyInfo('Foo', $classInfo, 'protected', false);

        $this->assertTrue($propInfo->isProtected());
        $this->assertFalse($propInfo->isStatic());
        $this->assertEquals('Bar', $propInfo->getClass()->getName());
    }
}
