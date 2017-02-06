<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\View\ConstantInfoVisitor;

class ConstantInfo
{
    private $name;

    private $value;

    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function accept(ConstantInfoVisitor $visitor)
    {
        $visitor->visitElement($this);
    }
}
