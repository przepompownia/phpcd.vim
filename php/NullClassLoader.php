<?php

namespace PHPCD;

/**
 * Null Object may be needed
 * when we want to use PHPCD without Composer's autoloader
 */
class NullClassLoader implements ClassLoaderInterface
{
    /**
     * @return array
     */
    public function getClassMap()
    {
        return [];
    }

    public function reload()
    {
    }
}
