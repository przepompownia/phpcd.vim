<?php

namespace tests\Fixtures\FunctionRepository;

use PHPCD\Filter\MethodFilter;
use X\Y as Z;

function veryLongName(MethodFilter $methodFilter): Z
{
    return new Z();
}
