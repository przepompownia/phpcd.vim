<?php

namespace PHPCD;

class NamespaceInfo
{
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

    public function getByPath($path)
    {
        $dir = dirname($path);

        $namespaces = [];
        foreach ($this->prefixes as $namespace => $paths) {
            foreach ($paths as $path) {
                $path = realpath($this->projectRoot.'/'.$path);
                if (strpos($dir, $path) === 0) {
                    $sub_path = str_replace($path, '', $dir);
                    $sub_path = str_replace('/', '\\', $sub_path);
                    $sub_namespace = trim(ucwords($sub_path, '\\'), '\\');
                    if ($sub_namespace) {
                        $sub_namespace = '\\' . $sub_namespace;
                    }
                    $namespaces[] = trim($namespace, '\\').$sub_namespace;
                }
            }
        }

        return $namespaces;
    }
}
