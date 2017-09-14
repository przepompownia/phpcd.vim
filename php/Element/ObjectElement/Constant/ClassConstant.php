<?php

namespace PHPCD\Element\ObjectElement\Constant;

use PHPCD\View\ClassConstantVisitor;

interface ClassConstant
{
    public function getName();

    public function getValue();

    public function getClass();

    public function accept(ClassConstantVisitor $visitor);
}
