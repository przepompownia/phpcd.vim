<?php

namespace PHPCD\Element\ObjectElement\Constant;

use PHPCD\Element\ObjectElement\ObjectElement;
use PHPCD\View\ClassConstantVisitor;

interface ClassConstant extends ObjectElement
{
    public function getValue();

    public function accept(ClassConstantVisitor $visitor);
}
