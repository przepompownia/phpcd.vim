<?php

namespace PHPCD\ClassInfo;

/**
 * Repository to store and retrieve information
 * about classes, interfaces and traits
 * (CIT abbreviation comes from here)
 * defined in the project.
 *
 * Propositions of better name for are welcome.
 *
 * This repository interface is still unstable.
 * It may change in some version.
 */
interface ClassInfoRepository
{
    /**
     * @param ClassFilter $filter criteria to search
     * @return ClassInfoCollection
     */
    public function find(ClassFilter $filter);

    /**
     * Update whole repository
     *
     * Maybe it will turn out
     * that this operation is specific
     * to certain implementation only
     * and should be removed.
     */
    public function reload();

    /**
     * Get ClassInfo based on class name
     *
     * @param string class path
     * @return ClassInfo|null
     */
    public function get($path);
}
