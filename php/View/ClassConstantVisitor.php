<?php

namespace PHPCD\View;

use PHPCD\Element\ObjectElement\Constant\ClassConstant;

interface ClassConstantVisitor
{
    public function visitElement(ClassConstant $constantInfo);
}
