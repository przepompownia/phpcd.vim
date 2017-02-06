<?php

namespace PHPCD\View;

use PHPCD\Element\ConstantInfo\ConstantInfo;

interface ConstantInfoVisitor
{
    public function visitElement(ConstantInfo $constantInfo);
}
