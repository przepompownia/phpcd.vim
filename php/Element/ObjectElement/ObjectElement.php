<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\Element\ClassInfo\ClassInfo;

interface ObjectElement
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
