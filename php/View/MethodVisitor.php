<?php

namespace PHPCD\View;

use PHPCD\Element\ObjectElement\MethodInfo;

interface MethodVisitor
{
    public function visitElement(MethodInfo $methodInfo);
}
