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
     * @param bool $add_leading_backslash prepend class path with backslash
     * @return array
     */
    public function find($path_pattern, ClassFilter $filter = null, $add_leading_backslash = true)
    {
        $result = [];

        foreach ($this->classmap as $classpath => $file) {
            if ($this->pattern_matcher->match($path_pattern, $classpath)) {
                $item = [];

                $class_info = $this->classInfoFactory->createClassInfo($classpath);

                if ($filter === null || $class_info->matchesFilter($filter)) {
                    $item['full_name'] = ($add_leading_backslash ? '\\' : '') . $classpath;
                    $item['short_name'] = $class_info->getShortName();
                    $item['doc_comment'] = $class_info->getDocComment();
                }

                if (!empty($item)) {
                    $result[] = $item;
                }
            }
        }

        // @todo complete also built-in declared classes
        // get_declared_classes() returns classes
        // from phpcd's (not project's) environment
        return $result;
    }

    public function reload()
    {
        $this->loadClassMap();

        return true;
    }
}
