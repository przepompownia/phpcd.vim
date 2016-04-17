<?php

namespace PHPCD;

interface ClassLoaderInterface
{
    /**
     * @return array
     */
    public function getClassMap();

    public function reload();
}
