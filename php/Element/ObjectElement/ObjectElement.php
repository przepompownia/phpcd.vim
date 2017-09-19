<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\Element\ClassInfo\ClassInfo;
use PHPCD\Element\PhysicalLocation;

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

    /**
     * @return array
     */
    public function getAllowedTypes();

    /**
     * @return array
     */
    public function getNonTrivialTypes();

    public function getPhysicalLocation(): PhysicalLocation;
}
