<?php

namespace PHPCD;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;

class ComposerClassmapFileRepository implements CITInfoRepository
{
    use LoggerAwareTrait;

    private $relative_classmap_path = '/vendor/composer/autoload_classmap.php';

    private $project_root;

    private $classmap = [];

    public function __construct(
        $project_root,
        LoggerInterface $logger
    ) {
        $this->setLogger($logger);
        $this->setProjectRoot($project_root);
        $this->loadClassMap();
    }

    private function setProjectRoot($project_root)
    {
        $this->project_root = $project_root;

        return $this;
    }

    private function getClassmapPath()
    {
        return $this->project_root . $this->relative_classmap_path;
    }

    private function loadClassMap()
    {
        $this->classmap = require $this->getClassmapPath();

        return $this;
    }

    /**
     * @return array
     */
    public function find()
    {
        return $this->classmap;
    }

    public function reload()
    {
        $this->loadClassMap();

        return true;
    }
}
