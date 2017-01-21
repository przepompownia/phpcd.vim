<?php

namespace PHPCD\DocBlock;

use Psr\Log\LoggerInterface as Logger;
use Psr\Log\LoggerAwareTrait;
use PHPCD\PHPFileInfo\PHPFileInfoFactory;

class LegacyTypeLogic
{
    use LoggerAwareTrait;

    /*
     * Probably it should be replaced by
     * correctly implemented repository
     * to avoid scanning each file each time
     * even if such was not changed in meantime.
     *
     * @var PHPFileInfoFactory
     */
    private $fileInfoFactory;

    public function __construct(Logger $logger, PHPFileInfoFactory $fileInfoFactory)
    {
        $this->setLogger($logger);
        $this->fileInfoFactory = $fileInfoFactory;
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
        $has_doc = preg_match('/@(return|var)\s+(\S+)/m', $doc, $matches);
        if ($has_doc) {
            return $this->fixRelativeType($path, explode('|', $matches[2]));
        }

        return [];
    }

    /**
     * Fetch function, class method or class attribute's docblock
     *
     * @param string $class_name for function set this args to empty
     * @param string $name
     */
    public function doc($class_name, $name, $is_method = true)
    {
        try {
            if (!$class_name) {
                return $this->docFunction($name);
            }

            return $this->docClass($class_name, $name, $is_method);
        } catch (\ReflectionException $e) {
            $this->logger->debug($e->getMessage());
            return [null, null];
        }
    }

    private function docFunction($name)
    {
        $reflection = new \ReflectionFunction($name);
        $doc = $reflection->getDocComment();
        $path = $reflection->getFileName();
        $doc = preg_replace('/[ \t]*\* ?/m', '', $doc);
        $doc = preg_replace('#\s*\/|/\s*#', '', $doc);

        return [$path, $doc];
    }

    private function docClass($class_name, $name, $is_method)
    {
        $reflection_class = new \ReflectionClass($class_name);

        if ($is_method) {
            $reflection = $reflection_class->getMethod($name);
        } else {
            if ($reflection_class->hasProperty($name)) {
                $reflection = $reflection_class->getProperty($name);
            } else {
                $class_doc = $reflection_class->getDocComment();

                $has_pseudo_property = preg_match('/@property(|-read|-write)\s+(?<type>\S+)\s+\$?'.$name.'/mi', $class_doc, $matches);
                if ($has_pseudo_property) {
                    return [$reflection_class->getFileName(), '@var '.$matches['type']];
                }
            }
        }

        $doc = $reflection->getDocComment();

        if (preg_match('/@(return|var)\s+static/i', $doc)) {
            $path = $reflection_class->getFileName();
        } else {
            $path = $reflection->getDeclaringClass()->getFileName();
        }

        $doc = preg_replace('/[ \t]*\* ?/m', '', $doc);
        $doc = preg_replace('#\s*\/|/\s*#', '', $doc);

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
                $nsuse = $this->fileInfoFactory->createFileInfo($path);
            }

            if (in_array(strtolower($type), ['static', '$this', 'self'])) {
                $type = $nsuse->getNamespace() . '\\' . $nsuse->getClass();
            } elseif ($type[0] != '\\') {
                $parts = explode('\\', $type);
                $alias = array_shift($parts);
                $imports = $nsuse->getImports();
                if (isset($imports[$alias])) {
                    $type = $imports[$alias];
                    if ($parts) {
                        $type = $type . '\\' . join('\\', $parts);
                    }
                } else {
                    $type = $nsuse->getNamespace() . '\\' . $type;
                }
            }

            if ($type) {
                if ($type[0] != '\\') {
                    $type = '\\' . $type;
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
        'array'    => 1,
        'bool'     => 1,
        'callable' => 1,
        'double'   => 1,
        'float'    => 1,
        'int'      => 1,
        'mixed'    => 1,
        'null'     => 1,
        'object'   => 1,
        'resource' => 1,
        'scalar'   => 1,
        'string'   => 1,
        'void'     => 1,
    ];
}
