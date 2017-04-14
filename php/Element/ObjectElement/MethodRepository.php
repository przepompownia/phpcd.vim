<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\Filter\MethodFilter;

interface MethodRepository
{
    /**
     * @return MethodCollection
     */
    public function find(MethodFilter $filter);

    /**
     * @return MethodInfo
     */
    public function getByPath(MethodPath $path);
}
