<?php

namespace PHPCD\View;

use PHPCD\Element\FunctionInfo\FunctionInfo;

interface FunctionInfoVisitor
{
    public function visitElement(FunctionInfo $functionInfo);
}
