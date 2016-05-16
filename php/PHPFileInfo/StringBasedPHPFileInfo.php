<?php

namespace PHPCD\PHPFileInfo;

use SplFileObject;

/**
 *
 */
class StringBasedPHPFileInfo implements PHPFileInfo
{
    const USE_PATTERN =
        '/^use\s+((?<type>(constant|function)) )?(?<left>[\\\\\w]+\\\\)?({)?(?<right>[\\\\,\w\s]+)(})?\s*;$/';

    const ALIAS_PATTERN = '/(?<suffix>[\\\\\w]+)(\s+as\s+(?<alias>\w+))?/';

    const CLASS_PATTERN = '/^\s*\b((((final|abstract)\s+)?class)|interface|trait)\s+(?<class>\S+)/i';

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
        if (file_exists($path) && is_readable($path)) {
            $this->file = new SplFileObject($path);
            $this->scanFile();
        }
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
                $expansions = array_map('self::trim', explode(',', $use_matches['right']));

                foreach ($expansions as $expansion) {
                    if (preg_match(self::ALIAS_PATTERN, $expansion, $expansion_matches) && !empty($expansion_matches)) {
                        $suffix = $expansion_matches['suffix'];

                        if (empty($expansion_matches['alias'])) {
                            // Get default alias
                            $suffix_parts = explode('\\', $suffix);
                            $alias = array_pop($suffix_parts);
                        } else {
                            $alias = $expansion_matches['alias'];
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

    public function getFullClassPath()
    {
        return $this->getNamespace().'\\'.$this->getClass();
    }

    public function hasAliasUsed($alias)
    {
        return !empty($this->imports[$alias]);
    }

    public function getUsedAliasesForPath($full_path)
    {
        $used = array_filter($this->imports, function ($value) use ($full_path) {
            return $value === $full_path;
        });

        return array_keys($used);
    }

    public function getPathByAlias($alias)
    {
        if ($this->hasAliasUsed($alias)) {
            return $this->imports[$alias];
        }

        return null;
    }

    /**
     * @param array $new_class_params {
     *  @type string  $alias
     *  @type string  $full_path
     *  }
     *
     * @return array {
     *  @type string        $alias        the original or modified alias
     *  @type string|null   $full_path    null if we have no new import to do
     *  }[]
     */
    public function getFixForNewClassUsage(array $new_class_params)
    {
        $new_alias  = $new_class_params['alias'];
        $new_path   = trim($new_class_params['full_path'], '\\');

        $used_aliases = $this->getUsedAliasesForPath($new_path);
        if (!empty($used_aliases)) {
            $suggestions = [];
            foreach ($used_aliases as $alias) {
                if ($alias === $new_alias) {
                    // Nothing to do
                    return [['alias' => null, 'full_path' => null ]];
                }

                $suggestions[] = [ 'alias' => $alias, 'full_path' => null ];
            }

            return $suggestions;
        }

        if (!empty($this->imports)) {
            if ($this->hasAliasUsed($new_alias)) {
                if ($this->extractNamespaceFromPath($new_path) === $this->getNamespace()) {
                    return [['alias' => 'namespace\\'.$new_alias, 'full_path' => null ]];
                }

                // The path was not used,
                // but an alternative alias is needed.
                $new_alias = $this->generateNewAlias($new_alias);

                return [['alias' => $new_alias, 'full_path' => $new_path ]];
            }
        }

        if ($new_alias === $this->getClass()) {
            // Although it is not an error, we encourage
            // to not override the current class name with
            // an the same named alias of another path.
            $new_alias = $this->generateNewAlias($new_alias);

            return [['alias' => $new_alias, 'full_path' => $new_path ]];
        }

        // The alias was not used, so it does not need change,
        // the path need to insert
        return [['alias' => null, 'full_path' => $new_path ]];
    }

    private function extractNamespaceFromPath($path)
    {
        return substr($path, 0, strrpos($path, '\\'));
    }

    private function generateNewAlias($alias)
    {
        $suffix = '1';

        do {
            ++$suffix;
        } while ($this->canNewAliasConflict($alias.$suffix));

        return $alias.$suffix;
    }

    public function canNewAliasConflict($alias)
    {
        if ($this->hasAliasUsed($alias) || $alias === $this->getClass()) {
            return true;
        }

        // TODO: use an external knowledge from repository
        // and check if there is no class in the current namespace
        // whose name is the same as the newly generated alias.
        return false;
    }
}
