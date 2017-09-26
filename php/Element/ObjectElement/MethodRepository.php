<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\Filter\MethodFilter;

interface MethodRepository
{
    /**
     * @return MethodCollection
     */
    public function find(MethodFilter $filter);

    public function getByPath(ObjectElementPath $path): MethodInfo;
}
