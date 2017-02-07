<?php

namespace PHPCD\Element\ClassInfo;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\PHPFileInfo\PHPFileInfoFactory;
use Composer\Autoload\ClassLoader;
use PHPCD\Filter\ClassFilter;
use PHPCD\NotFoundException;

class ComposerClassmapFileRepository implements ClassInfoRepository
{
    use LoggerAwareTrait;

    private $classLoader;

    private $classmap = [];

    /** @var ClassInfoFactory */
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
     * @param ClassFilter $filter criteria to search
     *
     * @return ClassInfoCollection
     */
    public function find(ClassFilter $filter)
    {
        $collection = $this->classInfoFactory->createClassInfoCollection();

        foreach (array_keys($this->classmap) as $classpath) {
            if ($this->pattern_matcher->match($filter->getPattern(), $classpath)) {
                try {
                    $classInfo = $this->get($classpath);
                } catch (NotFoundException $e) {
                    continue;
                }

                if ($classInfo->matchesFilter($filter)) {
                    $collection->add($classInfo);
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
     *  - superclass is not defined.
     *
     *  @return bool
     */
    private function isValid($classpath)
    {
        $filePath = $this->classLoader->findFile($classpath);

        if (false === $filePath) {
            return false;
        }

        try {
            $fileInfo = $this->fileInfoFactory->createFileInfo($filePath);
        } catch (\Exception $e) {
            $this->logger->warning($e->getMessage(), $e->getTrace());
        }

        if ($fileInfo->hasErrors()) {
            $message = '%s %s did not pass validation and then cannot be added to class info repository. Reason:';
            $this->logger->warning(
                sprintf($message, ucfirst($fileInfo->getType()), $classpath),
                $fileInfo->getErrors()
            );

            return false;
        } else {
            return true;
        }
    }

    /**
     * Get ClassInfo based on class name.
     *
     * @param string $classpath
     *
     * @return ClassInfo
     */
    public function get($classpath)
    {
        $classpath = ltrim($classpath, '\\');

        if ($this->isValid($classpath)) {
            return $this->classInfoFactory->createClassInfo($classpath);
        }

        throw new NotFoundException(sprintf('Cannot find class %s in repository', $classpath));
    }
}
