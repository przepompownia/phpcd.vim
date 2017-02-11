<?php

namespace PHPCD\Element\ClassInfo;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use PHPCD\PatternMatcher\PatternMatcher;
use PHPCD\PHPFile\PHPFileFactory;
use Composer\Autoload\ClassLoader;
use PHPCD\Filter\ClassFilter;
use PHPCD\NotFoundException;

class ComposerClassmapFileRepository implements ClassRepository
{
    use LoggerAwareTrait;

    private $classLoader;

    private $classmap = [];

    /** @var ClassFactory */
    private $classFactory;

    /**
     * @var PHPFileFactory
     */
    private $fileFactory;

    /**
     * @var PatternMatcher
     */
    private $patternMatcher;

    public function __construct(
        ClassLoader $classLoader,
        PatternMatcher $patternMatcher,
        ClassFactory $classFactory,
        PHPFileFactory $fileFactory,
        LoggerInterface $logger
    ) {
        $this->patternMatcher = $patternMatcher;
        $this->classLoader = $classLoader;
        $this->classFactory = $classFactory;
        $this->fileFactory = $fileFactory;
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
     * @return ClassCollection
     */
    public function find(ClassFilter $filter)
    {
        $collection = $this->classFactory->createCollection();

        foreach (array_keys($this->classmap) as $classpath) {
            if ($this->patternMatcher->match($filter->getPattern(), $classpath)) {
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
            $file = $this->fileFactory->createFile($filePath);
        } catch (\Exception $e) {
            $this->logger->warning($e->getMessage(), $e->getTrace());
        }

        if ($file->hasErrors()) {
            $message = '%s %s did not pass validation and then cannot be added to class info repository. Reason:';
            $this->logger->warning(
                sprintf($message, ucfirst($file->getType()), $classpath),
                $file->getErrors()
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
            return $this->classFactory->createClassInfo($classpath);
        }

        throw new NotFoundException(sprintf('Cannot find class %s in repository', $classpath));
    }
}
