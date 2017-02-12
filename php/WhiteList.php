<?php

namespace PHPCD;

use PHPCD\PHPFile\PHPFileFactory;

class WhiteList
{
    const PHPCD_AUTOLOAD_FILE = __DIR__ . '/../vendor/autoload.php';

    const PHPCD_DIR = '.phpcd';

    const WHITELIST_FILENAME = 'whitelist.php';

    public static function write()
    {
        $phpcdClassLoader = require self::PHPCD_AUTOLOAD_FILE;

        $fileFactory = new PHPFileFactory();

        $whiteList = [];

        foreach ($phpcdClassLoader->getClassMap() as $class => $fileName) {
            $file = $fileFactory->createFile($fileName);
            if (!$file->hasErrors()) {
                $whiteList[] = $fileName;
            }
        }

        $export = var_export($whiteList, true);

        $output = sprintf("<?php\nreturn %s;\n", $export);

        mkdir(self::getOutputDir(), 0700, true);

        file_put_contents(self::getFileName(), $output);
    }

    /**
     * @return array
     */
    public static function read()
    {
        if (is_readable(self::getFileName())) {
            return require(self::getFileName());
        }

        return [];
    }

    public static function load()
    {
        $whitelist = self::read();
        if (empty($whitelist)) {
            self::write();
        }

        foreach ($whitelist as $fileName) {
            require_once($fileName);
        }
    }

    private static function getOutputDir()
    {
        return __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.self::PHPCD_DIR;
    }

    private static function getFileName()
    {
        return self::getOutputDir().DIRECTORY_SEPARATOR.self::WHITELIST_FILENAME;
    }
}


