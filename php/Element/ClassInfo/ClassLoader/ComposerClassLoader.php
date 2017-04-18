<?php

namespace PHPCD\Element\ClassInfo\ClassLoader;

class ComposerClassLoader implements ClassLoader
{
    /**
     * @var \Composer\Autoload\ClassLoader
     */
    private $classLoader;

    public function __construct($classLoader)
    {
        $this->classLoader = $classLoader;
    }

    /**
     * @return array
     */
    public function getClassMap()
    {
        return $this->classLoader->getClassMap();
    }

    /**
     * @return string|bool
     */
    public function findFile($classpath)
    {
        return $this->classLoader->findFile($classpath);
    }
}
