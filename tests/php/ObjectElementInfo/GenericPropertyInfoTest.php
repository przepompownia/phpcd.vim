<?php

namespace PHPCD\ObjectElementInfo;

use PHPUnit\Framework\TestCase;

class GenericPropertyInfoTest extends TestCase
{
    public function testConstructor()
    {
        $propInfo = new GenericPropertyInfo('Foo', 'Bar', 'protected', false);

        $this->assertTrue($propInfo->isProtected());
        $this->assertFalse($propInfo->isStatic());
    }
}
