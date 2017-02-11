<?php

namespace PHPCD\View;

use PHPCD\Element\FunctionInfo\FunctionInfo;

interface FunctionVisitor
{
    public function visitElement(FunctionInfo $functionInfo);
}
