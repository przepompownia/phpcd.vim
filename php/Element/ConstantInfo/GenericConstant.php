<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\View\ConstantVisitor;

class GenericConstant extends AbstractConstantInfo implements ConstantInfo
{
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function accept(ConstantVisitor $visitor)
    {
        $visitor->visitElement($this);
    }
}
