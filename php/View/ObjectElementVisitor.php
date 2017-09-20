<?php

declare(strict_types=1);

namespace PHPCD\View;

use PHPCD\Element\ObjectElement\Constant\ClassConstant;
use PHPCD\Element\ObjectElement\MethodInfo;
use PHPCD\Element\ObjectElement\ObjectElementCollection;
use PHPCD\Element\ObjectElement\PropertyInfo;

interface ObjectElementVisitor
{
    public function visitCollection(ObjectElementCollection $collection);

    public function visitProperty(PropertyInfo $propertyInfo);

    public function visitMethod(MethodInfo $methodInfo);

    public function visitConstant(ClassConstant $classConstant);
}
