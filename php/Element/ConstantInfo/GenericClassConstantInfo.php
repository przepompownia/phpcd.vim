<?php

namespace PHPCD\Element\ConstantInfo;

use PHPCD\Element\ClassInfo\ClassInfo;

class GenericClassConstantInfo extends AbstractConstantInfo implements ClassConstantInfo
{
    /**
     * @var ClassInfo
     */
    private $class;

    public function __construct(ClassInfo $class, $name, $value)
    {
        $this->class = $class;
        $this->name = $name;
        $this->value = $value;
    }

    public function getClass()
    {
        return $this->class;
    }
}
