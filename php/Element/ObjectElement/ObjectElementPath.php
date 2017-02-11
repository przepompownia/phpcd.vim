<?php

namespace PHPCD\Element\ObjectElement;

abstract class ObjectElementPath
{
    /**
     * @var string
     */
    protected $className;

    public function getClassName()
    {
        return $this->className;
    }
}
