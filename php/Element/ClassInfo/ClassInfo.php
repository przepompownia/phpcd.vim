<?php

namespace PHPCD\Element\ClassInfo;

use PHPCD\Filter\ClassFilter;
use PHPCD\View\ClassVisitor;

interface ClassInfo
{
    /**
     * @return bool
     */
    public function isAbstractClass();

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

    /**
     * @return string
     */
    public function getFileName();

    /**
     * @return int
     */
    public function getStartLine();

    /**
     * @return string
     */
    public function getNamespaceName();

    public function accept(ClassVisitor $visitor);
}
