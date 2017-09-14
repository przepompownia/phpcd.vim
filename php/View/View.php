<?php

namespace PHPCD\View;

use PHPCD\Element\ClassInfo\ClassCollection;
use PHPCD\Element\ObjectElement\Constant\ClassConstantCollection;
use PHPCD\Element\ConstantInfo\ConstantCollection;
use PHPCD\Element\FunctionInfo\FunctionCollection;
use PHPCD\Element\ObjectElement\MethodCollection;
use PHPCD\Element\ObjectElement\PropertyCollection;
use PHPCD\PHPFile\PHPFile;

interface View
{
    public function renderConstantCollection(ConstantCollection $collection);

    public function renderClassConstantCollection(ClassConstantCollection $collection);

    public function renderClassCollection(ClassCollection $collection);

    public function renderMethodCollection(MethodCollection $collection);

    public function renderFunctionCollection(FunctionCollection $collection);

    public function renderPropertyCollection(PropertyCollection $collection);

    public function renderPHPFile(PHPFile $file);
}
