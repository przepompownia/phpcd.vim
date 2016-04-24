<?php

namespace PHPCD;

/**
 * Null Object may be needed
 * when we want to use PHPCD without Composer's autoloader
 */
class NullClassLoader implements ClassInfoRepository
{
    /**
     * @param string $path_pattern Input pattern
     * @param ClassFilter $filter criteria to search
     * @param bool $add_leading_backslash prepend class path with backslash
     * @return array
     */
    public function find($path_pattern, ClassFilter $filter = null, $add_leading_backslash = true)
    {
        return [];
    }

    public function reload()
    {
    }
}
