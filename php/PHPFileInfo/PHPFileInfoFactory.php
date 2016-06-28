<?php

namespace PHPCD\PHPFileInfo;

use SplFileObject;

class PHPFileInfoFactory
{
    /**
     * Create PHPFileInfo
     *
     * @return PHPFileInfo|null
     */
    public function createFileInfo($path)
    {
        if (file_exists($path) && is_readable($path)) {
            $file = new SplFileObject($path);

            return new StringBasedPHPFileInfo($file);
        }
    }
}
