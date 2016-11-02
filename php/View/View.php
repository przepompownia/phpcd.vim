<?php

namespace PHPCD\View;

use PHPCD\ClassInfo\ClassInfo;
use PHPCD\ObjectElementInfo\MethodInfo;
use PHPCD\ObjectElementInfo\PropertyInfo;
use PHPCD\ConstantInfo\ConstantInfo;
use PHPCD\FunctionInfo\FunctionInfo;
use PHPCD\PHPFileInfo\PHPFileInfo;

interface View
{
    public function renderConstantInfo(ConstantInfo $constantInfo);

    public function renderClassInfo(ClassInfo $classInfo);

    public function renderMethodInfo(MethodInfo $methodInfo);

    public function renderFunctionInfo(FunctionInfo $functionInfo);

    public function renderPropertyInfo(PropertyInfo $propertyInfo);

    public function renderPHPFileInfo(PHPFileInfo $fileInfo);
}