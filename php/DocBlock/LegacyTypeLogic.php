<?php

namespace PHPCD\DocBlock;

use Psr\Log\LoggerInterface as Logger;
use Psr\Log\LoggerAwareTrait;
use PHPCD\PHPFile\PHPFileFactory;

class LegacyTypeLogic
{
    use LoggerAwareTrait;

    /*
     * Probably it should be replaced by
     * correctly implemented repository
     * to avoid scanning each file each time
     * even if such was not changed in meantime.
     *
     * @var PHPFileFactory
     */
    private $fileFactory;

    public function __construct(Logger $logger, PHPFileFactory $fileFactory)
    {
        $this->setLogger($logger);
        $this->fileFactory = $fileFactory;
    }

    public function typeByReturnType($class_name, $name)
    {
        try {
            if ($class_name) {
                $reflection = new \ReflectionClass($class_name);
                $reflection = $reflection->getMethod($name);
            } else {
                $reflection = new \ReflectionFunction($name);
            }
            $type = (string) $reflection->getReturnType();

            if (strtolower($type) == 'self') {
                $type = $class_name;
            }

            return $type;
        } catch (\ReflectionException $e) {
            $this->logger->debug((string) $e);
        }
    }

    public function typeByDoc($path, $doc)
    {
        $doc = preg_replace('/[ \t]*\* ?/m', '', $doc);
        $doc = preg_replace('#\s*\/|/\s*#', '', $doc);
        $has_doc = preg_match('/@(return|var)\s+(\S+)/m', $doc, $matches);
        if ($has_doc) {
            return $this->fixRelativeType($path, explode('|', $matches[2]));
        }

        return [];
    }

    public function docFunction($name)
    {
        $reflection = new \ReflectionFunction($name);
        $doc = $reflection->getDocComment();
        $path = $reflection->getFileName();

        return [$path, $doc];
    }

    private function fixRelativeType($path, $names)
    {
        $nsuse = null;

        $types = [];
        foreach ($names as $type) {
            if (isset($this->primitive_types[$type])) {
                continue;
            }

            if (!$nsuse && $type[0] != '\\') {
                $nsuse = $this->fileFactory->createFile($path);
            }

            if (in_array(strtolower($type), ['static', '$this', 'self'])) {
                $type = $nsuse->getNamespace().'\\'.$nsuse->getClassName();
            } elseif ($type[0] != '\\') {
                $parts = explode('\\', $type);
                $alias = array_shift($parts);
                $imports = $nsuse->getImports();
                if (isset($imports[$alias])) {
                    $type = $imports[$alias];
                    if ($parts) {
                        $type = $type.'\\'.implode('\\', $parts);
                    }
                } else {
                    $type = $nsuse->getNamespace().'\\'.$type;
                }
            }

            if ($type) {
                if ($type[0] != '\\') {
                    $type = '\\'.$type;
                }
                $types[] = $type;
            }
        }

        return self::arrayUnique($types);
    }

    private static function arrayUnique($array)
    {
        $_ = [];
        foreach ($array as $a) {
            $_[$a] = 1;
        }

        return array_keys($_);
    }

    private $primitive_types = [
        'array' => 1,
        'bool' => 1,
        'callable' => 1,
        'double' => 1,
        'float' => 1,
        'int' => 1,
        'mixed' => 1,
        'null' => 1,
        'object' => 1,
        'resource' => 1,
        'scalar' => 1,
        'string' => 1,
        'void' => 1,
    ];
}
