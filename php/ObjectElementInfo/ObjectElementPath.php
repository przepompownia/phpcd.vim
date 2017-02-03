<?php

namespace PHPCD\ObjectElementInfo;

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
