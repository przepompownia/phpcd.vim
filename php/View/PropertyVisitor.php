<?php

namespace PHPCD\View;

use PHPCD\Element\ObjectElementInfo\PropertyInfo;

interface PropertyVisitor
{
    public function visitElement(PropertyInfo $propertyInfo);
}
