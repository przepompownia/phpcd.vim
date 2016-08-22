<?php

namespace PHPCD\Filter;

interface ClassElementFilter
{
    const CLASS_NAME = 'className';

    public function getClass();
}
