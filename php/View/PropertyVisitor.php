<?php

namespace PHPCD\View;

use PHPCD\Element\ObjectElement\PropertyInfo;

interface PropertyVisitor
{
    public function visitElement(PropertyInfo $propertyInfo);
}
