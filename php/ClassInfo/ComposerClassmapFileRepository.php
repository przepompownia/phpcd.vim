<?php

namespace PHPCD\ClassInfo;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use PHPCD\PatternMatcher\PatternMatcher;

class ComposerClassmapFileRepository implements ClassInfoRepository
{
    use LoggerAwareTrait;

    private $relative_classmap_path = '/vendor/composer/autoload_classmap.php';

    private $project_root;

    private $classmap = [];

    /** @var ClassInfoFactory **/
    private $classInfoFactory;

    /**
     * @var PatternMatcher
     */
    private $pattern_matcher;

    public function __construct(
        $project_root,
        PatternMatcher $pattern_matcher,
        ClassInfoFactory $classInfoFactory,
        LoggerInterface $logger
    ) {
        $this->pattern_matcher = $pattern_matcher;
        $this->classInfoFactory = $classInfoFactory;
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
     * @param string $path_pattern Input pattern
     * @param ClassFilter $filter criteria to search
     * @return ClassInfoCollection
     */
    public function find($path_pattern, ClassFilter $filter = null)
    {
        $collection = $this->classInfoFactory->createClassInfoCollection();

        foreach (array_keys($this->classmap) as $classpath) {
            if ($this->pattern_matcher->match($path_pattern, $classpath)) {
                $class_info = $this->get($classpath);

                if ($filter === null || $class_info->matchesFilter($filter)) {
                    $collection->add($class_info);
                }
            }
        }

        // @todo complete also built-in declared classes
        // get_declared_classes() returns classes
        // from phpcd's (not project's) environment
        return $collection;
    }

    /**
     * Check if getting information about classes cause no problem
     * Examples:
     *  - file has syntax errors
     *  - superclass is not defined
     *
     *  @return bool
     */
    private function isValid($classpath)
    {
        // @todo inject ClassLoader
        // $loader->findFile()
        // check syntax for file
        // log invalid classes
        return true;
    }

    /**
     * Get ClassInfo based on class name
     *
     * @param string class path
     * @return ClassInfo|null
     */
    public function get($classpath)
    {
        if ($this->isValid($classpath)) {
            return $this->classInfoFactory->createClassInfo($classpath);
        }

        return null;
    }

    public function reload()
    {
        $this->loadClassMap();

        return true;
    }
}
