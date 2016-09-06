<?php

namespace PHPCD\ClassInfo;

use PHPCD\Filter\ClassFilter;

interface ClassInfo
{
    /**
     * @return bool
     */
    public function isAbstract();

    /**
     * @return bool
     */
    public function isFinal();

    /**
     * @return bool
     */
    public function isTrait();

    /**
     * @return bool
     */
    public function isInstantiable();

    /**
     * @return bool
     */
    public function isInterface();

    /**
     * @return string
     */
    public function getShortName();

    /**
     * @return string
     */
    public function getDocComment();

    /**
     * @return bool
     */
    public function matchesFilter(ClassFilter $classFilter);

    /**
     * @return string
     */
    public function getName();
}
