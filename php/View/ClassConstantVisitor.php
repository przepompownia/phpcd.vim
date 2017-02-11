<?php

namespace PHPCD\View;

use PHPCD\Element\ConstantInfo\ClassConstant;

interface ClassConstantVisitor
{
    public function visitElement(ClassConstant $constantInfo);
}
