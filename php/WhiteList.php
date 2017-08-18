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
    const PHPCD_AUTOLOAD_FILE = __DIR__.'/../vendor/autoload.php';

    const PHPCD_DIR = '.phpcd';

    const WHITELIST_FILENAME = 'whitelist.php';

    public static function write($forceReindex = false)
    {
        $phpcdClassLoader = require self::PHPCD_AUTOLOAD_FILE;

        $fileFactory = new PHPFileFactory();

        $oldWhiteList = self::read();
        $whiteList = [];

        foreach ($phpcdClassLoader->getClassMap() as $class => $fileName) {
            $modificationTime = filemtime($fileName);

            if (isset($oldWhiteList[$class]['modified'])) {
                $cachedModificationTime = $oldWhiteList[$class]['modified'];

                if (false === $forceReindex && $modificationTime <= $cachedModificationTime) {
                    $whiteList[$class] = $oldWhiteList[$class];
                    continue;
                }
            }

            $file = $fileFactory->createFile($fileName);
            if (false === $file->hasErrors()) {
                $whiteList[$class] = [
                    'fileName' => $fileName,
                    'modified' => $modificationTime,
                ];
            }
        }

        $export = var_export($whiteList, true);

        $output = sprintf("<?php\nreturn %s;\n", $export);

        self::createOutputDirIfNeeded();

        file_put_contents(self::getFileName(), $output);
    }

    private static function createOutputDirIfNeeded()
    {
        if (!file_exists(self::getOutputDir())) {
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
        if (!is_readable(self::getFileName())) {
            return [];
        }

        $whitelist = require self::getFileName();

        if (!is_array($whitelist)) {
            error_log('Wrong whielist format.');
            return [];
        }

        return $whitelist;
    }

    public static function load()
    {
        $whitelist = self::read();
        if (empty($whitelist)) {
            self::write();
        }

        foreach ($whitelist as $file) {
            if (!empty($file['fileName']) && is_readable($file['fileName'])) {
                try {
                    require_once $file['fileName'];
                } catch (\Exception $e) {

                }
            }
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
