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
     * Set the composer projectRoot dir
     *
     * @param string $projectRoot the path
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
        $composer = json_decode($json, true);

        if (isset($composer['autoload']['psr-4'])) {
            $list = $composer['autoload']['psr-4'];
        }

        $unify = function (&$path) {
            if (! is_array($path)) {
                $path = [$path];
            }
        };

        array_walk($list, $unify);

        if (! isset($composer['autoload-dev']['psr-4'])) {
            return $list;
        }

        $list_dev = $composer['autoload-dev']['psr-4'];

        foreach ($list_dev as $namespace => $paths) {
            if (! isset($list[$namespace])) {
                $list[$namespace] = [];
            }
            $list[$namespace] = array_merge($list[$namespace], (array) $paths);
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

                    if (! empty($prefix)) {
                        array_unshift($path, trim($prefix, self::NAMESPACE_SEPARATOR));
                    }

                    $namespaces[] = implode(self::NAMESPACE_SEPARATOR, $path);
                }
            }
        }

        return array_unique($namespaces);
    }
}
