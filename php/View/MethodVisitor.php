<?php

namespace PHPCD\View;

use PHPCD\Element\ObjectElementInfo\MethodInfo;

interface MethodVisitor
{
    public function visitElement(MethodInfo $methodInfo);
}
