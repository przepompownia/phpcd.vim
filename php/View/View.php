<?php

namespace PHPCD\View;

use PHPCD\Element\FunctionInfo\FunctionCollection;
use PHPCD\Element\ConstantInfo\ConstantInfoCollection;
use PHPCD\Element\ClassInfo\ClassInfo;
use PHPCD\Element\ObjectElementInfo\MethodInfoCollection;
use PHPCD\Element\ObjectElementInfo\PropertyInfoCollection;
use PHPCD\PHPFileInfo\PHPFileInfo;

interface View
{
    public function renderConstantInfoCollection(ConstantInfoCollection $collection);

    public function renderClassInfo(ClassInfo $classInfo);

    public function renderMethodCollection(MethodInfoCollection $collection);

    public function renderFunctionCollection(FunctionCollection $collection);

    public function renderPropertyCollection(PropertyInfoCollection $collection);

    public function renderPHPFileInfo(PHPFileInfo $fileInfo);
}
