<?php

namespace PHPCD\PHPFile;

use SplFileObject;
use SplTempFileObject;

class PHPFileFactory
{
    /**
     * Create PHPFile.
     *
     * @return StringBasedPHPFile|null
     */
    public function createFile($path)
    {
        if (!is_readable($path)) {
            if (file_exists($path)) {
                throw new \Exception(sprintf('Cannot read file %s.', $path));
            }

            return new StringBasedPHPFile(new SplTempFileObject(0));
        }

        $file = new SplFileObject($path);

        return new StringBasedPHPFile($file);
    }
}
