<?php

namespace PHPCD;

class NamespaceInfo
{
    private $root;

    public function __construct($root)
    {
        $this->setRoot($root);
    }

    /**
     * Set the composer root dir
     *
     * @param string $root the path
     * @return static
     */
    private function setRoot($root)
    {
        // @TODO do we need to validate this input variable?
        $this->root = $root;
        return $this;
    }

    public function getByPath($path)
    {
        $dir = dirname($path);

        $composer_path = $this->root . '/composer.json';
        $composer = json_decode(file_get_contents($composer_path), true);

        if (isset($composer['autoload']['psr-4'])) {
            $list = $composer['autoload']['psr-4'];
        } else {
            $list = [];
        }

        if (isset($composer['autoload-dev']['psr-4'])) {
            $list_dev = $composer['autoload-dev']['psr-4'];
        } else {
            $list_dev = [];
        }

        foreach ($list_dev as $namespace => $paths) {
            if (isset($list[$namespace])) {
                $list[$namespace] = array_merge((array)$list[$namespace], (array) $paths);
            } else {
                $list[$namespace] = (array) $paths;
            }
        }

        $namespaces = [];
        foreach ($list as $namespace => $paths) {
            foreach ((array)$paths as $path) {
                $path = realpath($this->root.'/'.$path);
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
