<?php

namespace PHPCD\View;

use PHPCD\Element\ClassInfo\ClassInfo;

interface ClassVisitor
{
    public function visitElement(ClassInfo $classInfo);
}
