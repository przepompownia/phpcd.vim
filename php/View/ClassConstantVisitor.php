<?php

namespace PHPCD\View;

use PHPCD\Element\ConstantInfo\ClassConstantInfo;

interface ClassConstantVisitor
{
    public function visitElement(ClassConstantInfo $constantInfo);
}
