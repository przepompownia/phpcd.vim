<?php

namespace PHPCD\FunctionInfo;

interface FunctionRepository
{
    /**
     * @param FunctionFilter $filter
     * @return FunctionCollection
    public function find(FunctionFilter $filter);

    /**
     * Get FunctionInfo based on class name
     *
     * @return FunctionInfo
     */
    public function get($name);
}
