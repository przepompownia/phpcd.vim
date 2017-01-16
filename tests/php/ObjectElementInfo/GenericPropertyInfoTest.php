<?php

namespace tests\ObjectElementInfo;

use PHPUnit\Framework\TestCase;
use PHPCD\ObjectElementInfo\GenericPropertyInfo;

class GenericPropertyInfoTest extends TestCase
{
    public function testConstructor()
    {
        $propInfo = new GenericPropertyInfo('Foo', 'Bar', 'protected', false);

        $this->assertTrue($propInfo->isProtected());
        $this->assertFalse($propInfo->isStatic());
    }
}
