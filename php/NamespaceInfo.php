<?php

namespace PHPCD;

class NamespaceInfo
{
    const NAMESPACE_SEPARATOR = '\\';

    private $projectRoot;

    private $prefixes = [];

    public function __construct($projectRoot)
    {
        $this->setProjectRoot($projectRoot);
    }

    /**
     * Set the composer projectRoot dir.
     *
     * @param string $projectRoot the path
     *
     * @return static
     */
    private function setProjectRoot($projectRoot)
    {
        // @TODO do we need to validate this input variable?
        $this->projectRoot = $projectRoot;

        return $this;
    }

    public function getPrefixesFromComposerJson($json)
    {
        $list = [];

        $unify = function (&$path) {
            if (!is_array($path)) {
                $path = [$path];
            }
        };

        $append = function (&$paths, $namespace) use (&$list) {
            if (!isset($list[$namespace])) {
                $list[$namespace] = [];
            }
            $list[$namespace] = array_merge($list[$namespace], $paths);
        };

        $composer = json_decode($json, true);

        foreach (['autoload', 'autoload-dev'] as $autoload) {
            foreach (['psr-0', 'psr-4'] as $psr) {
                if (isset($composer[$autoload][$psr])) {
                    $listPSR = $composer[$autoload][$psr];
                    array_walk($listPSR, $unify);
                    array_walk($listPSR, $append);
                }
            }
        }

        return $list;
    }

    public function loadPrefixesFromComposerJson($json)
    {
        $this->prefixes = $this->getPrefixesFromComposerJson($json);
    }

    public function getPrefixes()
    {
        return $this->prefixes;
    }

    public function getByPath($classFile)
    {
        $classFileDir = dirname($classFile);

        $namespaces = [];
        foreach ($this->prefixes as $prefix => $namespaceRoots) {
            foreach ($namespaceRoots as $namespaceRoot) {
                $absoluteNsRoot = sprintf(
                    '%s%s%s',
                    rtrim($this->projectRoot, DIRECTORY_SEPARATOR),
                    DIRECTORY_SEPARATOR,
                    trim($namespaceRoot, DIRECTORY_SEPARATOR)
                );

                if (strpos($classFileDir, $absoluteNsRoot) === 0) {
                    $relativeClassFileDir = str_replace($absoluteNsRoot, '', $classFileDir);

                    $path = array_filter(explode(DIRECTORY_SEPARATOR, $relativeClassFileDir), 'strlen');
                    array_walk($path, function (&$word) {
                        return ucwords($word);
                    });

                    if (!empty($prefix)) {
                        array_unshift($path, trim($prefix, self::NAMESPACE_SEPARATOR));
                    }

                    $namespaces[] = implode(self::NAMESPACE_SEPARATOR, $path);
                }
            }
        }

        return array_unique($namespaces);
    }
}
