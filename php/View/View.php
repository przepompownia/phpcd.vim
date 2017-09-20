<?php

namespace PHPCD\View;

use PHPCD\Element\ClassInfo\ClassCollection;
use PHPCD\Element\ConstantInfo\ConstantCollection;
use PHPCD\Element\FunctionInfo\FunctionCollection;
use PHPCD\Element\ObjectElement\ObjectElementCollection;
use PHPCD\PHPFile\PHPFile;

interface View
{
    public function renderConstantCollection(ConstantCollection $collection);

    public function renderClassCollection(ClassCollection $collection);

    public function renderFunctionCollection(FunctionCollection $collection);

    public function renderObjectElementCollection(ObjectElementCollection $collection);

    public function renderPHPFile(PHPFile $file);
}
