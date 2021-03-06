<?php

namespace PHPCD\Element\ClassInfo;

use PHPCD\Filter\ClassFilter;

/**
 * Repository to store and retrieve information
 * about classes, interfaces and traits
 * defined in the project.
 *
 * Propositions of better name for are welcome.
 *
 * This repository interface is still unstable.
 * It may change in some version.
 */
interface ClassRepository
{
    /**
     * @param ClassFilter $filter criteria to search
     *
     * @return ClassCollection
     */
    public function find(ClassFilter $filter);

    /**
     * Get ClassInfo based on class name.
     *
     * @param string class path
     *
     * @return ClassInfo
     */
    public function get($path);
}
