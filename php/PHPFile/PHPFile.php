<?php

namespace PHPCD\PHPFile;

interface PHPFile
{
    /**
     * @return string
     */
    public function getNamespace();

    /**
     * @return string
     */
    public function getClassName();

    /**
     * @return string
     */
    public function getSuperclass();

    /**
     * @return array
     */
    public function getInterfaces();

    /**
     * @return string
     */
    public function getFullClassPath();

    /**
     * @return bool
     */
    public function hasAliasUsed($alias);

    /**
     * @return bool
     */
    public function hasErrors();

    /**
     * @return array
     */
    public function getErrors();

    /**
     * @return array
     */
    public function getUsedAliasesForPath($full_path);

    /**
     * @return string
     */
    public function getPathByAlias($alias);

    /**
     * @return string
     */
    public function getImports();

    /**
     * @param array $new_class_params {
     *
     *  @var string    $alias
     *  @var string    $full_path
     *  }
     *
     * @return array {
     *
     *  @var string        $alias        the original or modified alias
     *  @var string|null   $full_path    null if we have no new import to do
     *  }
     */
    public function getFixForNewClassUsage(array $new_class_params);

    /**
     * @return bool
     */
    public function isClass();

    /**
     * @return bool
     */
    public function isInterface();

    /**
     * @return bool
     */
    public function isTrait();

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType();
}
