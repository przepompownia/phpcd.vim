<?php

namespace PHPCD\PHPFileInfo;

use SplFileObject;
use SplTempFileObject;

class PHPFileInfoFactory
{
    /**
     * Create PHPFileInfo
     *
     * @return PHPFileInfo|null
     */
    public function createFileInfo($path)
    {
        if (! is_readable($path)) {
            if (file_exists($path)) {
                throw new \Exception(sprintf('Cannot read file %s.', $path));
            }

            return new StringBasedPHPFileInfo(new SplTempFileObject(0));
        }

        $file = new SplFileObject($path);

        return new StringBasedPHPFileInfo($file);
    }
}
