<?php

namespace PHPCD\View;

use PHPCD\Element\ClassInfo\ClassInfoCollection;
use PHPCD\Element\ConstantInfo\ClassConstantInfoCollection;
use PHPCD\Element\ConstantInfo\ConstantInfoCollection;
use PHPCD\Element\FunctionInfo\FunctionCollection;
use PHPCD\Element\ObjectElementInfo\MethodInfoCollection;
use PHPCD\Element\ObjectElementInfo\PropertyInfoCollection;
use PHPCD\PHPFileInfo\PHPFileInfo;

interface View
{
    public function renderConstantInfoCollection(ConstantInfoCollection $collection);

    public function renderClassConstantCollection(ClassConstantInfoCollection $collection);

    public function renderClassInfoCollection(ClassInfoCollection $collection);

    public function renderMethodCollection(MethodInfoCollection $collection);

    public function renderFunctionCollection(FunctionCollection $collection);

    public function renderPropertyCollection(PropertyInfoCollection $collection);

    public function renderPHPFileInfo(PHPFileInfo $fileInfo);
}
