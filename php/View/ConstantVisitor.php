<?php

namespace PHPCD\View;

use PHPCD\Element\ConstantInfo\ConstantInfo;

interface ConstantVisitor
{
    public function visitElement(ConstantInfo $constantInfo);
}
