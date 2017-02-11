<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\View\ClassConstantVisitor;

interface ClassConstant
{
    public function getName();

    public function getValue();

    public function getClass();

    public function accept(ClassConstantVisitor $visitor);
}
