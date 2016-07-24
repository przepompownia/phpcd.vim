<?php

namespace PHPCD\ClassInfo;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\PHPFileInfo\PHPFileInfoFactory;
use Composer\Autoload\ClassLoader;

class ComposerClassmapFileRepository implements ClassInfoRepository
{
    use LoggerAwareTrait;

    private $relative_classmap_path = '/vendor/composer/autoload_classmap.php';

    private $project_root;

    private $classLoader;

    private $classmap = [];

    /** @var ClassInfoFactory **/
    private $classInfoFactory;

    /**
     * @var PHPFileInfoFactory
     */
    private $fileInfoFactory;

    /**
     * @var PatternMatcher
     */
    private $pattern_matcher;

    public function __construct(
        ClassLoader $classLoader,
        PatternMatcher $pattern_matcher,
        ClassInfoFactory $classInfoFactory,
        PHPFileInfoFactory $fileInfoFactory,
        LoggerInterface $logger
    ) {
        $this->pattern_matcher = $pattern_matcher;
        $this->classLoader = $classLoader;
        $this->classInfoFactory = $classInfoFactory;
        $this->fileInfoFactory = $fileInfoFactory;
        $this->setLogger($logger);
        $this->loadClassMap();
    }

    private function loadClassMap()
    {
        $this->classmap = $this->classLoader->getClassmap();

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

                if ($class_info !== null && ($filter === null || $class_info->matchesFilter($filter))) {
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
        $filePath = $this->classLoader->findFile($classpath);

        $fileInfo = $this->fileInfoFactory->createFileInfo($filePath);

        if ($fileInfo->hasErrors()) {
            $message = 'Class %s did not passed validation and then cannot be added to class info repository. Reason:';
            $this->logger->warning(sprintf($message, $classpath), $fileInfo->getErrors());
            return false;
        } else {
            return true;
        }
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
