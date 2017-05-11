<?php

namespace PHPCD\Docblock;

class ReturnValue
{
    /**
     * @return \ReflectionClass
     */
    public function getReflectionClass()
    {
        return new \ReflectionClass($this);
    }

}
