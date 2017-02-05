<?php

namespace PHPCD\View;

use PHPCD\Element\FunctionInfo\FunctionCollection;
use PHPCD\ConstantInfo\ConstantInfoCollection;
use PHPCD\ClassInfo\ClassInfo;
use PHPCD\Element\ObjectElementInfo\MethodInfo;
use PHPCD\Element\ObjectElementInfo\PropertyInfo;
use PHPCD\ConstantInfo\ConstantInfo;
use PHPCD\Element\FunctionInfo\FunctionInfo;
use PHPCD\PHPFileInfo\PHPFileInfo;

interface View
{
    public function renderConstantInfo(ConstantInfo $constantInfo);

    public function renderConstantInfoCollection(ConstantInfoCollection $collection);

    public function renderClassInfo(ClassInfo $classInfo);

    public function renderMethodInfo(MethodInfo $methodInfo);

    public function renderFunctionInfo(FunctionInfo $functionInfo);

    public function renderFunctionCollection(FunctionCollection $collection);

    public function renderPropertyInfo(PropertyInfo $propertyInfo);

    public function renderPHPFileInfo(PHPFileInfo $fileInfo);
}
