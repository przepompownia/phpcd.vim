<?php

namespace PHPCD\ObjectElementInfo;

use PHPCD\ClassInfo\ClassInfo;

interface ObjectElementInfo
{
    /**
     * @return ClassInfo
     */
    public function getClass();

    public function getName();

    public function isPublic();

    public function isProtected();

    public function isStatic();

    public function getDocComment();

    public function getModifiers();
}
