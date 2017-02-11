<?php

namespace PHPCD\Element\ConstantInfo;

abstract class AbstractConstant
{
    protected $name;

    protected $value;

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }
}
