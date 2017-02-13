<?php

namespace PHPCD;

use PHPCD\PHPFile\PHPFileFactory;

/**
 * Prepare or read the cache file that contains the list of files
 * that can be loaded before the main process is forked.
 *
 * Previously, if some file was need to be autoloaded by two forks,
 * then it was included as many times as the number of fokrs thad needed it.
 *
 * It is needed because of the current implementation of msgpack-rpc.
 */
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
            if (false === $file->hasErrors()) {
                $whiteList[] = $fileName;
            }
        }

        $export = var_export($whiteList, true);

        $output = sprintf("<?php\nreturn %s;\n", $export);

        self::createOutputDirIfNeeded();

        file_put_contents(self::getFileName(), $output);
    }

    private static function createOutputDirIfNeeded()
    {
        if (! file_exists(self::getOutputDir())) {
            mkdir(self::getOutputDir(), 0700, true);
        }

        if (!is_dir(self::getOutputDir()) || !is_readable(self::getOutputDir()) || !is_writable(self::getOutputDir())) {
            throw new \Exception(sprintf('Cannot create file in %s.', self::getOutputDir()));
        }
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


