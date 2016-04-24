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
     * @param string $path_pattern Input pattern
     * @param ClassFilter $filter criteria to search
     * @param bool $add_leading_backslash prepend class path with backslash
     * @return array
     */
    public function find($path_pattern, ClassFilter $filter = null, $add_leading_backslash = true);

    /**
     * Update whole repository
     *
     * Maybe it will turn out
     * that this operation is specific
     * to certain implementation only
     * and should be removed.
     */
    public function reload();
}
