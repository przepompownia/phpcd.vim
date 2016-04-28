<?php

namespace PHPCD\PHPFileInfo;

/**
 *
 */
class StringBasedPHPFileInfo implements PHPFileInfo
{
    const USE_PATTERN =
        '/^use\s+((?<type>(constant|function)) )?(?<left>[\\\\\w]+\\\\)?({)?(?<right>[\\\\,\w\s]+)(})?\s*;$/';

    const ALIAS_PATTERN = '/(?<suffix>[\\\\\w]+)(\s+as\s+(?<alias>\w+))?/';

    const CLASS_PATTERN = '/^\s*\b((((final|abstract)?\s+)class)|interface|trait)\s+(?<class>\S+)/i';

    const NAMESPACE_PATTERN = '/(<\?php)?\s*namespace\s+(?<namespace>.*);$/';

    /*
     * @var SplFileObject
     */
    private $file;

    private $namespace;

    private $class;

    private $imports = [];

    public function __construct($path)
    {
        $this->file = new \SplFileObject($path);
        $this->scanFile();
    }

    /**
     * Scan the file to retrieve informations
     * about its namespace, imports and class.
     */
    public function scanFile()
    {
        $this->file->rewind();

        foreach ($this->file as $line) {
            $class = $this->scanLineForClassDeclaration($line);
            if ($class) {
                $this->setClass($class);
                break;
            }

            $line = trim($line);
            if (!$line) {
                continue;
            }

            $namespace = $this->scanLineForNamespaceDeclaration($line);
            if ($namespace) {
                $this->setNamespace($namespace);
            } else {
                $imports = $this->scanLineForImports($line);

                if (!empty($imports)) {
                    $this->mergeImports($imports);
                }
            }
        }

        $this->file->rewind();
    }

    /**
     * @var string
     */
    private function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @var string
     */
    private function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    private function mergeImports(array $imports)
    {
        $this->imports = $imports + $this->imports;

        return $this;
    }

    private function scanLineForNamespaceDeclaration($line)
    {
        if (preg_match(self::NAMESPACE_PATTERN, $line, $matches)) {
            return $matches['namespace'];
        }

        return null;
    }

    private function scanLineForClassDeclaration($line)
    {
        if (preg_match(self::CLASS_PATTERN, $line, $matches)) {
            return $matches['class'];
        }

        return null;
    }

    private function scanLineForImports($line)
    {
        $imports = [];

        if (strtolower(substr($line, 0, 3) == 'use')) {
            if (preg_match(self::USE_PATTERN, $line, $use_matches) && !empty($use_matches)) {
                $expansions = array_map([self, 'trim'], explode(',', $use_matches['right']));

                foreach ($expansions as $expansion) {
                    if (preg_match(self::ALIAS_PATTERN, $expansion, $expansion_matches) && !empty($expansion_matches)) {
                        $suffix = $expansion_matches['suffix'];
                        $alias = $expansion_matches['alias'];

                        if (empty($alias)) {
                            // Get default alias
                            $suffix_parts = explode('\\', $suffix);
                            $alias = array_pop($suffix_parts);
                        }
                    }

                    /** empty type means import of some class **/
                    if (empty($use_matches['type'])) {
                        $imports[$alias] = $use_matches['left'] . $suffix;
                    }
                    // @todo case when $use_matches['type'] is 'constant' or 'function'
                    // This requires change of the oputput format because
                    // we can use the same alias string to some class and some function at once
                }
            }
        }

        return $imports;
    }

    private static function trim($str)
    {
        return trim($str, "\t\n\r\0\x0B\\ ");
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getImports()
    {
        return $this->imports;
    }
}
