<?php

namespace PHPCD\B\C;

class ExpectPublicVariable
{
    public function x()
    {
        $alpha = new \PHPCD\A\Alpha;
    }
}
// classInfo $class_name == parent
