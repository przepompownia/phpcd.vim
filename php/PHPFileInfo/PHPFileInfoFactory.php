<?php

namespace PHPCD\PHPFileInfo;

class PHPFileInfoFactory
{
    /**
     * Create PHPFileInfo
     *
     * @return PHPFileInfo
     */
    public function createFileInfo($path)
    {
        return new StringBasedPHPFileInfo($path);
    }
}
