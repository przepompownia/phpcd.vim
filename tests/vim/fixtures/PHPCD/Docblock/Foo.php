<?php

namespace PHPCD\Docblock;

use PHPCD\Docblock\ReturnValue;

class Foo
{
    public function Bar()
    {
        $rv = new ReturnValue;
        $rc = $rv->getReflectionClass();
    }
}
