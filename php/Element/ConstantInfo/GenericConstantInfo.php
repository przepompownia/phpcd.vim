<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\View\ConstantInfoVisitor;

class GenericConstantInfo extends AbstractConstantInfo implements ConstantInfo
{
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function accept(ConstantInfoVisitor $visitor)
    {
        $visitor->visitElement($this);
    }
}
