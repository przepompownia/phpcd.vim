<?php

namespace PHPCD\Element\ObjectElement;

use PHPCD\Element\ClassInfo\ClassInfo;
use PHPCD\Element\PhysicalLocation;
use PHPCD\View\ObjectElementVisitor;

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

    public function getNonTrivialTypes(): array;

    public function getPhysicalLocation(): PhysicalLocation;

    public function acceptObjectElement(ObjectElementVisitor $visitor): void;
}
