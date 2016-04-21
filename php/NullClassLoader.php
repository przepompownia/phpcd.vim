<?php

namespace PHPCD;

/**
 * Null Object may be needed
 * when we want to use PHPCD without Composer's autoloader
 */
class NullClassLoader implements CITInfoRepository
{
    /**
     * @return array
     */
    public function find()
    {
        return [];
    }

    public function reload()
    {
    }
}
