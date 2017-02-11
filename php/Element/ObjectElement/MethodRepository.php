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
     * @param string $path
     *
     * @return MethodInfo
     */
    public function getByPath($path);
}
